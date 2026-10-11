<?php

namespace App\Mail;

use App\Models\Lead;
use Carbon\Carbon;

class LeadRows
{
    /** @return array<string,string> label => value, in email order */
    public static function for(Lead $lead): array
    {
        $p = $lead->payload;

        if ($config = $lead->formConfig()) {
            return self::fromConfig($config, $p);
        }

        $pickup = collect([$p['pickup_address'] ?? null, $p['pickup_city'] ?? null, $p['pickup_state'] ?? null, $p['pickup_zip'] ?? null])->filter()->implode(', ');

        return array_filter([
            'Full Name' => $p['name'] ?? '',
            'Email' => $p['email'] ?? '',
            'Phone' => $p['phone'] ?? '',
            'Pick-up Date & Time' => isset($p['pickup_at']) ? Carbon::parse($p['pickup_at'])->format('D j M Y, g:i a') : '',
            'Pick-up Address' => $pickup,
            'Drop-off Address' => $p['dropoff_address'] ?? '',
            'Number of Passengers' => (string) ($p['passengers'] ?? ''),
            'Luggage Count' => (string) ($p['luggage'] ?? ''),
            'Additional Info (Flight No. / Airport / Notes)' => $p['flight_info'] ?? '',
        ], fn ($v) => $v !== '');
    }

    /** @return array<string,string> */
    private static function fromConfig(array $config, array $p): array
    {
        $rows = [];
        foreach ($config['fields'] as $f) {
            if (! isset($f['name']) || $f['type'] === 'checkbox') {
                continue;
            }
            $v = trim((string) ($p[$f['name']] ?? ''));
            if ($v === '') {
                continue;
            }
            if ($f['type'] === 'date') {
                $v = Carbon::parse($v)->format('D j M Y');
            }
            $rows[$f['label']] = $v;
        }

        return $rows;
    }
}
