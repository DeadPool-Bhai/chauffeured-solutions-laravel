<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\Redirect;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LeadStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $startOfDay = now('Australia/Sydney')->startOfDay()->utc();

        return [
            Stat::make('Leads today', Lead::where('created_at', '>=', $startOfDay)->count()),
            Stat::make('Leads, last 7 days', Lead::where('created_at', '>=', now()->subDays(7))->count()),
            Stat::make('Emails not sent', Lead::whereNull('notified_at')->count())
                ->description('Check the queue cron if this stays above zero')
                ->color(Lead::whereNull('notified_at')->where('created_at', '<', now()->subMinutes(10))->exists() ? 'danger' : 'success'),
            Stat::make('Redirect hits, all time', number_format((int) Redirect::sum('hits'))),
        ];
    }
}
