<?php

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use function Livewire\Volt\{state, computed, updated};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

state([
    'search' => '',
    'selectedCategory' => null,
    'cart' => [],
    'total' => 0.0,
    'discount' => 0.0,

    'customer_phone' => '',
    'customer_name' => '',
    'existing_customer' => null,
]);

updated([
    'customer_phone' => function ($value) {
        if (strlen($value) >= 10) {
            $this->existing_customer = Customer::where('phone', $value)->first();
            if ($this->existing_customer) {
                $this->customer_name = $this->existing_customer->name;
            } else {
                $this->customer_name = '';
            }
        } else {
            $this->existing_customer = null;
        }
    },
    'discount' => function () {
        $this->discount = max(0.0, (float) $this->discount);
        $this->calculateTotal();
    },
]);

$products = computed(function () {
    return Product::query()
        ->where('is_available', true)
        ->whereHas('variants', fn($q) => $q->where('stock', '>', 0))
        ->when($this->selectedCategory, fn($q) => $q->where('category_id', $this->selectedCategory))
        ->when($this->search !== '', function ($q) {
            $term = '%' . $this->search . '%';
            $q->where(function ($inner) use ($term) {
                $inner->where('product_name', 'like', $term)->orWhereHas('variants', fn($vq) => $vq->where('sku', 'like', $term));
            });
        })
        ->with(['variants' => fn($q) => $q->where('stock', '>', 0)])
        ->get();
});

$categories = computed(function () {
    return Category::query()->where('is_active', true)->get();
});

$addToCart = function (int $variantId) {
    $variant = ProductVariant::with('product')->where('id', $variantId)->first();
    if (!$variant || $variant->stock <= 0 || !$variant->product->is_available) {
        return;
    }

    $price = $variant->price_cents / 100;

    if (isset($this->cart[$variantId])) {
        if ($this->cart[$variantId]['qty'] >= $variant->stock) {
            return;
        }
        $this->cart[$variantId]['qty']++;
    } else {
        $variantDetails = array_filter([$variant->size, $variant->color]);
        $variantName = !empty($variantDetails) ? ' (' . implode(' - ', $variantDetails) . ')' : '';

        $this->cart[$variantId] = [
            'id' => $variant->id,
            'product_id' => $variant->product_id,
            'name' => $variant->product->product_name . $variantName,
            'price' => $variant->price_cents / 100,
            'qty' => 1,
            'max_stock' => $variant->stock,
        ];
    }
    $this->calculateTotal();
};

$incrementQty = function (int $variantId) {
    if (isset($this->cart[$variantId]) && $this->cart[$variantId]['qty'] < $this->cart[$variantId]['max_stock']) {
        $this->cart[$variantId]['qty']++;
        $this->calculateTotal();
    }
};

$decrementQty = function (int $variantId) {
    if (isset($this->cart[$variantId])) {
        if ($this->cart[$variantId]['qty'] > 1) {
            $this->cart[$variantId]['qty']--;
            $this->calculateTotal();
        } else {
            $this->removeItem($variantId);
        }
    }
};

$removeItem = function (int $variantId) {
    unset($this->cart[$variantId]);
    $this->calculateTotal();
};

$calculateTotal = function () {
    $subtotal = collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']);
    $this->total = max(0.0, $subtotal - $this->discount);
};

