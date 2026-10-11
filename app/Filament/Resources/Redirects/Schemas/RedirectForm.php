<?php

namespace App\Filament\Resources\Redirects\Schemas;

use App\Models\Redirect;
use App\Support\UrlPath;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class RedirectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('from_path')->label('Old URL path')->required()->maxLength(255)
                ->placeholder('/old-page/')
                ->unique(Redirect::class, 'from_path', ignoreRecord: true, modifyRuleUsing: null)
                ->dehydrateStateUsing(fn ($state) => UrlPath::redirectKey((string) $state))
                ->helperText('Matched without regard to case or a trailing slash.'),
            Select::make('status_code')->label('Type')->options([
                301 => '301 Permanent redirect',
                302 => '302 Temporary redirect',
                307 => '307 Temporary (keeps method)',
                410 => '410 Gone (page removed for good)',
            ])->default(301)->required()->live(),
            TextInput::make('to_path')->label('New URL')
                ->placeholder('/new-page/ or https://example.com/page')
                ->required(fn (Get $get) => (int) $get('status_code') !== 410)
                ->hidden(fn (Get $get) => (int) $get('status_code') === 410)
                ->maxLength(500)
                ->rule(fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                    if (blank($value) || ! str_starts_with($value, '/')) {
                        return;
                    }
                    $key = UrlPath::redirectKey(parse_url($value, PHP_URL_PATH) ?: '/');
                    if ($key === UrlPath::redirectKey((string) $get('from_path'))) {
                        $fail('This sends visitors back to the same URL.');
                    }
                    if (Redirect::where('from_path', $key)->where('is_active', true)->where('status_code', '!=', 410)->exists()) {
                        $fail('That URL is itself redirected. Point at the final URL so visitors and Google take one hop.');
                    }
                }),
            Toggle::make('is_active')->label('Active')->default(true),
            Textarea::make('note')->rows(2)->columnSpanFull(),
        ]);
    }
}
