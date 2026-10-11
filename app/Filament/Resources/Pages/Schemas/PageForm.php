<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Models\Page;
use Closure;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Tabs::make('Page')->columnSpanFull()->persistTabInQueryString()->tabs([
                Tab::make('Content')->schema([
                    TextInput::make('title')->required()->maxLength(255),
                    TextInput::make('h1')->label('Heading (H1)')->maxLength(255)
                        ->helperText('Shown as the main heading on the page.'),
                    TextInput::make('canonical_path')
                        ->label('URL path')
                        ->required()
                        ->maxLength(255)
                        ->rule('regex:#^/[^\s?\#]*$#')
                        ->unique(Page::class, 'canonical_path', ignoreRecord: true)
                        ->helperText('Exactly as it appears after the domain, including any trailing slash. If you change it, a 301 redirect from the old URL is added for you.'),
                    RichEditor::make('body_html')->label('Body')->columnSpanFull()
                        ->helperText('Shortcodes such as [fluentform id="4"] and [trustindex] are replaced by the quote form and the reviews block.'),
                    Textarea::make('excerpt')->rows(2),
                ]),

                Tab::make('SEO')->schema([
                    TextInput::make('seo_title')->label('Title tag')->maxLength(120)->live(onBlur: true)
                        ->hint(fn (Get $get) => self::lengthHint($get('seo_title'), 60, 'characters'))
                        ->helperText('Aim for 60 or fewer characters. Google may cut it off beyond about 600 pixels.'),
                    Textarea::make('meta_description')->rows(3)->maxLength(320)->live(onBlur: true)
                        ->hint(fn (Get $get) => self::lengthHint($get('meta_description'), 155, 'characters'))
                        ->helperText('Aim for 120 to 155 characters, written as a reason to click.'),
                    TextInput::make('focus_keyword')->helperText('Used by the AI assistant and the Search Console monitor.'),
                    Select::make('robots')->label('Search engines')->options([
                        '' => 'Index (default)',
                        'noindex, follow' => 'Do not index',
                    ])->default('')
                        ->dehydrateStateUsing(fn ($state) => $state ?: null),
                    TextInput::make('canonical_override')->label('Canonical URL override')->url()
                        ->helperText('Leave empty unless this page should point search engines at a different URL.'),
                    TextInput::make('featured_image')->helperText('Path under /wp-content/uploads/, for example /wp-content/uploads/2026/02/photo.jpg'),
                    TextInput::make('og_image')->label('Social share image')->helperText('Falls back to the featured image.'),
                ]),

                Tab::make('FAQs')->schema([
                    Repeater::make('faqs')->relationship()->orderColumn('position')->reorderable()->collapsible()
                        ->itemLabel(fn (array $state) => $state['question'] ?? null)
                        ->schema([
                            TextInput::make('question')->required()->maxLength(255),
                            Textarea::make('answer')->required()->rows(3),
                        ])->helperText('Used for FAQ structured data. Keep these in step with the FAQ shown in the body.'),
                ]),

                Tab::make('Advanced')->schema([
                    Section::make('Publishing')->columns(3)->schema([
                        Select::make('status')->options(['published' => 'Published', 'draft' => 'Draft'])->required()->default('draft'),
                        Select::make('kind')->options(['page' => 'Page', 'post' => 'Blog post'])->required()->default('page'),
                        TextInput::make('type')->required()->default('page')
                            ->helperText('Picks the template: home, suburb, post, page ...'),
                    ]),
                    Textarea::make('schema_json')->label('Structured data (JSON-LD)')->rows(14)->columnSpanFull()
                        ->rule(fn (): Closure => function (string $attribute, $value, Closure $fail) {
                            if (blank($value)) {
                                return;
                            }
                            preg_match_all('#<script[^>]*application/ld\+json[^>]*>(.*?)</script>#is', $value, $m);
                            if (! $m[1]) {
                                $fail('Wrap the JSON in <script type="application/ld+json"> ... </script>.');
                            }
                            foreach ($m[1] as $json) {
                                if (json_decode($json) === null) {
                                    $fail('One of the JSON-LD blocks is not valid JSON.');
                                }
                            }
                        })
                        ->helperText('Hand-written blocks carried over from the old site. Invalid JSON is rejected.'),
                    Textarea::make('data')->label('Template data (JSON)')->rows(10)->columnSpanFull()
                        ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $state)
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? json_decode($state, true) : null)
                        ->rule('nullable|json')
                        ->helperText('Hero, cards and stats for templated pages such as the homepage.'),
                ]),
            ]),
        ]);
    }

    private static function lengthHint(?string $value, int $max, string $unit): ?string
    {
        $n = mb_strlen((string) $value);

        return $n === 0 ? null : "{$n} / {$max} {$unit}".($n > $max ? ' (too long)' : '');
    }
}
