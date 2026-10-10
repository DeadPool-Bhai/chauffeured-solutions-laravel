<?php

return [
    // Who gets the "new request" email. Comma separated in .env.
    'notify_to' => array_values(array_filter(array_map('trim', explode(',', env('LEAD_NOTIFY_TO', 'asker.asraf@stechs.com.au'))))),

    // Where the browser lands after a successful quote request. Keep this exact path:
    // it is the GTM conversion trigger on the live site.
    'thank_you_path' => '/thank-you-f4-quote-request/',

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret' => env('RECAPTCHA_SECRET'),
        'min_score' => (float) env('RECAPTCHA_MIN_SCORE', 0.5),
    ],
];
