<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\BooksImport;
use App\Exports\BooksExport;
use App\Models\Category;
use App\Models\ImportExportLog;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BookImportExportController extends Controller
{
    // ── IMPORT ──────────────────────────────────────────────

    public function showImportForm()
    {
        $logs = ImportExportLog::where('user_id', auth()->id())
            ->where('type', 'import')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.books.import', compact('logs'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file'             => 'required|file|mimes:xlsx,csv|max:10240',
            'duplicate_action' => 'required|in:skip,update',
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
            $import = new BooksImport($request->duplicate_action);
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
                'status'       => 'completed',
                'failed_rows'  => $failureCount,
                'failures'     => $failureData,
            ]);

            $message = $failureCount > 0
                ? "Import completed with {$failureCount} row(s) skipped due to errors."
                : 'Import completed successfully!';

            return redirect()->route('admin.books.import')
                ->with('success', $message)
                ->with('log_id', $log->id);

        } catch (\Exception $e) {
            $log->update(['status' => 'failed']);
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
{
    $rows = [
        ['ISBN', 'Title', 'Author', 'Price', 'Stock', 'Category', 'Description'],
        ['978-3-16-148410-0', 'Sample Book Title', 'Author Name', '19.99', '100', 'Fiction', 'A brief description of the book.'],
    ];

    return Excel::download(
        new \App\Exports\BooksTemplateExport($rows),
        'books_import_template.csv',
        \Maatwebsite\Excel\Excel::CSV
    );
}

    // ── EXPORT ──────────────────────────────────────────────

    public function showExportForm()
    {
        $categories = Category::orderBy('name')->get();

        $logs = ImportExportLog::where('user_id', auth()->id())
            ->where('type', 'export')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.books.export', compact('categories', 'logs'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'columns'      => 'required|array|min:1',
            'columns.*'    => 'in:isbn,title,author,price,stock,category,description,created_at',
            'price_min'    => 'nullable|numeric|min:0',
            'price_max'    => 'nullable|numeric|min:0',
            'date_from'    => 'nullable|date',
            'date_to'      => 'nullable|date',
            'stock_status' => 'nullable|in:all,in_stock,out_of_stock',
            'category_id'  => 'nullable|exists:categories,id',
        ]);

        $filters = $request->only(['category_id', 'price_min', 'price_max', 'stock_status', 'date_from', 'date_to']);
        $columns = $request->columns;

        ImportExportLog::create([
            'user_id'   => auth()->id(),
            'type'      => 'export',
            'status'    => 'completed',
            'file_name' => 'books_export_' . now()->format('Ymd_His') . '.csv',
            'format'    => 'csv',
            'filters'   => $filters,
        ]);

        return Excel::download(
            new BooksExport($filters, $columns),
            'books_export_' . now()->format('Ymd_His') . '.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }
}