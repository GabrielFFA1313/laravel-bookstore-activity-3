<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class UsersTemplateExport implements FromArray
{
    public function array(): array
    {
        return [
            ['name', 'email', 'password', 'role', 'phone', 'address'],
            ['Juan dela Cruz', 'juan@example.com', 'password123', 'customer', '09171234567', 'Dologon, Valencia, Bukidnon'],
            ['Maria Santos', 'maria@example.com', 'password123', 'customer', '09281234567', 'Cagayan de Oro, Misamis Oriental'],
        ];
    }
}