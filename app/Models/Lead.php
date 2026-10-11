<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'notified_at' => 'datetime',
        ];
    }

    /** The form definition for non-quote forms, from config/forms.php. */
    public function formConfig(): ?array
    {
        return collect(config('forms'))->firstWhere('form_key', $this->form_key);
    }

    public function getContactNameAttribute(): string
    {
        $field = $this->formConfig()['name_field'] ?? 'name';

        return trim((string) ($this->payload[$field] ?? '')) ?: 'there';
    }

    public function getContactEmailAttribute(): ?string
    {
        return $this->payload['email'] ?? null;
    }
}
