<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class PosTerminal extends Page
{
    // protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

    protected string $view = 'filament.pages.pos-terminal';

    // protected static ?string $navigationLabel = 'POS Terminal';

    protected static ?string $title = 'POS Terminal';

    protected static ?int $navigationSort = 1;

    // ── Reactive State ────────────────────────────────────────────────────────

    public string $search = '';

    public ?int $selectedCategory = null;

    /**
     * Cart keyed by variant_id (string key).
     *
     * Shape per entry:
     * [
     *   'variant_id'   => int,
     *   'product_id'   => int,
     *   'product_name' => string,
     *   'variant_label'=> string,   // "42 / أحمر"  built from size + color
     *   'sku'          => string,
     *   'price_cents'  => int,      // stored as cents matching DB column
     *   'qty'          => int,
     *   'max_stock'    => int,
     * ]
     */
    public array $cart = [];

    public float $total = 0.0;      // in full currency units (cents / 100)

    public float $discount = 0.0;

    // ── Authorization ─────────────────────────────────────────────────────────

    public function mount(): void
    {
        $user = Auth::user();

        abort_unless(
            $user && in_array($user->role, ['admin', 'cashier'], true),
            403,
            'Access denied. POS Terminal requires admin or cashier role.'
        );
    }

    // ── Computed Properties ───────────────────────────────────────────────────

    #[Computed]
    public function products(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Product::query()
            ->where('is_available', true)   // ERD uses is_available (not is_active)
            ->whereNull('deleted_at')
            ->when(
                $this->selectedCategory,
                fn ($q) => $q->where('category_id', $this->selectedCategory)
            )
            ->when(
                $this->search !== '',
                function ($q) {
                    $term = '%' . $this->search . '%';
                    $q->where(function ($inner) use ($term) {
                        $inner->where('product_name', 'like', $term)
                              ->orWhereHas('variants', fn ($vq) =>
                                  $vq->where('sku', 'like', $term)       // search by SKU
                                     ->orWhere('size', 'like', $term)
                                     ->orWhere('color', 'like', $term)
                              );
                    });
                }
            )
            ->with([
                // Only load variants with stock > 0
                'variants' => fn ($q) => $q->where('stock', '>', 0)
                                           ->orderBy('size')
                                           ->orderBy('color'),
            ])
            ->orderBy('product_name')
            ->get();
    }

    #[Computed]
    public function categories(): \Illuminate\Database\Eloquent\Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Build a human-readable label from size + color columns.
     * e.g. "42 / أحمر"  |  "42"  |  "أحمر"  |  "—"
     */
    private function buildVariantLabel(ProductVariant $variant): string
    {
        $parts = array_filter([
            trim((string) $variant->size),
            trim((string) $variant->color),
        ]);

        return $parts ? implode(' / ', $parts) : '—';
    }

    /**
     * Convert price_cents (int) to float currency units.
     */
    private function centsToFloat(int $cents): float
    {
        return $cents / 100;
    }

    // ── Cart Actions ──────────────────────────────────────────────────────────

    public function addToCart(int $variantId): void
    {
        $variant = ProductVariant::with('product')->find($variantId);

        if (! $variant) {
            Notification::make()->title('المنتج غير موجود')->danger()->send();
            return;
        }

        if ($variant->stock <= 0) {
            Notification::make()->title('المنتج غير متوفر في المخزون')->warning()->send();
            return;
        }

        $key = (string) $variantId;

        if (isset($this->cart[$key])) {
            if ($this->cart[$key]['qty'] >= $variant->stock) {
                Notification::make()->title('وصلت للحد الأقصى المتاح')->warning()->send();
                return;
            }
            // Livewire v4: reassign whole entry to trigger reactivity
            $item        = $this->cart[$key];
            $item['qty'] = $item['qty'] + 1;
            $this->cart[$key] = $item;
        } else {
            $this->cart[$key] = [
                'variant_id'    => $variant->id,
                'product_id'    => $variant->product_id,
                'product_name'  => $variant->product->product_name,
                'variant_label' => $this->buildVariantLabel($variant),
                'sku'           => $variant->sku,
                'price_cents'   => (int) $variant->price_cents,
                'qty'           => 1,
                'max_stock'     => (int) $variant->stock,
            ];
        }

        $this->calculateTotal();
    }

    public function incrementQty(int $variantId): void
    {
        $key = (string) $variantId;

        if (! isset($this->cart[$key])) {
            return;
        }

        if ($this->cart[$key]['qty'] < $this->cart[$key]['max_stock']) {
            $item        = $this->cart[$key];
            $item['qty'] = $item['qty'] + 1;
            $this->cart[$key] = $item;
            $this->calculateTotal();
        } else {
            Notification::make()->title('وصلت للحد الأقصى المتاح')->warning()->send();
        }
    }

    public function decrementQty(int $variantId): void
    {
        $key = (string) $variantId;

        if (! isset($this->cart[$key])) {
            return;
        }

        if ($this->cart[$key]['qty'] > 1) {
            $item        = $this->cart[$key];
            $item['qty'] = $item['qty'] - 1;
            $this->cart[$key] = $item;
            $this->calculateTotal();
        } else {
            $this->removeItem($variantId);
        }
    }

    public function removeItem(int $variantId): void
    {
        unset($this->cart[(string) $variantId]);
        $this->calculateTotal();
    }

    public function clearCart(): void
    {
        $this->cart     = [];
        $this->discount = 0.0;
        $this->total    = 0.0;
    }

    public function calculateTotal(): void
    {
        // price_cents stored as int → convert to float for display
        $subtotal = collect($this->cart)
            ->sum(fn ($item) => $this->centsToFloat($item['price_cents']) * $item['qty']);

        $this->total = max(0.0, $subtotal - $this->discount);
    }

    public function updatedDiscount(): void
    {
        $this->calculateTotal();
    }

    // ── Checkout ──────────────────────────────────────────────────────────────

    public function checkout(): void
    {
        if (empty($this->cart)) {
            Notification::make()
                ->title('السلة فارغة')
                ->body('يرجى إضافة منتجات قبل إتمام الدفع.')
                ->warning()
                ->send();
            return;
        }

        DB::transaction(function () {
            // total_price_cents stored as int in DB
            $order = Order::create([
                'employee_id'       => Auth::id(),
                'customer_id'       => null,           // nullable — walk-in
                'payment_method'    => 'cash',
                'total_price_cents' => (int) round($this->total * 100),
                'created_at'        => now(),
            ]);

            foreach ($this->cart as $item) {
                OrderItem::create([
                    'order_id'            => $order->order_id,
                    'variant_id'          => $item['variant_id'],
                    'product_name'        => $item['product_name'],   // ERD has product_name on order_items
                    'current_price_cents' => $item['price_cents'],
                    'quantity'            => $item['qty'],
                ]);

                // Decrement stock atomically
                ProductVariant::where('variant_id', $item['variant_id'])
                    ->decrement('stock', $item['qty']);
            }
        });

        $this->clearCart();
        unset($this->products);   // bust computed cache so stock refreshes

        Notification::make()
            ->title('تم إتمام الطلب بنجاح ✓')
            ->body('تم حفظ الطلب وتحديث المخزون.')
            ->success()
            ->send();
    }
}
