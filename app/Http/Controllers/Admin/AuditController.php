<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AuditExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('audits')
            ->leftJoin('users', function ($join) {
                $join->on('audits.user_id', '=', 'users.id')
                     ->where('audits.user_type', '=', 'App\\Models\\User');
            })
            ->select('audits.*', 'users.name as user_name', 'users.email as user_email');

        if ($request->filled('event')) {
            $query->where('audits.event', $request->event);
        }

        if ($request->filled('auditable_type')) {
            $query->where('audits.auditable_type', 'like', '%' . $request->auditable_type . '%');
        }

        if ($request->filled('user_id')) {
            $query->where('audits.user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('audits.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('audits.created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('audits.auditable_type', 'like', '%' . $request->search . '%')
                  ->orWhere('audits.event', 'like', '%' . $request->search . '%')
                  ->orWhere('audits.ip_address', 'like', '%' . $request->search . '%')
                  ->orWhere('users.name', 'like', '%' . $request->search . '%')
                  ->orWhere('users.email', 'like', '%' . $request->search . '%');
            });
        }

        $audits = $query->orderByDesc('audits.created_at')->paginate(25)->withQueryString();
        $events = DB::table('audits')->distinct()->pluck('event')->sort()->values();

        return view('admin.audit.index', compact('audits', 'events'));
    }

    public function show(string $id)
    {
        $audit = DB::table('audits')
            ->leftJoin('users', function ($join) {
                $join->on('audits.user_id', '=', 'users.id')
                     ->where('audits.user_type', '=', 'App\\Models\\User');
            })
            ->select('audits.*', 'users.name as user_name', 'users.email as user_email')
            ->where('audits.id', $id)
            ->firstOrFail();

        $expectedChecksum = hash('sha256', json_encode([
            $audit->event,
            $audit->auditable_type,
            $audit->auditable_id,
            $audit->old_values,
            $audit->new_values,
            $audit->ip_address,
            $audit->created_at,
        ]));

        $integrityOk = $audit->checksum === $expectedChecksum;

        return view('admin.audit.show', compact('audit', 'integrityOk'));
    }

    public function exportCsv(Request $request)
    {
        $filters  = $request->only(['event', 'auditable_type', 'date_from', 'date_to']);
        $filename = 'audit_log_' . now()->format('Ymd_His') . '.csv';

        return Excel::download(
            new AuditExport($filters),
            $filename,
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    public function exportPdf(Request $request)
    {
        $query = DB::table('audits')
            ->leftJoin('users', function ($join) {
                $join->on('audits.user_id', '=', 'users.id')
                     ->where('audits.user_type', '=', 'App\\Models\\User');
            })
            ->select('audits.*', 'users.name as user_name', 'users.email as user_email');

        if ($request->filled('event')) {
            $query->where('audits.event', $request->event);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('audits.created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('audits.created_at', '<=', $request->date_to);
        }

        $audits = $query->orderByDesc('audits.created_at')->limit(500)->get();

        $pdf = Pdf::loadView('admin.audit.pdf', compact('audits'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('audit_log_' . now()->format('Ymd_His') . '.pdf');
    }
}