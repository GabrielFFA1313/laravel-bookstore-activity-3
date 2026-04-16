<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\OrdersExport;
use App\Exports\RevenueExport;
use App\Models\Order;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OrderExportController extends Controller
{
    // ── ADMIN ORDER EXPORT ───────────────────────────────────

    public function showExportForm()
    {
        return view('admin.orders.export');
    }

    public function export(Request $request)
    {
        $request->validate([
            'status'    => 'nullable|in:pending,processing,shipped,delivered,cancelled',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date',
            'customer'  => 'nullable|string|max:100',
        ]);

        $filters  = $request->only(['status', 'date_from', 'date_to', 'customer']);
        $filename = 'orders_export_' . now()->format('Ymd_His') . '.csv';

        return Excel::download(new OrdersExport($filters), $filename, \Maatwebsite\Excel\Excel::CSV);
    }

    // ── FINANCIAL / REVENUE REPORT ───────────────────────────

    public function showRevenueForm()
    {
        // Summary stats for the form page
        $totalRevenue  = Order::where('status', '!=', 'cancelled')->sum('total_amount');
        $totalOrders   = Order::count();
        $monthRevenue  = Order::where('status', '!=', 'cancelled')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
        $monthOrders   = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('admin.orders.revenue', compact(
            'totalRevenue', 'totalOrders', 'monthRevenue', 'monthOrders'
        ));
    }

    public function exportRevenue(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
            'group_by'  => 'required|in:daily,monthly',
        ]);

        $filename = 'revenue_report_' . $request->date_from . '_to_' . $request->date_to . '.csv';

        return Excel::download(
            new RevenueExport($request->date_from, $request->date_to, $request->group_by),
            $filename,
            \Maatwebsite\Excel\Excel::CSV
        );
    }
}