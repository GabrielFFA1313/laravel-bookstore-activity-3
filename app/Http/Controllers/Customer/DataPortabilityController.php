<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomerOrdersExport;

class DataPortabilityController extends Controller
{
    // ── GDPR: Export all personal data as JSON ────────────────────────────
    public function index()
{
    return view('customer.data');
}

    public function exportMyData()
    {
        $user = Auth::user();

        $data = [
            'exported_at' => now()->toISOString(),
            'gdpr_note'   => 'This export contains all personal data held by PageTurner for your account.',
            'account' => [
                'id'                => $user->id,
                'name'              => $user->name,
                'email'             => $user->email,
                'role'              => $user->role,
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'created_at'        => $user->created_at->toISOString(),
                'two_factor_enabled'=> $user->hasTwoFactorEnabled(),
            ],
            'addresses' => $user->addresses->map(fn($a) => [
                'label'         => $a->label,
                'full_name'     => $a->full_name,
                'phone'         => $a->phone,
                'address_line_1'=> $a->address_line_1,
                'address_line_2'=> $a->address_line_2,
                'city'          => $a->city,
                'province'      => $a->province,
                'postal_code'   => $a->postal_code,
                'is_default'    => $a->is_default,
            ])->toArray(),
            'orders' => $user->orders()->with('orderItems.book')->get()->map(fn($order) => [
                'id'             => $order->id,
                'status'         => $order->status,
                'total_amount'   => $order->total_amount,
                'shipping_name'  => $order->shipping_name,
                'shipping_address'=> $order->shipping_address,
                'shipping_city'  => $order->shipping_city,
                'placed_at'      => $order->created_at->toISOString(),
                'items'          => $order->orderItems->map(fn($item) => [
                    'book_title'  => $item->book->title,
                    'book_author' => $item->book->author,
                    'quantity'    => $item->quantity,
                    'unit_price'  => $item->unit_price,
                    'subtotal'    => $item->subtotal,
                ])->toArray(),
            ])->toArray(),
            'reviews' => $user->reviews()->with('book')->get()->map(fn($review) => [
                'book_title' => $review->book->title,
                'rating'     => $review->rating,
                'comment'    => $review->comment,
                'created_at' => $review->created_at->toISOString(),
            ])->toArray(),
            'notifications_count' => $user->notifications()->count(),
        ];

        $filename = 'my_data_' . now()->format('Ymd_His') . '.json';

        return response(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 200)
    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
    ->header('Content-Type', 'application/json');
    }

    // ── Order History Export as PDF ───────────────────────────────────────

    public function exportOrdersPdf()
    {
        $user   = Auth::user();
        $orders = $user->orders()->with('orderItems.book')->latest()->get();

        $pdf = Pdf::loadView('customer.exports.orders_pdf', compact('orders', 'user'));

        return $pdf->download('my_orders_' . now()->format('Ymd') . '.pdf');
    }

    // ── Order History Export as CSV ───────────────────────────────────────

    public function exportOrdersCsv()
    {
        $user = Auth::user();
        return Excel::download(
            new CustomerOrdersExport($user->id),
            'my_orders_' . now()->format('Ymd') . '.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    // ── Purchase/Reading History Export ───────────────────────────────────

    public function exportReadingHistory()
    {
        $user = Auth::user();

        $purchasedBooks = \App\Models\Book::with('category')
            ->whereHas('orderItems.order', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where('status', '!=', 'cancelled');
            })
            ->withCount(['orderItems as times_purchased' => function ($q) use ($user) {
                $q->whereHas('order', fn($o) => $o->where('user_id', $user->id));
            }])
            ->get()
            ->map(fn($book) => [
                'title'           => $book->title,
                'author'          => $book->author,
                'category'        => $book->category?->name,
                'times_purchased' => $book->times_purchased,
                'price'           => $book->price,
            ]);

        $data = [
            'exported_at'     => now()->toISOString(),
            'user'            => $user->name,
            'total_books'     => $purchasedBooks->count(),
            'purchased_books' => $purchasedBooks->toArray(),
        ];

        return response(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 200)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'application/json');
    }
}