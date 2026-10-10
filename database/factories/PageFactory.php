<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Page> */
class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $slug = fake()->unique()->slug(2);

        return [
            'kind' => 'page',
            'type' => 'page',
            'canonical_path' => '/'.$slug.'/',
            'title' => fake()->sentence(3),
            'h1' => fake()->sentence(4),
            'body_html' => '<p>'.fake()->paragraph().'</p>',
            'seo_title' => fake()->sentence(6),
            'meta_description' => fake()->sentence(14),
            'status' => Page::STATUS_PUBLISHED,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => Page::STATUS_DRAFT]);
    }
}
