<?php

namespace Tests\Feature;

use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Filament\Resources\Redirects\Pages\CreateRedirect;
use App\Filament\Resources\Redirects\Pages\ListRedirects;
use App\Models\Lead;
use App\Models\Page;
use App\Models\Redirect;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_admin_screens_load(): void
    {
        $p = Page::factory()->create();
        foreach (['pages', 'redirects', 'leads'] as $r) {
            $this->get('/admin/'.$r)->assertOk();
        }
        $this->get('/admin')->assertOk();
        $this->get('/admin/pages/'.$p->id.'/edit')->assertOk();
        $this->get('/admin/redirects/create')->assertOk();
    }

    public function test_lists_render_records(): void
    {
        $pages = Page::factory()->count(3)->create();
        Livewire::test(ListPages::class)->assertCanSeeTableRecords($pages);
        $r = Redirect::factory()->create();
        Livewire::test(ListRedirects::class)->assertCanSeeTableRecords([$r]);
        $l = Lead::create(['form_key' => 'quote', 'payload' => ['name' => 'A', 'email' => 'a@b.com', 'phone' => '1']]);
        Livewire::test(ListLeads::class)->assertCanSeeTableRecords([$l]);
        $this->get('/admin/leads/'.$l->id)->assertOk();
    }

    public function test_editing_page_url_creates_301(): void
    {
        $p = Page::factory()->create(['canonical_path' => '/old-url/']);
        Livewire::test(EditPage::class, ['record' => $p->getKey()])
            ->fillForm(['canonical_path' => '/new-url/'])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame('/new-url/', $p->fresh()->canonical_path);
        $this->assertDatabaseHas('redirects', ['to_path' => '/new-url/', 'status_code' => 301]);
        $this->getRaw('/old-url/')->assertStatus(301);
    }

    public function test_redirect_rejects_chain_and_self(): void
    {
        Redirect::create(['from_path' => '/a', 'to_path' => '/b/', 'status_code' => 301, 'is_active' => true]);
        Livewire::test(CreateRedirect::class)
            ->fillForm(['from_path' => '/c', 'to_path' => '/a/', 'status_code' => 301])
            ->call('create')
            ->assertHasFormErrors(['to_path']);
        Livewire::test(CreateRedirect::class)
            ->fillForm(['from_path' => '/d', 'to_path' => '/d/', 'status_code' => 301])
            ->call('create')
            ->assertHasFormErrors(['to_path']);
        Livewire::test(CreateRedirect::class)
            ->fillForm(['from_path' => '/e', 'to_path' => '/f/', 'status_code' => 301])
            ->call('create')
            ->assertHasNoFormErrors();
        $this->assertDatabaseHas('redirects', ['to_path' => '/f/']);
    }
}
