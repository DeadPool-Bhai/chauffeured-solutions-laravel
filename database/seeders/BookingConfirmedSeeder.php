<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * New page that Limo Anywhere can send customers to after a confirmed booking.
 * GTM fires the primary conversion on a view of this URL. Noindex, not in the sitemap.
 */
class BookingConfirmedSeeder extends Seeder
{
    public function run(): void
    {
        Page::updateOrCreate(['canonical_path' => '/booking-confirmed/'], [
            'kind' => 'page',
            'type' => 'utility',
            'title' => 'Booking Confirmed',
            'h1' => 'Your booking is confirmed',
            'seo_title' => 'Booking Confirmed - Chauffeured Solutions',
            'meta_description' => 'Your chauffeur booking with Chauffeured Solutions is confirmed.',
            'robots' => 'noindex, follow',
            'status' => Page::STATUS_PUBLISHED,
            'published_at' => now(),
            'body_html' => '<p>Thank you. Your chauffeur booking has been confirmed and a confirmation email is on its way.</p>'
                .'<p>If you need to change anything, call us on <a href="tel:+61404448804">+61 404 448 804</a> or reply to your confirmation email.</p>',
        ]);
    }
}
