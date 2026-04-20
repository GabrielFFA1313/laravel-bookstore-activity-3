<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>My Orders — {{ $user->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; padding: 30px; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; }
        .brand { font-size: 22px; font-weight: bold; color: #4f46e5; }
        .brand-sub { font-size: 11px; color: #888; margin-top: 2px; }
        .title-block { text-align: right; }
        .title-block h2 { font-size: 18px; font-weight: bold; color: #111; }
        .title-block p { font-size: 11px; color: #666; margin-top: 3px; }

        .summary { background: #f9fafb; border-radius: 6px; padding: 12px 16px; margin-bottom: 24px; }
        .summary p { font-size: 11px; color: #555; margin-bottom: 3px; }

        .order { border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 20px; overflow: hidden; }
        .order-header { background: #f3f4f6; padding: 10px 14px; display: flex; justify-content: space-between; }
        .order-header .left h3 { font-size: 13px; font-weight: bold; color: #111; }
        .order-header .left p { font-size: 10px; color: #777; margin-top: 2px; }
        .order-header .right { text-align: right; }
        .order-header .right .amount { font-size: 14px; font-weight: bold; color: #4f46e5; }

        .badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 9px; font-weight: bold; }
        .badge-pending    { background: #fef9c3; color: #92400e; }
        .badge-processing { background: #dbeafe; color: #1e40af; }
        .badge-shipped    { background: #e0e7ff; color: #3730a3; }
        .badge-delivered  { background: #dcfce7; color: #166534; }
        .badge-cancelled  { background: #fee2e2; color: #991b1b; }

        .order-body { padding: 12px 14px; }
        .shipping { font-size: 10px; color: #666; margin-bottom: 10px; }
        .shipping strong { color: #333; }

        table { width: 100%; border-collapse: collapse; }
        thead th { background: #f9fafb; padding: 6px 8px; text-align: left; font-size: 10px; color: #555; font-weight: bold; }
        tbody td { padding: 6px 8px; border-bottom: 1px solid #f3f4f6; font-size: 10px; }
        tbody tr:last-child td { border-bottom: none; }
        .text-right { text-align: right; }

        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #aaa; border-top: 1px solid #e5e7eb; padding-top: 12px; }
    </style>
</head>
<body>

    <div class="header">
        <div>
            <div class="brand">PageTurner</div>
            <div class="brand-sub">Your favorite bookstore</div>
        </div>
        <div class="title-block">
            <h2>Order History</h2>
            <p>{{ $user->name }} ({{ $user->email }})</p>
            <p>Generated {{ now()->format('F d, Y') }}</p>
        </div>
    </div>

    <div class="summary">
        <p><strong>Total Orders:</strong> {{ $orders->count() }}</p>
        <p><strong>Total Spent:</strong> ${{ number_format($orders->where('status', '!=', 'cancelled')->sum('total_amount'), 2) }}</p>
        <p><strong>Member Since:</strong> {{ $user->created_at->format('F d, Y') }}</p>
    </div>

    @forelse($orders as $order)
        <div class="order">
            <div class="order-header">
                <div class="left">
                    <h3>Order #{{ $order->id }}</h3>
                    <p>{{ $order->created_at->format('F d, Y \a\t g:i A') }}</p>
                </div>
                <div class="right">
                    <span class="badge badge-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                    <div class="amount">${{ number_format($order->total_amount, 2) }}</div>
                </div>
            </div>
            <div class="order-body">
                <div class="shipping">
                    <strong>Ship To:</strong>
                    {{ $order->shipping_name }} · {{ $order->shipping_phone }} ·
                    {{ $order->shipping_address }}, {{ $order->shipping_city }}, {{ $order->shipping_province }}
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Author</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->orderItems as $item)
                            <tr>
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
        </div>
    @empty
        <p style="text-align:center; color:#aaa; padding: 40px;">No orders found.</p>
    @endforelse

    <div class="footer">
        PageTurner Order History — Generated for {{ $user->name }} on {{ now()->format('F d, Y') }}
    </div>

</body>
</html>