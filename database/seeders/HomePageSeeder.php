<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Support\SectionSplitter;
use Illuminate\Database\Seeder;

/**
 * Structured content for the homepage (hero, cards, stats) captured from the live site,
 * plus a tidy body. Run after import:wordpress.
 */
class HomePageSeeder extends Seeder
{
    public function run(): void
    {
        $page = Page::where('canonical_path', '/')->first();
        if (! $page) {
            return;
        }

        $stats = [
            "Sydney's Largest Chauffeur Fleet",
            'Real-Time GPS Tracking on All Vehicles',
            '24/7 Customer Support',
            'Over 40 Years of Experience',
            'Experts in Large Events & Conferences',
            'Friendly & Reliable Chauffeurs',
        ];

        $keep = ['Superior', 'What Makes', 'Our Chauffeurs', 'Areas We Serve', 'Frequently'];
        $body = '';
        foreach (SectionSplitter::byH2($page->body_html)['sections'] as $s) {
            if (! collect($keep)->contains(fn ($k) => str_starts_with($s['title'], $k))) {
                continue;
            }
            $html = $s['html'];
            $html = preg_replace('#\[trustindex[^\]]*\]#i', '', $html);
            $html = preg_replace('#<img [^>]*alt="\d"[^>]*>#i', '', $html); // numbered icon placeholders
            foreach ($stats as $stat) {
                $html = str_replace(htmlspecialchars($stat, ENT_NOQUOTES), '', $html);
            }
            $html = preg_replace('#<p>\s*</p>#', '', $html);
            $body .= '<h2>'.e($s['title'])."</h2>\n".trim($html)."\n";
        }

        $page->update([
            'type' => 'home',
            'h1' => '#1 Chauffeur Service in Sydney – 40+ Years of Luxury Transfers & Car Hire',
            'body_html' => trim($body),
            'data' => [
                'hero' => [
                    'sub' => 'Your Trusted Choice for Chauffeur Hire Sydney – Corporate, Airport & Wedding Transfers',
                    'video' => '/wp-content/uploads/2025/05/home-header.mp4',
                ],
                'booking_title' => 'Make a booking',
                'services' => [
                    'eyebrow' => 'About',
                    'title' => 'Experience the Best Luxury Chauffeur Services In Sydney',
                    'intro' => 'With over 40 years of experience, Chauffeured Solutions is Sydney’s leading provider of luxury chauffeur services. We are renowned for our reliable corporate transfers, seamless airport and hotel pickups, elegant wedding and special event transfers, and tailored chauffeur hire services in Sydney.',
                    'cards' => [
                        ['title' => 'Tour/Sightseeing', 'img' => '/wp-content/uploads/2026/02/Tour_Sightseeing-Sydney.jpg', 'href' => '/tours-sightseeing/'],
                        ['title' => 'VIP Transfers', 'img' => '/wp-content/uploads/2026/02/VIP-Transfers-Sydney.jpg', 'href' => '/vip/'],
                        ['title' => 'People Mover', 'img' => '/wp-content/uploads/2026/02/People-Mover-Sydney.jpg', 'href' => '/people-mover/'],
                        ['title' => 'Airport Transfer', 'img' => '/wp-content/uploads/2026/02/Airport-Transfer-sydney.jpg', 'href' => '/airport-transfers-sydney/'],
                        ['title' => 'Wedding Chauffeur', 'img' => '/wp-content/uploads/2026/02/Wedding-Chauffeur-sydney.jpg', 'href' => '/wedding-chauffeur/'],
                        ['title' => 'Events Transfers', 'img' => '/wp-content/uploads/2026/02/Events-Transfers-Sydney.jpg', 'href' => '/event-transfers'],
                        ['title' => 'Corporate Hire', 'img' => '/wp-content/uploads/2026/02/Corporate-Hire-Sydney.jpg', 'href' => '/corporate-hire/'],
                    ],
                ],
                'fleet' => [
                    'eyebrow' => 'Ride in elegance',
                    'title' => "Sydney's Premier Chauffeur-Driven Car Hire",
                    'intro' => 'We offer a premium fleet of cars to ensure you travel in comfort every time, including the Mercedes-Benz V-Class, E-Class, and S-Class, the Audi A8, the BMW 7 Series. Our luxury vehicles all feature the finest leather interiors, plenty of leg room, and the ultimate climate control and sound systems.',
                    'cards' => [
                        ['title' => 'Mercedes-Benz Viano', 'cat' => 'People Mover', 'img' => '/wp-content/uploads/2025/06/Hir-Mercedes-Benz-Viano-in-sydney.jpg', 'href' => '/booking/'],
                        ['title' => 'Mercedes-Benz Sprinter', 'cat' => 'Luxury People Mover', 'img' => '/wp-content/uploads/2025/08/hire-mercedes-benz-sprinter-in-sydney.jpg', 'href' => '/booking/'],
                        ['title' => 'Bus', 'cat' => 'Luxury People Mover', 'img' => '/wp-content/uploads/2025/06/53-SEATER-BUS.jpg', 'href' => '/booking/'],
                        ['title' => 'Mercedes E class', 'cat' => 'Corporate Car', 'img' => '/wp-content/uploads/2025/06/Hire-Mercedes-E-class-in-sydey-e1750293052448.jpg', 'href' => '/booking/'],
                        ['title' => 'Genesis', 'cat' => 'Corporate Car', 'img' => '/wp-content/uploads/2025/07/Hite-Genesis-G80-in-stdney.png', 'href' => '/booking/'],
                        ['title' => 'Mercedes S 450 L', 'cat' => 'Luxury European Car', 'img' => '/wp-content/uploads/2025/06/Hite-Mercedes-S-class-s450-in-stdney.jpg', 'href' => '/booking/'],
                        ['title' => 'Audi A8 L', 'cat' => 'Luxury European Car', 'img' => '/wp-content/uploads/2025/06/hire-Audi-A8-L-in-sydney.jpg', 'href' => '/booking/'],
                        ['title' => 'BMW 7 Series L', 'cat' => 'Luxury European Car', 'img' => '/wp-content/uploads/2025/06/hire-BMW-7-Series-740-in-sydney.jpg', 'href' => '/booking/'],
                        ['title' => 'Audi Q7', 'cat' => 'SUV', 'img' => '/wp-content/uploads/2025/06/hire-Audi-Q7-in-Sydney.jpg', 'href' => '/booking/'],
                        ['title' => 'Lexus ES300', 'cat' => 'Corporate Car', 'img' => '/wp-content/uploads/2025/06/hire-Lexus-E300-in-sydney-1.jpg', 'href' => '/booking/'],
                        ['title' => 'BMW X7', 'cat' => 'SUV', 'img' => '/wp-content/uploads/2025/06/hire-BMW-X7-in-Sydney.jpg', 'href' => '/booking/'],
                        ['title' => 'Lexus RX', 'cat' => 'SUV', 'img' => '/wp-content/uploads/2025/06/hire-Lexus-RX-in-Sydney.jpg', 'href' => '/booking/'],
                    ],
                ],
                'band' => [
                    'title' => 'Book Your Private Chauffeur',
                    'sub' => 'Punctual, Professional, and Tailored to Your Schedule.',
                ],
                'intro_eyebrow' => 'Chauffeur car hire tailored to your needs, every time.',
                'stats' => $stats,
            ],
        ]);
    }
}
