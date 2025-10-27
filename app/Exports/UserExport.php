<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UserExport implements FromQuery, WithHeadings
{
    use Exportable;

    public function query()
    {
        return User::query()
            ->select([
                'users.name',
                'users.email',
                'users.phone',
                'users.city',
                'users.birthdate',
                'users.institution',
                'users.occupation',
            ])
            ->withCount(['transactions as total_transactions' => function (Builder $query) {
                $query->where('status', 'success');
            }]);
    }


    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Phone',
            'City',
            'Birthdate',
            'Institution',
            'Occupation',
            'Total Transactions',
        ];
    }
}
