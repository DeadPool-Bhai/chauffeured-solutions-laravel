<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();

            // 'page' or 'post'. One table keeps the router, sitemap and redirects simple.
            $table->string('kind', 10)->default('page')->index();
            // home, service, venue, vehicle, tour, suburb, utility, legal, blog_index, post ...
            $table->string('type', 30)->default('service')->index();
            $table->string('template', 40)->default('standard');

            // Exactly as the live site's canonical path, including any trailing slash.
            // '/about-us/' and '/hotel-transfers' are different values on purpose.
            $table->string('canonical_path', 255)->unique();

            $table->string('title');
            $table->string('h1')->nullable();
            $table->longText('body_html')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('featured_image')->nullable();

            // SEO fields (Phase 1: snapshot of the rendered live values)
            $table->string('seo_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('robots', 60)->nullable();          // e.g. "noindex" or null for default
            $table->string('canonical_override', 255)->nullable();
            $table->string('og_image')->nullable();
            $table->string('focus_keyword')->nullable();

            // Existing hand-built JSON-LD blocks, carried over verbatim in Phase 1.
            $table->longText('schema_json')->nullable();

            $table->string('status', 10)->default('draft')->index(); // published | draft
            $table->timestamp('published_at')->nullable();
            $table->timestamp('wp_modified_at')->nullable();
            $table->unsignedBigInteger('wp_id')->nullable()->unique();

            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->string('question');
            $table->text('answer');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('pages');
    }
};
