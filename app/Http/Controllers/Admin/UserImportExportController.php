<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\UsersImport;
use App\Exports\UsersExport;
use App\Exports\UsersTemplateExport;
use App\Models\ImportExportLog;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class UserImportExportController extends Controller
{
    // ── IMPORT ──────────────────────────────────────────────

    public function showImportForm()
    {
        $logs = ImportExportLog::where('user_id', auth()->id())
            ->where('type', 'import')
            ->where('file_name', 'like', '%user%')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.users.import', compact('logs'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv|max:10240',
        ]);

        $file     = $request->file('file');
        $fileName = $file->getClientOriginalName();

        $log = ImportExportLog::create([
            'user_id'   => auth()->id(),
            'type'      => 'import',
            'status'    => 'processing',
            'file_name' => $fileName,
            'format'    => $file->getClientOriginalExtension(),
        ]);

        try {
            $import = new UsersImport();
            Excel::import($import, $file);

            $failures     = $import->failures();
            $failureData  = [];
            $failureCount = 0;

            foreach ($failures as $failure) {
                $failureCount++;
                $failureData[] = [
                    'row'    => $failure->row(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values(),
                ];
            }

            $log->update([
                'status'      => 'completed',
                'failed_rows' => $failureCount,
                'failures'    => $failureData,
            ]);

            $message = $failureCount > 0
                ? "Import completed with {$failureCount} row(s) skipped due to errors."
                : 'Users imported successfully!';

            return redirect()->route('admin.users.import')
                ->with('success', $message);

        } catch (\Exception $e) {
            $log->update(['status' => 'failed']);
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new UsersTemplateExport(),
            'users_import_template.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    // ── EXPORT ──────────────────────────────────────────────

    public function showExportForm()
    {
        $logs = ImportExportLog::where('user_id', auth()->id())
            ->where('type', 'export')
            ->where('file_name', 'like', '%user%')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.users.export', compact('logs'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'status'    => 'nullable|in:verified,unverified',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date',
            'search'    => 'nullable|string|max:100',
        ]);

        $filters  = $request->only(['status', 'date_from', 'date_to', 'search']);
        $filename = 'users_export_' . now()->format('Ymd_His') . '.csv';

        ImportExportLog::create([
            'user_id'   => auth()->id(),
            'type'      => 'export',
            'status'    => 'completed',
            'file_name' => $filename,
            'format'    => 'csv',
            'filters'   => $filters,
        ]);

        return Excel::download(
            new UsersExport($filters),
            $filename,
            \Maatwebsite\Excel\Excel::CSV
        );
    }
}