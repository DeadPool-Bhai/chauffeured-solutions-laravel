<?php

namespace App\Filament\Resources\Leads;

use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Filament\Resources\Leads\Pages\ViewLead;
use App\Filament\Resources\Leads\Tables\LeadsTable;
use App\Mail\LeadRows;
use App\Models\Lead;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static ?string $navigationLabel = 'Leads';

    protected static ?int $navigationSort = 0;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Quote request')->columns(2)->schema([
                TextEntry::make('payload.name')->label('Name')->copyable(),
                TextEntry::make('payload.email')->label('Email')->copyable(),
                TextEntry::make('payload.phone')->label('Phone')->copyable(),
                TextEntry::make('created_at')->label('Received')->dateTime('D j M Y, g:i a')->timezone('Australia/Sydney'),
                TextEntry::make('details')->label('Booking details')->columnSpanFull()
                    ->state(fn (Lead $r) => collect(LeadRows::for($r))->map(fn ($v, $k) => "{$k}: {$v}")->implode("\n"))
                    ->extraAttributes(['style' => 'white-space: pre-line']),
            ]),
            Section::make('Where it came from')->columns(2)->collapsed()->schema([
                TextEntry::make('source_page_title')->label('Page title')->placeholder('Unknown'),
                TextEntry::make('source_path')->label('Page path')->placeholder('Unknown'),
                TextEntry::make('referer')->label('Referer')->placeholder('None'),
                TextEntry::make('ip')->label('IP address'),
                TextEntry::make('user_agent')->label('Browser')->columnSpanFull(),
                TextEntry::make('notified_at')->label('Emails sent')->dateTime('D j M Y, g:i a')->timezone('Australia/Sydney')->placeholder('Not sent yet'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return LeadsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeads::route('/'),
            'view' => ViewLead::route('/{record}'),
        ];
    }
}
