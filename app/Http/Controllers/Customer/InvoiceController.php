<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    use AuthorizesRequests;

    public function download(Order $order)
    {
        $this->authorize('view', $order);
        $order->load(['orderItems.book', 'user']);

        $pdf = Pdf::loadView('customer.invoice', compact('order'));

        return $pdf->download('invoice_order_' . $order->id . '.pdf');
    }
}