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
    'payment_method' => 'cash',
]);

updated(['customer_phone' => function ($value) {
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
}]);

$products = computed(function () {
    return Product::query()
        ->where('is_available', true)
        ->whereHas('variants', fn ($q) => $q->where('stock', '>', 0))
        ->when($this->selectedCategory, fn ($q) => $q->where('category_id', $this->selectedCategory))
        ->when($this->search !== '', function ($q) {
            $term = '%' . $this->search . '%';
            $q->where(function ($inner) use ($term) {
                $inner->where('product_name', 'like', $term)
                    ->orWhereHas('variants', fn ($vq) => $vq->where('sku', 'like', $term));
            });
        })
        ->with(['variants' => fn ($q) => $q->where('stock', '>', 0)])
        ->get();
});

$categories = computed(function () {
    return Category::query()->where('is_active', true)->get();
});

$addToCart = function (int $variantId) {
    $variant = ProductVariant::with('product')->where('id', $variantId)->first();
    if (! $variant || $variant->stock <= 0 || !$variant->product->is_available) return;

    if (isset($this->cart[$variantId])) {
        if ($this->cart[$variantId]['qty'] >= $variant->stock) return;
        $this->cart[$variantId]['qty']++;
    } else {
        $variantDetails = array_filter([$variant->size, $variant->color]);
        $variantName = !empty($variantDetails) ? ' (' . implode(' - ', $variantDetails) . ')' : '';

        $imagePath = $variant->image_path
            ? $variant->image_path
            : ($variant->product->product_img ? $variant->product->product_img : null);

        $this->cart[$variantId] = [
            'id' => $variant->id,
            'product_id' => $variant->product_id,
            'name'       => $variant->product->product_name . $variantName,
            'price'      => $variant->price_cents / 100,
            'qty'        => 1,
            'max_stock'  => $variant->stock,
            'image'      => $imagePath,
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
    $subtotal = collect($this->cart)->sum(fn ($item) => $item['price'] * $item['qty']);
    $this->total = max(0.0, $subtotal - (float)$this->discount);
};

updated(['discount' => function () { $this->calculateTotal(); }]);

$checkout = function () {
    if (empty($this->cart)) return;
    if (empty(trim($this->customer_phone)) || empty(trim($this->customer_name))) return;

    $order = null;

    DB::transaction(function () use (&$order) {
        $customerId = null;
        if ($this->existing_customer) {
            $customerId = $this->existing_customer->id;
        } else {
            $newCustomer = Customer::create([
                'name' => trim($this->customer_name),
                'phone' => trim($this->customer_phone),
            ]);
            $customerId = $newCustomer->id;
        }

        $invoice = \App\Models\Invoice::create();

        $order = Order::create([
            'employee_id'       => Auth::id() ?? 1,
            'customer_id'       => $customerId,
            'invoice_id'        => $invoice->id,
            'payment_method'    => $this->payment_method,
            'total_price_cents' => (int) round($this->total * 100),
            'created_at'        => now(),
        ]);

        foreach ($this->cart as $item) {
            OrderItem::create([
                'order_id'            => $order->id,
                'variant_id'          => $item['id'],
                'product_name'        => $item['name'],
                'current_price_cents' => (int) round($item['price'] * 100),
                'quantity'            => $item['qty'],
            ]);
            ProductVariant::where('id', $item['id'])->decrement('stock', $item['qty']);
        }
    });

    $order->load(['employee', 'customer', 'items.variant']);

    $receiptData = [
        'order_id'       => $order->id,
        'date'           => $order->created_at->format('Y-m-d h:i A'),
        'cashier_name'   => $order->employee->name ?? Auth::user()->name ?? 'الكاشير الكابتن',
        'customer_name'  => $order->customer->name ?? 'عميل نقدي',
        'payment_method' => $order->payment_method == 'cash' ? 'نقدي (Cash)' : 'فيزا / كارت (Card)',
        'discount'       => number_format($this->discount, 2),
        'total'          => number_format($this->total, 2),
        'items'          => collect($this->cart)->values()->toArray(),
    ];

    $this->dispatch('trigger-print-receipt', receipt: $receiptData);

    $this->cart = []; $this->total = 0.0; $this->discount = 0.0;
    $this->customer_phone = ''; $this->customer_name = ''; $this->existing_customer = null;
    $this->payment_method = 'cash';
    session()->flash('message', 'تم الفاتورة وبدء الطباعة! ✓');
};
?>
<div>
<div class="bg-[#d2d9f4] overflow-hidden h-screen w-screen flex flex-col antialiased text-[#131b2e]" dir="rtl">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

    <style>
        body { font-family: 'IBM Plex Sans Arabic', sans-serif; }
        .data-font { font-family: 'JetBrains Mono', monospace; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .cart-scroll::-webkit-scrollbar { width: 6px; }
        .cart-scroll::-webkit-scrollbar-thumb { background: #c7c4d8; border-radius: 10px; }

        @media screen {
            #thermal-receipt-template {
                display: none !important;
            }
        }

        @media print {
            body > * {
                display: none !important;
            }

            #thermal-receipt-template {
                display: block !important;
                position: absolute;
                left: 0;
                top: 0;
                width: 100% !important;
                max-width: 80mm !important;
                padding: 10px !important;
                margin: 0 auto !important;
                direction: rtl !important;
                background: white !important;
                color: black !important;
            }

            #thermal-receipt-template table {
                display: table !important;
                width: 100% !important;
            }
            #thermal-receipt-template tr {
                display: table-row !important;
            }
            #thermal-receipt-template th,
            #thermal-receipt-template td {
                display: table-cell !important;
                color: black !important;
            }

            @page {
                margin: 0;
                size: auto;
            }
        }
    </style>

    <header class="fixed top-0 left-0 right-0 z-50 flex items-center justify-between px-6 bg-white h-16 border-b border-[#c7c4d8] shadow-sm">
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-3 pr-4 border-l border-[#c7c4d8]">
                <span class="material-symbols-outlined text-[#3525cd] text-[32px]">account_circle</span>
                <div class="text-right leading-tight">
                    <p class="text-sm font-bold text-[#131b2e]">{{ Auth::user()->name ?? 'مستخدم متصل' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 text-[#464555] text-sm bg-[#e2e7ff] px-4 py-1.5 rounded-full">
                <span class="data-font font-bold" id="digital-clock">{{ now()->format('H:i') }}</span>
                <span class="material-symbols-outlined text-[18px]">schedule</span>
            </div>
        </div>

        <span class="text-xl font-bold text-[#3525cd]">K&H Shoes</span>

        <div>
            <a href="/admin" class="text-sm font-bold text-gray-500 hover:text-[#3525cd] transition bg-gray-50 px-5 py-2.5 rounded-xl border border-gray-200 shadow-sm">لوحة التحكم ←</a>
        </div>
    </header>

    <main class="flex-1 mt-16 flex flex-row overflow-hidden">

        <section class="w-3/5 flex flex-col bg-[#faf8ff] border-l border-[#c7c4d8] overflow-hidden">
            <div class="p-4 space-y-4 bg-[#faf8ff] shadow-sm z-10">
                <div class="relative">
                    <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-[#777587] text-xl">search</span>
                    <input wire:model.live="search" class="w-full bg-[#f2f3ff] border border-[#c7c4d8] rounded-full py-3 pr-12 pl-4 text-sm focus:outline-none focus:ring-2 focus:ring-[#3525cd] transition-all" placeholder="محرك البحث عن المنتجات (الاسم، SKU، أو الباركود)..." type="text">
                </div>

                <div class="flex gap-2 overflow-x-auto no-scrollbar pb-1">
                    <button wire:click="$set('selectedCategory', null)" class="px-6 py-2.5 rounded-full text-sm font-semibold transition-all {{ is_null($selectedCategory) ? 'bg-[#3525cd] text-white shadow-md' : 'bg-[#e2e7ff] text-[#464555] hover:bg-[#dae2fd]' }}">الكل</button>
                    @foreach($this->categories as $cat)
                        <button wire:click="$set('selectedCategory', {{ $cat->id }})" class="px-6 py-2.5 rounded-full text-sm font-semibold transition-all {{ $selectedCategory == $cat->id ? 'bg-[#3525cd] text-white shadow-md' : 'bg-[#e2e7ff] text-[#464555] hover:bg-[#dae2fd]' }}">{{ $cat->name }}</button>
                    @endforeach
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-4 grid grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($this->products as $product)
                    @php
                        $mainProductImage = $product->product_img
                            ? asset('storage/' . $product->product_img)
                            : asset('images/default-product.png');
                    @endphp

                    <div class="bg-white rounded-2xl border border-[#c7c4d8] p-4 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                        <div class="aspect-square rounded-xl bg-[#eaedff] overflow-hidden mb-3 relative border border-gray-50 shadow-sm">
                            <img src="{{ $mainProductImage }}" class="w-full h-full object-cover transition-transform duration-300 transform hover:scale-105" alt="{{ $product->product_name }}">
                            <span class="absolute top-2 right-2 bg-black/60 text-white text-[11px] px-2.5 py-1 rounded-full backdrop-blur-sm data-font font-medium">
                                {{ $product->variants->count() }} خيارات
                            </span>
                        </div>

                        <div class="space-y-3">
                            <h3 class="text-sm font-bold text-[#131b2e] truncate">{{ $product->product_name }}</h3>

                            <div class="flex flex-col gap-1.5">
                                @foreach($product->variants as $variant)
                                    <button wire:click="addToCart({{ $variant->id }})" class="w-full flex justify-between items-center bg-[#f2f3ff] hover:bg-[#eaedff] focus:bg-[#eaedff] focus:ring-1 focus:ring-[#3525cd] border border-[#c7c4d8] p-2 rounded-xl text-xs font-medium transition group outline-none">
                                        <span class="text-gray-800 font-medium">{{ $variant->size }} {{ $variant->color }}</span>
                                        <div class="flex items-center gap-2">
                                            <span class="text-gray-400 text-[11px]">مخزن: {{ $variant->stock }}</span>
                                            <span class="text-[#3525cd] font-bold data-font">{{ number_format($variant->price_cents / 100, 0) }} ج.م</span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="w-2/5 flex flex-col bg-[#f2f3ff] relative border-r border-[#c7c4d8] shadow-lg z-20">
            <div class="p-4 border-b border-[#c7c4d8] flex items-center justify-between bg-[#eaedff]">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#3525cd] text-2xl">shopping_cart</span>
                    <h2 class="font-bold text-sm text-[#131b2e]">سلة المشتريات</h2>
                </div>
                <span class="bg-[#3525cd] text-white text-xs font-bold px-3.5 py-1 rounded-full">{{ count($cart) }} عناصر</span>
            </div>

            <div class="flex-1 overflow-y-auto cart-scroll p-4 space-y-3">
                @forelse($cart as $id => $item)
                    <div class="bg-white p-3.5 rounded-2xl border border-[#c7c4d8] shadow-sm flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="w-14 h-14 rounded-xl bg-[#e2e7ff] overflow-hidden flex-shrink-0 border border-gray-100 shadow-sm flex items-center justify-center">
                                @if($item['image'])
                                    <img src="{{ asset('storage/' . $item['image']) }}" class="w-full h-full object-cover" alt="{{ $item['name'] }}">
                                @else
                                    <span class="material-symbols-outlined text-[#3525cd] text-2xl">inventory_2</span>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <h4 class="font-bold text-xs text-[#131b2e] truncate">{{ $item['name'] }}</h4>
                                <span class="text-xs font-bold text-[#3525cd] data-font block mt-1">
                                    {{ number_format($item['price'], 2) }} ج.م
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="flex items-center bg-[#f2f3ff] rounded-full p-1 border border-[#c7c4d8]">
                                <button wire:click="decrementQty({{ $id }})" class="w-7 h-7 flex items-center justify-center bg-white hover:bg-gray-100 rounded-full font-bold text-sm shadow-sm transition">-</button>
                                <span class="px-3 font-bold text-sm data-font text-[#131b2e]">{{ $item['qty'] }}</span>
                                <button wire:click="incrementQty({{ $id }})" class="w-7 h-7 flex items-center justify-center bg-white hover:bg-gray-100 rounded-full font-bold text-sm shadow-sm transition">+</button>
                            </div>
                            <button wire:click="removeItem({{ $id }})" class="text-red-500 hover:bg-red-50 p-2 rounded-full transition">
                                <span class="material-symbols-outlined text-base">delete</span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="h-full flex flex-col items-center justify-center text-gray-400 gap-2 py-24">
                        <span class="text-5xl">📥</span>
                        <span class="text-sm font-semibold mt-2">السلة فارغة، أضف مبيعاتك الآن</span>
                    </div>
                @endforelse
            </div>

            <div class="p-4 border-y border-[#c7c4d8] bg-[#eaedff] space-y-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-red-500 text-xl">person_alert</span>
                    <h3 class="font-bold text-sm text-[#131b2e]">بيانات العميل الإلزامية لتأكيد الفاتورة</h3>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-[#464555] mr-1">رقم الهاتف المميز *</label>
                        <input wire:model.live="customer_phone" class="w-full bg-white border border-[#c7c4d8] rounded-xl px-4 py-2.5 text-sm font-semibold focus:border-[#3525cd] focus:ring-1 focus:ring-[#3525cd] outline-none transition-all" placeholder="01xxxxxxxxx" type="tel">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-[#464555] mr-1">اسم العميل بالكامل *</label>
                        <input wire:model.live="customer_name" {{ $existing_customer ? 'disabled' : '' }} class="w-full border rounded-xl px-4 py-2.5 text-sm font-semibold focus:border-[#3525cd] focus:ring-1 focus:ring-[#3525cd] outline-none transition-all {{ $existing_customer ? 'bg-green-50 text-green-700 font-bold border-green-300' : 'bg-white border-[#c7c4d8]' }}" placeholder="اكتب اسم العميل هنا..." type="text">
                    </div>
                </div>
                @if($existing_customer)
                    <p class="text-xs text-green-600 font-bold mt-1 flex items-center gap-1">✓ عميل مسجل: {{ $existing_customer->name }}</p>
                @endif
            </div>

            <div class="p-4 bg-[#131b2e] text-white flex flex-col gap-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-base">sell</span>
                        <input wire:model.live="discount" class="w-full bg-white/10 border border-white/20 rounded-xl py-2.5 pr-10 pl-3 text-sm text-white placeholder-white/40 focus:outline-none focus:border-[#4f46e5] transition-all" placeholder="تطبيق خصم ج.م" type="number" min="0">
                    </div>

                    <div class="relative">
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-base">
                            {{ $payment_method === 'cash' ? 'payments' : 'credit_card' }}
                        </span>
                        <select wire:model.live="payment_method" class="w-full bg-white/10 border border-white/20 rounded-xl py-2.5 pr-10 pl-3 text-sm text-white focus:outline-none focus:border-[#4f46e5] transition-all appearance-none cursor-pointer">
                            <option value="cash" class="text-[#131b2e]">نقدي (Cash)</option>
                            <option value="card" class="text-[#131b2e]">فيزا / كارت (Card)</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4 border-t border-white/10 pt-3.5">
                    <div class="text-right flex-shrink-0">
                        <p class="text-xs text-white/50 mb-0.5">الإجمالي المستحق</p>
                        <p class="text-2xl font-bold data-font text-white leading-none">{{ number_format($total, 2) }} <span class="text-sm font-normal">ج.م</span></p>
                    </div>

                    <button wire:click="checkout"
                            @if(empty($cart) || empty(trim($customer_phone)) || empty(trim($customer_name))) disabled @endif
                            class="flex-1 bg-[#4f46e5] hover:bg-[#3525cd] disabled:bg-gray-800 disabled:text-gray-500 disabled:cursor-not-allowed text-white py-3.5 rounded-xl flex items-center justify-center gap-2 active:scale-[0.98] transition-all shadow-lg font-bold text-sm">
                        <span>إتمام وطباعة الفاتورة</span>
                        <span class="material-symbols-outlined text-xl">print</span>
                    </button>
                </div>
            </div>
        </section>
    </main>
</div> <div id="thermal-receipt-template" class="block bg-white text-black p-4 w-[80mm] mx-auto text-sm leading-tight text-right">
    <div class="text-center space-y-1 border-b border-dashed border-black pb-3">
        <h1 class="text-base font-bold tracking-tight">K&H Shoes</h1>
        <p class="text-xs text-gray-600">فاتورة بيع تبسيطية</p>
    </div>

    <div class="py-3 border-b border-dashed border-black space-y-1 text-xs">
        <div class="flex justify-between">
            <span>رقم الفاتورة:</span>
            <span class="font-bold data-font" id="r-order-id">#0000</span>
        </div>
        <div class="flex justify-between">
            <span>التاريخ والوقت:</span>
            <span class="data-font" id="r-date">2026-05-21 12:00 PM</span>
        </div>
        <div class="flex justify-between">
            <span>الكاشير:</span>
            <span id="r-cashier">{{Auth::user()->name}}}</span>
        </div>
        <div class="flex justify-between">
            <span>العميل:</span>
            <span id="r-customer">{{$customer_name}}</span>
        </div>
    </div>

    <div class="py-3 border-b border-dashed border-black">
        <table class="w-full text-xs text-right">
            <thead>
            <tr class="font-bold border-b border-black">
                <th class="pb-1 text-right">المنتج</th>
                <th class="pb-1 text-center">الكمية</th>
                <th class="pb-1 text-left">السعر</th>
            </tr>
            </thead>
            <tbody id="r-items-body">
            </tbody>
        </table>
    </div>

    <div class="py-3 space-y-1.5 text-xs">
        <div class="flex justify-between">
            <span>طريقة الدفع:</span>
            <span id="r-payment-method" class="font-medium">Cash</span>
        </div>
        <div class="flex justify-between text-gray-700">
            <span>الخصم المطبق:</span>
            <span class="data-font" id="r-discount">0.00 ج.م</span>
        </div>
        <div class="flex justify-between text-base font-bold pt-1 border-t border-black">
            <span>الصـافي المستـحق:</span>
            <span class="data-font" id="r-total">0.00 ج.م</span>
        </div>
    </div>

    <div class="text-center pt-4 border-t border-dashed border-black mt-3 space-y-1">
        <p class="text-[11px] font-bold">شكراً لزيارتكم وثقتكم بنا!</p>
        <p class="text-[9px] text-gray-500">نظام المبيعات المتطور المتكامل</p>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('trigger-print-receipt', (event) => {
            const receipt = event.receipt;

            // 1. حقن البيانات الأساسية
            document.getElementById('r-order-id').innerText = '#' + receipt.order_id;
            document.getElementById('r-date').innerText = receipt.date;
            document.getElementById('r-cashier').innerText = receipt.cashier_name;
            document.getElementById('r-customer').innerText = receipt.customer_name;
            document.getElementById('r-payment-method').innerText = receipt.payment_method;
            document.getElementById('r-discount').innerText = receipt.discount + ' ج.م';
            document.getElementById('r-total').innerText = receipt.total + ' ج.م';

            // 2. حقن عناصر المنتجات
            const itemsBody = document.getElementById('r-items-body');
            itemsBody.innerHTML = '';

            receipt.items.forEach(item => {
                const row = document.createElement('tr');
                row.className = 'border-b border-gray-100 last:border-0';

                row.innerHTML = `
                    <td class="py-1.5 font-medium text-right">${item.name}</td>
                    <td class="py-1.5 text-center data-font">${item.qty}</td>
                    <td class="py-1.5 text-left data-font">${Number(item.price).toFixed(2)} ج.م</td>
                `;
                itemsBody.appendChild(row);
            });

            // 3. أمر نافذة الطباعة بعد استقرار الـ DOM تماماً
            requestAnimationFrame(() => {
                setTimeout(() => {
                    window.print();
                }, 350);
            });
        });
    });
</script>
</div>
