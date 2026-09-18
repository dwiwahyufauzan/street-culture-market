<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $revenue = (float) Order::where('payment_status', 'paid')->sum('total');

        return [
            Stat::make('Total Orders', Order::count())
                ->description('All customer dispatches')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make('Gross Revenue', 'Rp '.number_format($revenue, 0, ',', '.'))
                ->description('Settled paid transactions')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Active Garments', Product::where('is_active', true)->count())
                ->description('Active catalog releases')
                ->descriptionIcon('heroicon-m-tag')
                ->color('gray'),

            Stat::make('Total Customers', User::where('role', 'customer')->count())
                ->description('Registered buyer accounts')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}
