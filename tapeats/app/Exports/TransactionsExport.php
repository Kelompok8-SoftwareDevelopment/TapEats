<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionsExport implements FromCollection, WithHeadings
{
    protected $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    public function collection()
    {
        return $this->records->map(function ($item) {
            return [
                $item->code,
                $item->name,
                $item->phone,
                $item->payment_method,
                $item->payment_status,
                $item->total,
                $item->created_at->format('Y-m-d H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Phone',
            'Payment Method',
            'Payment Status',
            'Total',
            'Created At',
        ];
    }
}
