<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: 'XBRiyaz', 'DejaVu Sans', sans-serif; text-align: right; direction: rtl; color: #131b2e; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #3525cd; padding-bottom: 10px; }
        .invoice-title { font-size: 24px; color: #3525cd; font-weight: bold; }
        .meta-section { margin-bottom: 25px; width: 100%; }
        .meta-box { width: 48%; display: inline-block; vertical-align: top; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th { background-color: #f2f3ff; color: #3525cd; padding: 10px; text-align: right; border: 1px solid #c7c4d8; }
        .table td { padding: 10px; border: 1px solid #c7c4d8; text-align: right; }
        .total-section { text-align: left; margin-top: 20px; font-size: 16px; font-weight: bold; }
    </style>
</head>
<body>

<div class="header">
    <div class="invoice-title">فاتورة مبيعات #{{ $invoice->id }}</div>
    <p>متجر K&H Shoes</p>
</div>

<div class="meta-section">
    <div class="meta-box">
        <strong>معلومات الفاتورة:</strong>
        <p>التاريخ: {{ $invoice->created_at->format('Y-m-d h:i A') }}</p>
        <p>طريقة الدفع: {{ $invoice->order?->payment_method == 'cash' ? 'نقدي' : 'فيزا / كارت' }}</p>
    </div>
    <div class="meta-box" style="text-align: left;">
        <strong>أطراف المعاملة:</strong>
        <p>الكاشير: {{ $invoice->order?->employee?->name ?? 'غير محدد' }}</p>
        <p>العميل: {{ $invoice->order?->customer?->name ?? 'عميل نقدي' }}</p>
    </div>
</div>

<table class="table">
    <thead>
    <tr>
        <th>المنتج</th>
        <th style="text-align: center;">الكمية</th>
        <th style="text-align: left;">السعر</th>
    </tr>
    </thead>
    <tbody>
    @if($invoice->order)
        @foreach($invoice->order->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td style="text-align: center;">{{ $item->quantity }}</td>
                <td style="text-align: left;">{{ number_format($item->current_price_cents / 100, 2) }} ج.م</td>
            </tr>
        @endforeach
    @endif
    </tbody>
</table>

<div class="total-section">
    <p>الخصم: {{ number_format($invoice->order?->discount ?? 0, 2) }} ج.م</p>
    <p style="color: #3525cd; font-size: 18px;">الإجمالي الصافي: {{ number_format(($invoice->order?->total_price_cents ?? 0) / 100, 2) }} ج.م</p>
</div>

</body>
</html>
