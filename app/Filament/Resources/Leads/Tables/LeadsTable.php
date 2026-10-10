<?php

namespace App\Filament\Resources\Leads\Tables;

use App\Models\Lead;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Received')->dateTime('D j M Y, g:i a')->timezone('Australia/Sydney')->sortable(),
                TextColumn::make('payload.name')->label('Name')->searchable(query: fn (Builder $q, string $s) => $q->where('payload', 'like', "%{$s}%")),
                TextColumn::make('payload.email')->label('Email')->copyable(),
                TextColumn::make('payload.phone')->label('Phone')->copyable(),
                TextColumn::make('payload.pickup_at')->label('Pick-up')->state(fn (Lead $r) => ($t = $r->payload['pickup_at'] ?? null) ? date('D j M Y, g:i a', strtotime($t)) : null),
                TextColumn::make('source_path')->label('Page')->limit(40)->toggleable(),
                IconColumn::make('notified_at')->label('Emailed')->boolean()->getStateUsing(fn (Lead $r) => $r->notified_at !== null),
            ])
            ->filters([
                Filter::make('not_emailed')->label('Email not sent')->query(fn (Builder $q) => $q->whereNull('notified_at')),
                Filter::make('today')->label('Today')->query(fn (Builder $q) => $q->where('created_at', '>=', now('Australia/Sydney')->startOfDay()->utc())),
            ])
            ->recordActions([ViewAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
