<?php

namespace App\Filament\Widgets;

use App\Models\Listing;
use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $ingresosMes = Transaction::where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->sum('commission_amount');

        return [
            Stat::make('Usuarios registrados', User::count())
                ->description('Total de usuarios')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Anuncios activos', Listing::where('status', 'active')->count())
                ->description('Publicados ahora mismo')
                ->descriptionIcon('heroicon-o-tag')
                ->color('success'),

            Stat::make('Ventas completadas', Transaction::where('status', 'completed')->count())
                ->description('Transacciones finalizadas')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('info'),

            Stat::make('Ingresos del mes', 'COP ' . number_format($ingresosMes, 0, ',', '.'))
                ->description('Comisiones cobradas este mes')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('warning'),

            Stat::make('Reportes pendientes', \App\Models\Report::where('status', 'open')->count())
                ->description('Requieren revisión')
                ->descriptionIcon('heroicon-o-flag')
                ->color('danger'),

            Stat::make('Verificaciones pendientes', \App\Models\IdentityVerification::where('status', 'pending')->count())
                ->description('Documentos por revisar')
                ->descriptionIcon('heroicon-o-identification')
                ->color('warning'),
        ];
    }
}
