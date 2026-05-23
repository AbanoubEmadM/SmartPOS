<!DOCTYPE html>
<html dir="rtl">
<head>
    <style>
        body {
            font-family: 'Aptos', 'DejaVu Sans', sans-serif;
            direction: rtl;
            text-align: right;
            color: #131b2e;
        }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #3525cd; padding-bottom: 10px; }
        .invoice-title { font-size: 24px; color: #3525cd; font-weight: bold; }
        .meta-table { width: 100%; margin-bottom: 25px; border: none; }
        .meta-td { width: 50%; vertical-align: top; border: none; font-size: 14px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th { background-color: #f2f3ff; color: #3525cd; padding: 10px; text-align: right; border: 1px solid #c7c4d8; font-weight: bold; }
        .table td { padding: 10px; border: 1px solid #c7c4d8; text-align: right; }
        .total-section { text-align: left; margin-top: 20px; font-size: 16px; font-weight: bold; }
    </style>
</head>
<body>

<div class="header">
    <div class="invoice-title">فاتورة مبيعات #{{ $invoice->id }}</div>
    <p style="font-size: 14px; color: #464555;">متجر K&H Shoes</p>
</div>

<table class="meta-table">
    <tr>
        <td class="meta-td">
            <strong>معلومات الفاتورة:</strong>
            <p>التاريخ: {{ $invoice->created_at->format('Y-m-d h:i A') }}</p>
            <p>طريقة الدفع: {{ $invoice->order?->payment_method == 'cash' ? 'نقدي (Cash)' : 'فيزا / كارت (Card)' }}</p>
        </td>
        <td class="meta-td" style="text-align: left;">
            <strong>أطراف المعاملة:</strong>
            <p>الكاشير: {{ $invoice->order?->employee?->name ?? 'غير محدد' }}</p>
            <p>العميل: {{ $invoice->order?->customer?->name ?? 'عميل نقدي' }}</p>
        </td>
    </tr>
</table>

<table class="table">
    <thead>
    <tr>
        <th>المنتج</th>
        <th style="text-align: center; width: 15%;">الكمية</th>
        <th style="text-align: left; width: 25%;">السعر</th>
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
    <p>الخصم: {{ number_format($invoice->order?->discount_cents / 100 ?? 0, 2) }} ج.م</p>
    <p style="color: #3525cd; font-size: 18px; margin-top: 5px;">الإجمالي الصافي: {{ number_format(($invoice->order?->total_price_cents ?? 0) / 100, 2) }} ج.م</p>
</div>

</body>
</html>
