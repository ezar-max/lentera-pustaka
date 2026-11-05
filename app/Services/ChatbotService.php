<?php

namespace App\Services;

use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\Percakapan;
use App\Models\Pesan;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ChatbotService
{
    /**
     * Kirim pesan member ke Asisten AI Lentera Pustaka dan simpan balasannya.
     */
    public function kirimPesanKeAi(User $user, string $isiPesan): Pesan
    {
        $percakapan = Percakapan::firstOrCreate(
            [
                'user_id' => $user->id,
                'tipe' => 'ai',
                'status' => 'aktif',
            ]
        );

        Pesan::create([
            'percakapan_id' => $percakapan->id,
            'pengirim' => 'user',
            'pengirim_id' => $user->id,
            'isi' => trim($isiPesan),
            'dibaca' => true,
        ]);

        $konteksSistem = $this->susunKonteks($user);
        $jawabanAi = $this->panggilGeminiApi($percakapan, $konteksSistem, $isiPesan);

        return Pesan::create([
            'percakapan_id' => $percakapan->id,
            'pengirim' => 'ai',
            'pengirim_id' => null,
            'isi' => $jawabanAi,
            'dibaca' => true,
        ]);
    }

    /**
     * Menyusun konteks sistem: katalog + stok, aturan pinjam, dan ringkasan pinjaman user.
     */
    public function susunKonteks(User $user): string
    {
        $kuota = config('perpustakaan.kuota_pinjam_aktif', 3);
        $durasi = config('perpustakaan.durasi_pinjam_hari', 7);
        $batasAmbil = config('perpustakaan.batas_ambil_hari', 2);

        $daftarBuku = Buku::with('kategori')
            ->orderBy('judul')
            ->limit(30)
            ->get()
            ->map(function (Buku $buku): string {
                $kat = $buku->kategori->pluck('nama_kategori')->join(', ');

                return "- {$buku->judul} oleh {$buku->penulis} ({$buku->tahun_terbit}) | Kategori: {$kat} | Stok Tersedia: {$buku->stok_tersedia}/{$buku->stok_total}";
            })
            ->join("\n");

        $pinjamanAktif = Peminjaman::with('buku')
            ->where('user_id', $user->id)
            ->aktif()
            ->get()
            ->map(function (Peminjaman $p): string {
                $jatuhTempo = $p->tanggal_jatuh_tempo ? $p->tanggal_jatuh_tempo->format('d-m-Y') : '-';

                return "- {$p->buku->judul} (Status: {$p->status}, Jatuh Tempo: {$jatuhTempo})";
            })
            ->join("\n");

        if ($pinjamanAktif === '') {
            $pinjamanAktif = '- Belum ada pinjaman aktif saat ini.';
        }

        return <<<PROMPT
Kamu adalah Asisten Pustakawan Digital "Lentera Pustaka".
Jawablah selalu dalam Bahasa Indonesia yang sopan, tenang, ringkas, dan jelas. Tanpa emoji berlebihan.

BATASAN PENTING:
1. Kamu HANYA boleh menjawab pertanyaan seputar perpustakaan Lentera Pustaka, katalog buku, ketersediaan stok, rekomendasi bacaan dari katalog, aturan peminjaman/pengembalian, dan status peminjaman anggota.
2. Jika pengguna bertanya di luar topik perpustakaan atau literasi buku, tolak dengan sopan dan arahkan kembali ke layanan Lentera Pustaka.
3. Kamu TIDAK BISA dan TIDAK BOLEH menyetujui peminjaman, mengubah stok, atau mengubah data sistem secara langsung. Jika pengguna membutuhkan tindakan petugas manusia, sarankan menekan tombol "Hubungi Petugas".

ATURAN RESMI LENTERA PUSTAKA:
- Kuota maksimal pinjaman aktif: {$kuota} buku per member.
- Durasi peminjaman: {$durasi} hari sejak buku diserahkan petugas.
- Batas pengambilan buku setelah disetujui: {$batasAmbil} hari (lewat dari itu batal otomatis).
- Satu judul buku yang sama tidak boleh dipinjam ganda dalam waktu bersamaan.
- Perpustakaan tidak menerapkan sistem denda / poin denda.

PROFIL ANGGOTA SAAT INI:
- Nama: {$user->nama_lengkap}
- Daftar Pinjaman Aktif:
{$pinjamanAktif}

DATA KATALOG BUKU & STOK SAAT INI:
{$daftarBuku}
PROMPT;
    }

    protected function panggilGeminiApi(Percakapan $percakapan, string $konteksSistem, string $pesanTerbaru): string
    {
        $apiKey = (string) config('perpustakaan.gemini.api_key');
        $model = (string) config('perpustakaan.gemini.model', 'gemini-2.5-flash');

        if (trim($apiKey) === '') {
            return $this->jawabanLokalCerdas($percakapan->user, $pesanTerbaru);
        }

        try {
            $riwayat = $percakapan->pesan()
                ->latest('id')
                ->limit(8)
                ->get()
                ->reverse()
                ->values();

            $contents = [];
            foreach ($riwayat as $item) {
                $contents[] = [
                    'role' => $item->pengirim === 'ai' ? 'model' : 'user',
                    'parts' => [['text' => $item->isi]],
                ];
            }

            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $response = Http::timeout(15)->post($url, [
                'system_instruction' => [
                    'parts' => [['text' => $konteksSistem]],
                ],
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.4,
                    'maxOutputTokens' => 600,
                ],
            ]);

            if ($response->successful()) {
                $teks = $response->json('candidates.0.content.parts.0.text');
                if (is_string($teks) && trim($teks) !== '') {
                    return trim($teks);
                }
            }

            Log::warning('Gemini API non-200 response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (Throwable $e) {
            Log::error('Gemini API error: '.$e->getMessage());
        }

        return $this->jawabanLokalCerdas($percakapan->user, $pesanTerbaru);
    }

    /**
     * Fallback cerdas berbasis konteks database apabila GEMINI_API_KEY belum diisi atau jaringan offline.
     */
    protected function jawabanLokalCerdas(User $user, string $pertanyaan): string
    {
        $q = mb_strtolower($pertanyaan);

        if (str_contains($q, 'aturan') || str_contains($q, 'syarat') || str_contains($q, 'durasi') || str_contains($q, 'kuota') || str_contains($q, 'berapa lama')) {
            $kuota = config('perpustakaan.kuota_pinjam_aktif', 3);
            $durasi = config('perpustakaan.durasi_pinjam_hari', 7);
            $batasAmbil = config('perpustakaan.batas_ambil_hari', 2);

            return "Berikut aturan peminjaman di Lentera Pustaka:\n1. Maksimal peminjaman aktif adalah {$kuota} buku.\n2. Durasi peminjaman adalah {$durasi} hari sejak buku diserahkan petugas.\n3. Buku yang telah disetujui wajib diambil maksimal dalam {$batasAmbil} hari.\n4. Lentera Pustaka tidak menerapkan sistem denda.";
        }

        if (str_contains($q, 'denda') || str_contains($q, 'poin')) {
            return 'Lentera Pustaka tidak menerapkan sistem denda atau poin denda. Namun, pastikan buku dikembalikan tepat waktu dan dalam kondisi baik demi kenyamanan seluruh anggota.';
        }

        if (str_contains($q, 'pinjaman saya') || str_contains($q, 'status pinjam') || str_contains($q, 'sedang saya pinjam')) {
            $aktif = Peminjaman::with('buku')->where('user_id', $user->id)->aktif()->get();
            if ($aktif->isEmpty()) {
                return 'Saat ini Anda belum memiliki transaksi peminjaman yang sedang aktif. Anda dapat menelusuri menu Katalog Buku untuk mengajukan peminjaman.';
            }
            $list = $aktif->map(fn ($p) => "- {$p->buku->judul} (Status: {$p->status})")->join("\n");

            return "Berikut ringkasan peminjaman aktif Anda saat ini:\n{$list}";
        }

        // Cari kecocokan judul atau penulis di database
        $bukuCocok = Buku::with('kategori')
            ->where(function ($query) use ($q): void {
                foreach (explode(' ', $q) as $kata) {
                    $kataBersih = trim($kata, " ?.!,'\"");
                    if (mb_strlen($kataBersih) >= 4 && ! in_array($kataBersih, ['buku', 'stok', 'apakah', 'cari', 'yang', 'ada', 'bisa', 'pinjam', 'tentang'], true)) {
                        $query->orWhere('judul', 'like', "%{$kataBersih}%")
                            ->orWhere('penulis', 'like', "%{$kataBersih}%");
                    }
                }
            })
            ->limit(5)
            ->get();

        if ($bukuCocok->isNotEmpty()) {
            $info = $bukuCocok->map(fn ($b) => "- {$b->judul} karya {$b->penulis} (Stok tersedia: {$b->stok_tersedia} dari {$b->stok_total})")->join("\n");

            return "Berikut informasi koleksi buku yang relevan di katalog Lentera Pustaka:\n{$info}\n\nAnda dapat melihat detail dan mengajukan peminjaman melalui menu Katalog.";
        }

        $tersedia = Buku::where('stok_tersedia', '>', 0)->inRandomOrder()->limit(4)->get();
        $rekomendasi = $tersedia->map(fn ($b) => "- {$b->judul} ({$b->penulis}) — Tersedia {$b->stok_tersedia} eks")->join("\n");

        return "Terima kasih telah menghubungi Asisten AI Lentera Pustaka. Saya dapat membantu memberikan informasi mengenai katalog buku, ketersediaan stok, aturan peminjaman, maupun rekomendasi bacaan.\n\nBeberapa koleksi yang tersedia hari ini antara lain:\n{$rekomendasi}\n\nApabila Anda memerlukan bantuan verifikasi langsung dari staf, silakan gunakan tombol Hubungi Petugas.";
    }
}
