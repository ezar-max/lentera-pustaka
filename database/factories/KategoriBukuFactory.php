<?php

namespace Database\Factories;

use App\Models\KategoriBuku;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KategoriBuku>
 */
class KategoriBukuFactory extends Factory
{
    protected $model = KategoriBuku::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_kategori' => ucfirst(fake()->unique()->word()),
        ];
    }
}
