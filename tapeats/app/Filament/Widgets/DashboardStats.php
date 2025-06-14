<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Foods;
use App\Models\Barcodes;
use App\Models\TransactionItems;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Auth;

class DashboardStats extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $startDate = isset($this->filters['startDate']) ? Carbon::parse($this->filters['startDate'])->startOfDay() : now()->startOfMonth();
        $endDate = isset($this->filters['endDate']) ? Carbon::parse($this->filters['endDate'])->endOfDay() : now();

        // Stats yang bisa dilihat semua user
        $totalFoods = Foods::count();
        $totalBarcodes = Barcodes::count();

        $stats = [
            Stat::make('Total menu', $totalFoods)
                ->description('Total number of menu items')
                ->descriptionIcon('heroicon-o-rectangle-stack'),

            Stat::make('Total Table', $totalBarcodes)
                ->description('Total number of tables')
                ->descriptionIcon('heroicon-o-table-cells'),
        ];

        // Hanya owner yang bisa melihat sales dan transaction stats
        if (Auth::user()->isOwner()) {
            // Filter transactions & items nya by date yang PAID
            $filteredTransactions = Transaction::query()
                ->where('payment_status', 'PAID');

            // Filter transaction items untuk transaksi yang PAID
            $filteredItems = TransactionItems::query()
                ->whereHas('transaction', function ($query) {
                    $query->where('payment_status', 'PAID');
                });

            if ($startDate) {
                $filteredTransactions->where('created_at', '>=', $startDate);
                $filteredItems->where('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $filteredTransactions->where('created_at', '<=', $endDate);
                $filteredItems->where('created_at', '<=', $endDate);
            }

            $totalSales = $filteredItems->sum('subtotal');
            $totalTransactions = $filteredTransactions->count();

            // Tambahkan stats khusus owner
            $stats[] = Stat::make('Total Sales', 'Rp ' . number_format($totalSales, 0, ',', '.'))
                ->description('Total sales amount')
                ->descriptionIcon('heroicon-o-currency-dollar');

            $stats[] = Stat::make('Total Transactions', $totalTransactions)
                ->description('Total number of transactions')
                ->descriptionIcon('heroicon-o-receipt-percent');
        }

        return $stats;
    }
}
