<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CustomerOrdersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function query()
    {
        return Order::with('orderItems.book')
            ->where('user_id', $this->userId)
            ->latest();
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Status',
            'Items',
            'Total Amount',
            'Shipping Name',
            'Shipping Address',
            'Shipping City',
            'Date Placed',
        ];
    }

    public function map($order): array
    {
        return [
            '#' . $order->id,
            ucfirst($order->status),
            $order->orderItems->map(fn($i) => $i->book->title . ' x' . $i->quantity)->implode(', '),
            number_format($order->total_amount, 2),
            $order->shipping_name,
            $order->shipping_address,
            $order->shipping_city . ', ' . $order->shipping_province,
            $order->created_at->format('Y-m-d'),
        ];
    }
}