<?php

namespace Database\Factories;

use App\Models\Buku;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Buku>
 */
class BukuFactory extends Factory
{
    protected $model = Buku::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stok = fake()->numberBetween(3, 10);

        return [
            'judul' => fake()->sentence(3),
            'penulis' => fake()->name(),
            'penerbit' => fake()->company(),
            'tahun_terbit' => (int) fake()->year(),
            'isbn' => fake()->isbn13(),
            'sinopsis' => fake()->paragraph(3),
            'cover' => null,
            'stok_total' => $stok,
            'stok_tersedia' => $stok,
        ];
    }
}
