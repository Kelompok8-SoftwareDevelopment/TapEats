<?php

namespace App\Filament\Widgets;

use App\Models\TransactionItems;
use Illuminate\Support\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Auth;

class SalesChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Sales Chart';

    public static function canView(): bool
    {
        return Auth::user()->isOwner();
    }

    protected function getData(): array
    {
        $startDate = isset($this->filters['startDate']) ? Carbon::parse($this->filters['startDate'])->startOfDay() : now()->startOfMonth();
        $endDate = isset($this->filters['endDate']) ? Carbon::parse($this->filters['endDate'])->endOfDay() : now();

        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        $labels = [];
        $data = [];

        foreach ($period as $date) {
            $labels[] = $date->format('d M'); // e.g. "01 Jan"

            // Filter hanya transaksi dengan status PAID
            $data[] = TransactionItems::whereDate('created_at', $date)
                ->whereHas('transaction', function ($query) {
                    $query->where('payment_status', 'PAID');
                })
                ->sum('subtotal');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Penjualan (Rp)',
                    'data' => $data,
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#3b82f6',
                    'fill' => false,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'min' => 0,

                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
        ];
    }
}
