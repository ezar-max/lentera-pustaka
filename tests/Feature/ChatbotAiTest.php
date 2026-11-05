<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\KategoriBuku;
use App\Models\User;
use App\Services\ChatbotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotAiTest extends TestCase
{
    use RefreshDatabase;

    public function test_chatbot_fokus_pada_perpustakaan_dan_mencari_stok_buku_di_database(): void
    {
        $user = User::factory()->create();
        $kategori = KategoriBuku::factory()->create(['nama_kategori' => 'Sastra Klasik']);

        $bukuAda = Buku::factory()->create([
            'judul' => 'Laskar Pelangi',
            'penulis' => 'Andrea Hirata',
            'stok_total' => 5,
            'stok_tersedia' => 4,
        ]);
        $bukuAda->kategori()->attach($kategori->id);

        $chatbot = app(ChatbotService::class);

        // 1. Tes pencarian buku yang ADA di database
        $konteks = $chatbot->susunKonteks($user, 'Apakah ada buku Laskar Pelangi?');
        $this->assertStringContainsString('Laskar Pelangi', $konteks);
        $this->assertStringContainsString('STOK', $konteks);
        $this->assertStringContainsString('Tersedia 4 dari total 5 eksemplar', $konteks);

        $jawabanAda = $chatbot->kirimPesanKeAi($user, 'Apakah ada buku Laskar Pelangi?');
        $this->assertStringContainsString('Laskar Pelangi', $jawabanAda->isi);
        $this->assertStringContainsString('4', $jawabanAda->isi);

        // 2. Tes pencarian buku yang TIDAK ADA di database (tidak boleh membeberkan daftar buku lain)
        $konteksTidakAda = $chatbot->susunKonteks($user, 'Apakah ada buku Harry Potter?');
        $this->assertStringContainsString('TIDAK ADA di dalam database perpustakaan', $konteksTidakAda);

        $jawabanTidakAda = $chatbot->kirimPesanKeAi($user, 'Apakah ada buku Harry Potter?');
        $jawabanLower = mb_strtolower($jawabanTidakAda->isi);
        $this->assertTrue(
            str_contains($jawabanLower, 'tidak tersedia') ||
            str_contains($jawabanLower, 'tidak ditemukan') ||
            str_contains($jawabanLower, 'belum tersedia') ||
            str_contains($jawabanLower, 'belum ada') ||
            str_contains($jawabanLower, 'tidak ada')
        );

        // 3. Tes pertanyaan DI LUAR PERPUSTAKAAN (harus ditolak secara sopan & fokus perpustakaan)
        $jawabanLuar = $chatbot->kirimPesanKeAi($user, 'Siapa presiden Indonesia saat ini?');
        $this->assertStringContainsStringIgnoringCase('lentera pustaka', $jawabanLuar->isi);
        $this->assertStringContainsStringIgnoringCase('perpustakaan', $jawabanLuar->isi);

        // 4. Tes pertanyaan aturan perpustakaan
        $jawabanAturan = $chatbot->kirimPesanKeAi($user, 'Berapa hari durasi pinjam dan kuota buku?');
        $this->assertStringContainsString('peminjaman', mb_strtolower($jawabanAturan->isi));
        $this->assertStringContainsString('buku', mb_strtolower($jawabanAturan->isi));
        $this->assertStringContainsString('7 hari', mb_strtolower($jawabanAturan->isi));
    }
}
