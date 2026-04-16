<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 13px; color: #333; padding: 40px; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; }
        .brand { font-size: 26px; font-weight: bold; color: #4f46e5; }
        .brand-sub { font-size: 12px; color: #888; margin-top: 2px; }
        .invoice-title { text-align: right; }
        .invoice-title h2 { font-size: 22px; font-weight: bold; color: #111; }
        .invoice-title p { font-size: 12px; color: #666; margin-top: 4px; }

        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .badge-pending    { background: #fef9c3; color: #92400e; }
        .badge-processing { background: #dbeafe; color: #1e40af; }
        .badge-shipped    { background: #e0e7ff; color: #3730a3; }
        .badge-delivered  { background: #dcfce7; color: #166534; }
        .badge-cancelled  { background: #fee2e2; color: #991b1b; }

        .section { margin-bottom: 30px; }
        .section-title { font-size: 11px; font-weight: bold; color: #888; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }

        .two-col { display: flex; gap: 40px; }
        .two-col > div { flex: 1; }

        p.info { font-size: 13px; color: #444; line-height: 1.7; }
        p.info strong { color: #111; }

        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        thead th { background: #f3f4f6; padding: 10px 12px; text-align: left; font-size: 12px; color: #555; font-weight: bold; }
        tbody td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
        tbody tr:last-child td { border-bottom: none; }
        .text-right { text-align: right; }

        .totals { margin-top: 16px; }
        .totals table { width: 280px; margin-left: auto; }
        .totals td { padding: 6px 12px; font-size: 13px; }
        .totals .total-row td { font-size: 15px; font-weight: bold; color: #4f46e5; border-top: 2px solid #e5e7eb; padding-top: 10px; }

        .footer { margin-top: 50px; text-align: center; font-size: 11px; color: #aaa; border-top: 1px solid #e5e7eb; padding-top: 16px; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <div>
            <div class="brand">PageTurner</div>
            <div class="brand-sub">Your favorite bookstore</div>
        </div>
        <div class="invoice-title">
            <h2>INVOICE</h2>
            <p>Order #{{ $order->id }}</p>
            <p>{{ $order->created_at->format('F d, Y') }}</p>
            <br>
            <span class="badge badge-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
        </div>
    </div>

    {{-- Bill To & Ship To --}}
    <div class="section two-col">
        <div>
            <div class="section-title">Bill To</div>
            <p class="info">
                <strong>{{ $order->user->name }}</strong><br>
                {{ $order->user->email }}
            </p>
        </div>
        <div>
            <div class="section-title">Ship To</div>
            <p class="info">
                <strong>{{ $order->shipping_name }}</strong><br>
                {{ $order->shipping_phone }}<br>
                {{ $order->shipping_address }}<br>
                {{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal_code }}
            </p>
        </div>
    </div>

    {{-- Order Items --}}
    <div class="section">
        <div class="section-title">Order Items</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Book</th>
                    <th>Author</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->book->title }}</td>
                    <td>{{ $item->book->author }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Totals --}}
    <div class="totals">
        <table>
            <tr>
                <td>Subtotal</td>
                <td class="text-right">${{ number_format($order->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Shipping</td>
                <td class="text-right">Free</td>
            </tr>
            <tr class="total-row">
                <td>Total</td>
                <td class="text-right">${{ number_format($order->total_amount, 2) }}</td>
            </tr>
        </table>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>Thank you for shopping at PageTurner! &nbsp;|&nbsp; This is a computer-generated invoice.</p>
    </div>

</body>
</html>