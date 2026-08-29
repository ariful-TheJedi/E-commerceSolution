<?php

namespace Modules\Product\Infrastructure\Persistence\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Product\Infrastructure\Persistence\Models\Product;

/**
 * @extends Factory<Product>
 */
final class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->randomElement([
            'Aurora Laptop 14',
            'Nimbus Wireless Headphones',
            'Cedar Desk Lamp',
            'Harbor Water Bottle',
            'Pixel Soft Tee',
            'Orbit Mechanical Keyboard',
            'Trail Runner Shoes',
            'Cloud Soft Blanket',
        ]);

        return [
            'id' => (string) Str::uuid(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'status' => fake()->randomElement(['active', 'active', 'active', 'draft']),
            'description' => fake()->paragraph(2),
            'price_minor' => fake()->numberBetween(1299, 129999),
            'currency' => 'USD',
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['status' => 'active']);
    }

    public function draft(): static
    {
        return $this->state(fn (): array => ['status' => 'draft']);
    }
}