$checkout = function () {
    if (empty($this->cart)) {
        return;
    }

    if (empty(trim($this->customer_phone)) || empty(trim($this->customer_name))) {
        session()->flash('error', 'عذراً، يجب إدخال رقم هاتف واسم العميل لإتمام الفاتورة!');
        return;
    }

    DB::transaction(function () {
        $customerId = null;

        if ($this->existing_customer) {
            $customerId = $this->existing_customer->id;
        } else {
            $newCustomer = Customer::create([
                'name' => trim($this->customer_name),
                'phone' => trim($this->customer_phone),
                'created_at' => now(),
            ]);
            $customerId = $newCustomer->id;
        }

        $order = Order::create([
            'employee_id' => Auth::id() ?? 1,
            'customer_id' => $customerId,
            'payment_method' => 'cash',
            'total_price_cents' => (int) round($this->total * 100),
            'created_at' => now(),
        ]);

        foreach ($this->cart as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'variant_id' => $item['id'],
                'product_name' => $item['name'],
                'current_price_cents' => (int) round($item['price'] * 100),
                'quantity' => $item['qty'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            ProductVariant::where('id', $item['id'])->decrement('stock', $item['qty']);
        }
    });

    $this->cart = [];
    $this->total = 0.0;
    $this->discount = 0.0;
    $this->customer_phone = '';
    $this->customer_name = '';
    $this->existing_customer = null;

    session()->flash('message', 'تم إتمام البيع بنجاح وتحديث بيانات العميل والمخزن!');
};
?>

<div class="min-h-screen w-full bg-gray-100 flex flex-col overflow-hidden text-gray-800 select-none" dir="rtl">
    <script src="https://cdn.tailwindcss.com"></script>

    <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-3 flex justify-between items-center flex-shrink-0">
        <div class="flex items-center gap-3">
            <span class="text-2xl">🏪</span>
            <h1 class="text-xl font-black text-gray-900">SmartPOS <span class="text-amber-600 text-sm font-medium">v4
                    Terminal</span></h1>
        </div>
        @if (session()->has('message'))
            <div
                class="bg-green-100 border border-green-400 text-green-700 px-4 py-1.5 rounded-lg text-sm font-bold animate-pulse">
                {{ session('message') }}
            </div>
        @endif
        <div>
            <a href="/admin"
                class="text-sm font-semibold text-gray-500 hover:text-amber-600 bg-gray-50 px-4 py-2 rounded-xl border border-gray-200 transition">العودة
                للوحة التحكم ←</a>
        </div>
    </div>

    <div class="flex-1 flex overflow-hidden">
        <div class="w-3/5 p-4 flex flex-col gap-4 overflow-y-auto h-[calc(100vh-65px)]">
            <input type="text" wire:model.live="search" placeholder="ابحث باسم المنتج أو الـ SKU المميز..."
                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none shadow-sm">

            <div class="flex gap-2 overflow-x-auto pb-1">
                <button wire:click="$set('selectedCategory', null)"
                    class="px-5 py-2 rounded-xl text-sm font-bold border shadow-sm {{ is_null($selectedCategory) ? 'bg-amber-600 text-white' : 'bg-white text-gray-600' }}">الكل</button>
                @foreach ($this->categories as $cat)
                    <button wire:click="$set('selectedCategory', {{ $cat->category_id }})"
                        class="px-5 py-2 rounded-xl text-sm font-bold border shadow-sm {{ $selectedCategory == $cat->category_id ? 'bg-amber-600 text-white' : 'bg-white text-gray-600' }}">{{ $cat->name }}</button>
                @endforeach
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($this->products as $product)
                    <div
                        class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 flex flex-col justify-between min-h-[160px]">
                        <span
                            class="font-extrabold text-gray-900 text-base block leading-tight">{{ $product->product_name }}</span>
                        <div class="flex flex-col gap-1.5 mt-3">
                            @foreach ($product->variants as $variant)
                                <button wire:click="addToCart({{ $variant->id }})"
                                    class="w-full flex justify-between items-center bg-gray-50 hover:bg-amber-50 border border-gray-200 p-2 rounded-xl text-xs transition">
                                    <span class="font-bold text-gray-700">{{ $variant->size }}
                                        {{ $variant->color }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-400">المخزن: {{ $variant->stock }}</span>
                                        <span
                                            class="text-amber-600 font-black">{{ number_format($variant->price_cents / 100, 2) }}
                                            ج.م</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="w-2/5 bg-white border-r border-gray-200 flex flex-col shadow-xl h-[calc(100vh-65px)]">
            <div class="p-4 border-b bg-gray-50 font-black text-lg">🛒 السلة الحالية ({{ count($cart) }})</div>

            <div class="flex-1 overflow-y-auto p-4 flex flex-col gap-3">
                @forelse($cart as $id => $item)
                    <div class="flex justify-between items-center bg-gray-50 p-3 rounded-xl border border-gray-200">
                        <div class="flex-1 min-w-0 ml-2">
                            <span class="font-bold text-sm block truncate text-gray-900">{{ $item['name'] }}</span>
                            <span
                                class="text-xs font-black text-amber-600 block mt-0.5">{{ number_format($item['price'], 2) }}
                                ج.م</span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-white border rounded-lg p-1 shadow-sm">
                            <button wire:click="decrementQty({{ $id }})"
                                class="w-6 h-6 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded font-bold text-sm">-</button>
                            <span class="w-6 text-center text-sm font-mono font-bold">{{ $item['qty'] }}</span>
                            <button wire:click="incrementQty({{ $id }})"
                                class="w-6 h-6 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded font-bold text-sm">+</button>
                        </div>
                        <div class="text-left min-w-[70px] mr-2">
                            <span
                                class="font-black text-sm text-gray-900 block">{{ number_format($item['price'] * $item['qty'], 2) }}</span>
                            <button wire:click="removeItem({{ $id }})"
                                class="text-[10px] text-red-500 hover:underline">حذف</button>
                        </div>
                    </div>
                @empty
                    <div class="h-full flex flex-col items-center justify-center text-gray-400 font-bold gap-2">
                        <span class="text-4xl">📥</span>
                        <span>السلة فارغة</span>
                    </div>
                @endforelse
            </div>

            <div class="p-4 border-t border-b bg-gray-50/70 flex flex-col gap-2 flex-shrink-0">
                <span class="text-xs font-bold text-gray-500 block"> بيانات العميل (اختياري):</span>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <input type="text" wire:model.live="customer_phone" placeholder="رقم الهاتف..."
                            class="w-full text-right px-3 py-2 border border-gray-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-amber-500 focus:outline-none shadow-sm bg-white">
                    </div>
                    <div>
                        <input type="text" wire:model.live="customer_name" placeholder="اسم العميل الكامل..."
                            {{ $existing_customer ? 'disabled' : '' }}
                            class="w-full text-right px-3 py-2 border rounded-xl text-xs font-semibold focus:ring-2 focus:ring-amber-500 focus:outline-none shadow-sm {{ $existing_customer ? 'bg-green-50 text-green-700 font-bold border-green-300' : 'bg-white border-gray-200 text-gray-800' }}">
                    </div>
                </div>
                @if ($existing_customer)
                    <span class="text-[10px] text-green-600 font-bold flex items-center gap-1"> عميل مسجل بالنظام
                        ({{ $existing_customer->name }})</span>
                @elseif(!empty($customer_phone) && strlen($customer_phone) >= 10)
                    <span class="text-[10px] text-amber-600 font-bold flex items-center gap-1">عميل جديد (سيتم حفظه
                        تلقائياً بالنظام عند الدفع)</span>
                @endif
            </div>

            <div class="p-4 bg-gray-50 flex flex-col gap-3 flex-shrink-0">

                @if (session()->has('error'))
                    <div
                        class="bg-red-100 border border-red-400 text-red-700 px-3 py-2 rounded-xl text-xs font-bold text-center">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="flex justify-between items-center bg-white p-2 rounded-xl border border-gray-200 shadow-sm">
                    <span class="text-xs font-bold text-gray-500 mr-2">تطبيق خصم (ج.م):</span>
                    <input type="number" wire:model.live="discount" min="0" placeholder="0.00"
                        class="w-24 text-left px-2 py-1 border rounded-lg text-sm font-black focus:outline-none">
                </div>

                <div class="flex justify-between items-center font-black text-gray-900 px-2 my-1">
                    <span>الصافي الإجمالي:</span>
                    <span class="text-xl text-amber-600 font-mono">{{ number_format($total, 2) }} ج.م</span>
                </div>

                <button wire:click="checkout" @if (empty($cart) || empty(trim($customer_phone)) || empty(trim($customer_name))) disabled @endif
                    class="w-full py-3.5 bg-amber-600 hover:bg-amber-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-black text-center text-lg rounded-xl shadow transition">
                     تأكيد الدفع وحفظ الفاتورة
                </button>
            </div>
        </div>
    </div>
</div>
