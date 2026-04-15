<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportExportLog extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'status',
        'file_name',
        'format',
        'total_rows',
        'success_rows',
        'failed_rows',
        'failures',
        'filters',
    ];

    protected $casts = [
        'failures' => 'array',
        'filters'  => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}