<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RevenueExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    protected string $dateFrom;
    protected string $dateTo;
    protected string $groupBy; // 'daily', 'monthly'

    public function __construct(string $dateFrom, string $dateTo, string $groupBy = 'daily')
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo   = $dateTo;
        $this->groupBy  = $groupBy;
    }

    public function array(): array
    {
        $query = Order::query()
            ->where('status', '!=', 'cancelled')
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo);

        if ($this->groupBy === 'monthly') {
            $results = $query
                ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') as period, COUNT(*) as order_count, SUM(total_amount) as revenue")
                ->groupByRaw("TO_CHAR(created_at, 'YYYY-MM')")
                ->orderByRaw("TO_CHAR(created_at, 'YYYY-MM')")
                ->get();
        } else {
            $results = $query
                ->selectRaw("DATE(created_at) as period, COUNT(*) as order_count, SUM(total_amount) as revenue")
                ->groupByRaw("DATE(created_at)")
                ->orderByRaw("DATE(created_at)")
                ->get();
        }

        $rows = [];
        $totalRevenue = 0;
        $totalOrders  = 0;

        foreach ($results as $row) {
            $rows[] = [
                $row->period,
                $row->order_count,
                number_format($row->revenue, 2),
            ];
            $totalRevenue += $row->revenue;
            $totalOrders  += $row->order_count;
        }

        // Summary row
        $rows[] = ['', '', ''];
        $rows[] = ['TOTAL', $totalOrders, number_format($totalRevenue, 2)];

        return $rows;
    }

    public function headings(): array
    {
        return [
            $this->groupBy === 'monthly' ? 'Month' : 'Date',
            'Orders',
            'Revenue ($)',
        ];
    }

    public function title(): string
    {
        return 'Revenue Report';
    }
}