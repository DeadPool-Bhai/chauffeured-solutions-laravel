<?php

namespace App\Filament\Resources\Redirects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RedirectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('hits', 'desc')
            ->columns([
                TextColumn::make('from_path')->label('Old URL')->searchable()->copyable(),
                TextColumn::make('to_path')->label('New URL')->searchable()->limit(60)->placeholder('Gone'),
                TextColumn::make('status_code')->label('Type')->badge()->color(fn ($state) => $state === 410 ? 'danger' : 'gray'),
                TextColumn::make('hits')->numeric()->sortable(),
                TextColumn::make('last_hit_at')->label('Last used')->since()->sortable()->placeholder('Never'),
                ToggleColumn::make('is_active')->label('Active'),
                TextColumn::make('note')->limit(40)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status_code')->label('Type')->options([301 => '301', 302 => '302', 307 => '307', 410 => '410']),
                Filter::make('unused')->label('Never used')->query(fn (Builder $q) => $q->where('hits', 0)),
                Filter::make('inactive')->label('Inactive')->query(fn (Builder $q) => $q->where('is_active', false)),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
