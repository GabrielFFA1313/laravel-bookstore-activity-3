<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OrdersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Order::query()->with(['user', 'orderItems.book']);

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['customer'])) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->filters['customer'] . '%')
                  ->orWhere('email', 'like', '%' . $this->filters['customer'] . '%');
            });
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Customer Name',
            'Customer Email',
            'Status',
            'Items',
            'Total Amount',
            'Shipping Name',
            'Shipping Address',
            'Shipping City',
            'Shipping Province',
            'Date Placed',
        ];
    }

    public function map($order): array
    {
        return [
            '#' . $order->id,
            $order->user->name,
            $order->user->email,
            ucfirst($order->status),
            $order->orderItems->sum('quantity'),
            number_format($order->total_amount, 2),
            $order->shipping_name,
            $order->shipping_address,
            $order->shipping_city,
            $order->shipping_province,
            $order->created_at->format('Y-m-d H:i'),
        ];
    }
}