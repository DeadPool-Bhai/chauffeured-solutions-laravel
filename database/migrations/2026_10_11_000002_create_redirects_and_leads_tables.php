<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            // Stored normalised: lower case, leading slash, no trailing slash ("/corporate-sedan").
            // The slash and no-slash forms of a source URL are treated as the same rule.
            $table->string('from_path', 255)->unique();
            // Path ("/corporate-hire/") or absolute URL. Null for 410 rules.
            $table->string('to_path', 500)->nullable();
            $table->unsignedSmallInteger('status_code')->default(301); // 301, 302, 410
            $table->unsignedInteger('hits')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->string('note')->nullable();
            $table->timestamp('last_hit_at')->nullable();
            $table->timestamps();
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('form_key', 40)->index();   // quote, wedding_quote, wedding_booking
            $table->json('payload');
            $table->string('source_page_title')->nullable();
            $table->string('source_path')->nullable();
            $table->text('referer')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
        Schema::dropIfExists('redirects');
    }
};
