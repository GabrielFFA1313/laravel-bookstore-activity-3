<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class UsersImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithBatchInserts,
    WithChunkReading,
    SkipsOnFailure
{
    use SkipsFailures;

    public function model(array $row): ?User
    {
        // Skip if email already exists
        if (User::where('email', strtolower(trim($row['email'])))->exists()) {
            return null;
        }

        return new User([
            'name'              => trim($row['name']),
            'email'             => strtolower(trim($row['email'])),
            'password'          => Hash::make(trim($row['password'])),
            'role'              => 'customer',
            'phone'             => trim($row['phone'] ?? ''),
            'email_verified_at' => now(), // auto-verify imported users
        ]);
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'phone'    => ['nullable', 'string', 'max:20'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'email.email'       => 'The email address is invalid.',
            'password.min'      => 'Password must be at least 8 characters.',
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}