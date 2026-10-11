<?php

return [
    'name' => 'Chauffeured Solutions',
    'legal_name' => 'Chauffeured Solutions AUS Pty Ltd',
    'abn' => '50 165 419 885',
    'phone' => '(02) 9955 1000',
    'phone_href' => 'tel:0299551000',
    'mobile' => '+61 404 448 804',
    'mobile_href' => 'tel:+61404448804',
    'whatsapp' => 'https://wa.me/61404448804',
    'email' => 'enquiries@csonline.com.au',
    'address' => '183 Kent St, Sydney NSW 2000, Australia',
    'logo' => '/wp-content/uploads/2025/05/CS-Logo-white.png',
    'booking_embed' => 'https://book.mylimobiz.com/v4/chausol',

    'social' => [
        'Facebook' => 'https://www.facebook.com/chauffeuredsolutions.com.au',
        'LinkedIn' => 'https://www.linkedin.com/company/chauffeured-solutions-australia/',
        'Instagram' => 'https://www.instagram.com/chauffeuredsolutions.com.au/',
    ],

    // Staging: noindex header, robots.txt disallow all, no GTM/GA4, optional HTTP auth ("user:password").
    'staging' => (bool) env('APP_STAGING', false),
    'staging_auth' => env('STAGING_AUTH'),

    'tracking' => [
        'gtm' => env('APP_STAGING', false) ? null : env('GTM_ID', 'GTM-KBKWMQW5'),
        'ga4' => env('APP_STAGING', false) ? null : env('GA4_ID', 'G-0YJM2KDWVT'),
    ],

    'topbar' => [
        ['Instant Book Online', '/booking/'],
        ['Get Quote', '#quote'],
        ['Corporate Login', '/login/'],
    ],

    // Final URLs only (no redirect hops), written in each page's exact canonical form.
    'nav' => [
        ['Services', '/services/', [
            ['Hotel Transfers', '/hotel-transfers', [
                ['ICC Sydney', '/syd-airport-to-icc-sydney/'],
                ['Crown Sydney', '/syd-airport-to-crown-sydney/'],
                ['Sydney Harbour Marriott Hotel', '/syd-airport-sydney-harbour-marriott-hotel/'],
                ['Shangri-La Hotel Sydney', '/syd-airport-shangri-la-hotel-sydney/'],
                ['Intercontinental Hotel Sydney', '/syd-airport-intercontinental-hotel-sydney/'],
                ['Intercontinental Hotel Double Bay', '/syd-airport-intercontinental-hotel-double-bay/'],
                ['Sofitel Hotel Sydney', '/syd-airport-sofitel-hotel-sydney/'],
                ['Four Seasons Hotel Sydney', '/four-seasons-hotel/'],
                ['Park Hyatt The Rocks', '/syd-airport-park-hyatt-rocks/'],
                ['Hyatt Regency Sydney', '/syd-airport-hyatt-regency-sydney/'],
            ]],
            ['Tours/ Sightseeing', '/tours-sightseeing/', [
                ['Sydney Tour', '/sydney-tour/'],
                ['Hunter Valley tour', '/hunter-valley-tour/'],
                ['Melbourne Tour', '/chauffeured-melbourne-tour/'],
                ['Gold Coast Tour packages', '/chauffeured-gold-coast-tour/'],
                ['Cairns Tour', '/chauffeured-cairns-tour/'],
                ['Blue Mountains tour', '/blue-mountains-tour/'],
            ]],
            ['Hire Private Jet', '/hire-private-jet/'],
            ['Wedding Chauffeur Hire', '/wedding-chauffeur/'],
            ['VIP Transfers', '/vip/'],
            ['Event Transfers', '/event-transfers'],
            ['Show & Concert Transfers', '/show-concert-transfers/'],
        ]],
        ['Airport Transfers', '/airport-transfers-sydney/'],
        ['Corporate', '/corporate-hire/'],
        ['Our Fleet', '/fleet/', [
            ['Corporate Sedan', '/corporate-hire/', [
                ['Mercedes Benz E-class', '/mercedes-benz-e-class-chauffeured-sydney/'],
                ['Lexus ES300', '/lexus-es300-chauffeur-hire-car-service-in-sydney/'],
                ['Genesis', '/genesis-g80-chauffeur-hire-car-service-in-sydney/'],
            ]],
            ['Luxury European car', '/luxury-european-car/', [
                ['Audi A8', '/audi-a8/'],
                ['Mercedes Benz S-class', '/mercedes-benz-s-class-chauffeured-sydney/'],
                ['BMW 7 Series', '/bmw-7-series-chauffeured-sydney/'],
            ]],
            ['Luxury SUV', '/luxury-suv-hire-in-sydney/', [
                ['Audi Q7', '/audi-q7-chauffeur-hire-car-service-in-sydney/'],
                ['BMW X7', '/bmw-x7-chauffeur-hire-car-service-in-sydney/'],
                ['Lexus RX', '/lexus-rx-chauffeur-hire-car-service-in-sydney/'],
            ]],
            ['People Mover', '/people-mover/', [
                ['Mercedes Viano Van Hire Sydney', '/mercedes-viano-van-hire-sydney/'],
                ['Mercedes Sprinter Van Hire Sydney', '/mercedes-sprinter-van-hire-sydney/'],
                ['53 Seater Bus', '/53-seater-bus/'],
            ]],
        ]],
        ['About Us', '/about-us/', [
            ['How To', '/how-to/'],
            ['Contact Us', '/contact-us/'],
            ['Privacy Policy', '/privacy-policy/'],
            ['Blog', '/blog/'],
        ]],
    ],

    'footer_services' => [
        ['Airport Transfer', '/airport-transfers-sydney/'],
        ['Hotel Transfers', '/hotel-transfers'],
        ['Corporate Service', '/corporate-hire/'],
        ['Wedding Limo', '/wedding-chauffeur/'],
        ['VIP', '/vip/'],
        ['Contact Us', '/contact-us/'],
    ],
];
