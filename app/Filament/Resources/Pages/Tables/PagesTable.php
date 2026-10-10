<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Models\Page;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('canonical_path')
            ->columns([
                TextColumn::make('title')->searchable()->limit(45)->weight('medium'),
                TextColumn::make('canonical_path')->label('URL')->searchable()->copyable()->limit(55),
                TextColumn::make('kind')->badge()->color(fn (string $state) => $state === 'post' ? 'info' : 'gray'),
                TextColumn::make('status')->badge()->color(fn (string $state) => $state === 'published' ? 'success' : 'warning'),
                IconColumn::make('noindex')->label('Indexed')->state(fn (Page $r) => ! $r->isNoindex())->boolean(),
                TextColumn::make('seo_title')->label('Title tag')
                    ->state(fn (Page $r) => mb_strlen((string) $r->seo_title))
                    ->suffix(' chars')
                    ->color(fn ($state) => $state === 0 || $state > 65 ? 'danger' : 'success')
                    ->toggleable(),
                TextColumn::make('meta_description')->label('Description')
                    ->state(fn (Page $r) => mb_strlen((string) $r->meta_description))
                    ->suffix(' chars')
                    ->color(fn ($state) => $state === 0 || $state > 160 ? 'danger' : 'success')
                    ->toggleable(),
                TextColumn::make('wp_modified_at')->label('Modified')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kind')->options(['page' => 'Pages', 'post' => 'Blog posts']),
                SelectFilter::make('status')->options(['published' => 'Published', 'draft' => 'Draft']),
                SelectFilter::make('type')->options(fn () => Page::query()->distinct()->orderBy('type')->pluck('type', 'type')->all()),
                Filter::make('noindex')->label('Not indexed')->query(fn (Builder $q) => $q->where('robots', 'like', '%noindex%')),
                Filter::make('no_description')->label('Missing meta description')->query(fn (Builder $q) => $q->where(fn ($w) => $w->whereNull('meta_description')->orWhere('meta_description', ''))),
                Filter::make('long_title')->label('Title tag over 65 characters')->query(fn (Builder $q) => $q->whereRaw('LENGTH(seo_title) > 65')),
                Filter::make('long_description')->label('Description over 160 characters')->query(fn (Builder $q) => $q->whereRaw('LENGTH(meta_description) > 160')),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('view')->label('View live')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Page $r) => $r->canonicalUrl(), shouldOpenInNewTab: true),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
