<?php

namespace App\Services;

use App\Models\Buku;
use App\Models\KategoriBuku;
use App\Models\Peminjaman;
use App\Models\Percakapan;
use App\Models\Pesan;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
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

        $konteksSistem = $this->susunKonteks($user, $isiPesan);
        $jawabanAi = $this->panggilGeminiApi($percakapan, $konteksSistem, $isiPesan);
        $jawabanBersih = $this->bersihkanFormatText($jawabanAi);

        return Pesan::create([
            'percakapan_id' => $percakapan->id,
            'pengirim' => 'ai',
            'pengirim_id' => null,
            'isi' => $jawabanBersih,
            'dibaca' => true,
        ]);
    }

    /**
     * Membersihkan teks dari simbol aneh, markdown tebal (**teks**),
     * pagar heading (#), dan emoji/icon agar output selalu rapi dan bersih.
     */
    public function bersihkanFormatText(string $text): string
    {
        // Hapus penanda format tebal / miring markdown (**teks**, *teks*, __teks__, #)
        $text = str_replace(['**', '__', '*', '#'], '', $text);

        // Hapus icon centang, peringatan, silang, dan emoji umum
        $text = (string) preg_replace('/[\x{2700}-\x{27BF}\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2B50}\x{FE0F}]/u', '', $text);

        // Bersihkan spasi ganda atau baris kosong berlebih
        $text = (string) preg_replace('/[ \t]+/', ' ', $text);
        $text = (string) preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }

    /**
     * Mencari buku yang relevan dari database perpustakaan berdasarkan kata kunci pertanyaan user.
     *
     * @return Collection<int, Buku>
     */
    public function cariBukuRelevan(string $pertanyaan): Collection
    {
        $q = mb_strtolower(trim($pertanyaan));
        $stopwords = [
            'apakah', 'ada', 'buku', 'judul', 'koleksi', 'stok', 'yang', 'di', 'perpustakaan',
            'lentera', 'pustaka', 'tolong', 'cari', 'carikan', 'mau', 'tanya', 'ingin', 'dong',
            'min', 'halo', 'hai', 'bisa', 'tentang', 'kategori', 'genre', 'karya', 'penulis',
            'karangan', 'terbitan', 'tahun', 'tersedia', 'habis', 'gak', 'nggak', 'tidak', 'kah',
            'apa', 'saja', 'punya', 'punyak', 'tersediakah', 'masih', 'bacaan', 'buat', 'rekomendasi',
            'ini', 'itu', 'ya', 'ga', 'nih', 'gitu', 'atau', 'sama',
        ];

        $bersih = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $q);
        $kataKata = array_values(array_filter(
            explode(' ', (string) $bersih),
            fn ($k) => mb_strlen($k) >= 2 && ! in_array($k, $stopwords, true)
        ));

        if (empty($kataKata)) {
            return new Collection;
        }

        $query = Buku::with('kategori');

        $query->where(function ($qb) use ($kataKata): void {
            $frasaGabung = implode(' ', $kataKata);
            if (mb_strlen($frasaGabung) >= 3) {
                $qb->orWhere('judul', 'like', "%{$frasaGabung}%")
                    ->orWhere('penulis', 'like', "%{$frasaGabung}%");
            }

            foreach ($kataKata as $kata) {
                if (mb_strlen($kata) >= 3) {
                    $qb->orWhere('judul', 'like', "%{$kata}%")
                        ->orWhere('penulis', 'like', "%{$kata}%")
                        ->orWhere('penerbit', 'like', "%{$kata}%")
                        ->orWhere('sinopsis', 'like', "%{$kata}%")
                        ->orWhereHas('kategori', fn ($katQuery) => $katQuery->where('nama_kategori', 'like', "%{$kata}%"));
                }
            }
        });

        return $query->limit(6)->get();
    }

    /**
     * Menyusun instruksi sistem dan data perpustakaan untuk AI Gemini.
     */
    public function susunKonteks(User $user, string $pertanyaanUser = ''): string
    {
        $kuota = config('perpustakaan.kuota_pinjam_aktif', 3);
        $durasi = config('perpustakaan.durasi_pinjam_hari', 7);
        $batasAmbil = config('perpustakaan.batas_ambil_hari', 2);

        $bukuRelevan = $pertanyaanUser !== '' ? $this->cariBukuRelevan($pertanyaanUser) : new Collection;
        $hasilPencarianDb = '';

        if ($bukuRelevan->isNotEmpty()) {
            $hasilPencarianDb = "HASIL PENGECEKAN DATABASE UNTUK PERTANYAAN ANGGOTA SAAT INI:\n";
            foreach ($bukuRelevan as $b) {
                $kat = $b->kategori->pluck('nama_kategori')->join(', ') ?: 'Umum';
                if ($b->stok_tersedia > 0) {
                    $hasilPencarianDb .= "- Buku: {$b->judul} oleh {$b->penulis} ({$b->tahun_terbit}) | Kategori: {$kat} | STOK: Tersedia {$b->stok_tersedia} dari total {$b->stok_total} eksemplar. Status: Siap dipinjam.\n";
                } else {
                    $hasilPencarianDb .= "- Buku: {$b->judul} oleh {$b->penulis} ({$b->tahun_terbit}) | Kategori: {$kat} | STOK: 0 eksemplar tersedia (total {$b->stok_total}). Status: Sedang dipinjam anggota lain.\n";
                }
            }
        } else {
            $hasilPencarianDb = "HASIL PENGECEKAN DATABASE UNTUK PERTANYAAN ANGGOTA SAAT INI:\n- Buku atau kata kunci ini TIDAK ADA di dalam database perpustakaan Lentera Pustaka.\n";
        }

        $pinjamanAktif = Peminjaman::with('buku')
            ->where('user_id', $user->id)
            ->aktif()
            ->get()
            ->map(function (Peminjaman $p): string {
                $jatuhTempo = $p->tanggal_jatuh_tempo ? $p->tanggal_jatuh_tempo->format('d-m-Y') : '-';

                return "- {$p->buku->judul} (status: {$p->status}, jatuh tempo: {$jatuhTempo})";
            })
            ->join("\n");

        if ($pinjamanAktif === '') {
            $pinjamanAktif = '- Tidak ada peminjaman aktif.';
        }

        $daftarKategori = KategoriBuku::pluck('nama_kategori')->join(', ');

        return <<<PROMPT
PERAN:
Kamu adalah Asisten Pustakawan Lentera Pustaka yang ramah, santun, dan solutif.
Jawablah semua pertanyaan yang berkaitan dengan perpustakaan kami, buku, stok, aturan, status peminjaman, sinopsis, atau rekomendasi bacaan.

ATURAN PENULISAN & FORMAT JAWABAN (WAJIB DIIKUTI):
1. JANGAN PERNAH MENGGUNAKAN SIMBOL FONT KHUSUS ATAU RAW MARKDOWN:
   - DILARANG menggunakan tanda bintang ganda (seperti **teks**), tanda bintang satu (*teks*), pagar (#), atau garis bawah (__teks__).
   - Tuliskan judul buku dalam teks biasa dengan huruf kapital yang benar, contoh: Buku Sejarah Dunia yang Disembunyikan karya Jonathan Black.
   - DILARANG menggunakan emoji atau icon apa pun (jangan gunakan centang, tanda seru, buku, dll.).
   - JANGAN membuat format daftar kaku seperti formulir (- Kategori: ..., - Stok: ..., - Status: ...). Buatlah kalimat percakapan bahasa Indonesia yang mengalir rapi dan enak dibaca.

2. PENULISAN HARUS VARIATIF DAN TIDAK MONOTON:
   - Jangan gunakan pola kalimat yang kaku atau template yang diulang-ulang. Rangkailah kalimat yang luwes dan natural layaknya pustakawan sungguhan yang menyapa pengunjung.

3. KETENTUAN PENTING KETIKA ANGGOTA MENANYAKAN BUKU:
   a. JIKA BUKU TIDAK ADA DI DATABASE:
      - Jawab langsung secara singkat, sopan, dan jelas bahwa buku tersebut saat ini tidak tersedia / belum ada di perpustakaan Lentera Pustaka.
      - PENTING: JANGAN membeberkan atau menyebutkan daftar buku-buku lain yang ada di perpustakaan jika anggota tidak memintanya. Cukup jawab bahwa bukunya tidak ada.
   b. JIKA BUKU ADA DAN STOK TERSEDIA:
      - Sampaikan bahwa buku tersebut tersedia di perpustakaan, sebutkan penulis dan jumlah stok yang siap dipinjam. Beri tahu mereka bisa meminjamnya lewat menu Katalog Buku.
   c. JIKA BUKU ADA TETAPI STOK KOSONG (0):
      - Sampaikan bahwa bukunya terdaftar di perpustakaan, namun saat ini seluruh stok sedang dipinjam anggota lain.

4. TOPIK DI LUAR PERPUSTAKAAN:
   - Jika pengguna bertanya hal yang sama sekali di luar urusan perpustakaan atau literasi buku (misalnya cuaca, politik, resep, koding umum, dll.), tolak dengan santun bahwa kamu adalah asisten pustakawan yang khusus melayani informasi perpustakaan Lentera Pustaka.

ATURAN SIRKULASI KAMI:
- Kuota maksimal peminjaman aktif: {$kuota} buku.
- Durasi pinjam: {$durasi} hari kalender.
- Batas pengambilan buku setelah disetujui: {$batasAmbil} hari.
- Tidak ada sistem poin atau denda.

DATA ANGGOTA:
- Nama: {$user->nama_lengkap}
- Pinjaman Aktif:
{$pinjamanAktif}

{$hasilPencarianDb}

KATEGORI BUKU DI PERPUSTAKAAN KAMI:
{$daftarKategori}
PROMPT;
    }

    /**
     * Memanggil Google Gemini API resmi (gemini-2.5-flash).
     * Jika terjadi kendala jaringan/timeout, beralih ke jawaban lokal cerdas.
     */
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
                ->limit(6)
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

            $modelTarget = trim($model) !== '' ? $model : 'gemini-2.5-flash';
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelTarget}:generateContent?key={$apiKey}";

            $response = Http::timeout(12)->post($url, [
                'system_instruction' => [
                    'parts' => [['text' => $konteksSistem]],
                ],
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.4,
                    'maxOutputTokens' => 700,
                ],
            ]);

            if ($response->successful()) {
                $teks = $response->json('candidates.0.content.parts.0.text');
                if (is_string($teks) && trim($teks) !== '') {
                    return trim($teks);
                }
            }

            Log::warning('Gemini API non-200: '.$response->status(), [
                'body' => $response->body(),
            ]);
        } catch (Throwable $e) {
            Log::error('Gemini API error: '.$e->getMessage());
        }

        return $this->jawabanLokalCerdas($percakapan->user, $pesanTerbaru);
    }

    /**
     * Mesin Bahasa Alami Lokal (Natural Response Engine):
     * Tanpa format kaku, tanpa simbol ganjil / asterisks markdown,
     * tanpa emoji, serta kalimat bervariasi.
     */
    protected function jawabanLokalCerdas(User $user, string $pertanyaan): string
    {
        $q = mb_strtolower(trim($pertanyaan));

        // 1. Cek pertanyaan di luar lingkup perpustakaan
        $topikLuar = [
            'cuaca', 'presiden', 'politik', 'matematika', 'resep', 'masak', 'koding', 'coding',
            'programming', 'javascript', 'python', 'java', 'html', 'css', 'game', 'bermain',
            'pacar', 'jodoh', 'cinta', 'puisi cinta', 'gubernur', 'pemilu',
        ];
        foreach ($topikLuar as $tl) {
            if (str_contains($q, $tl) && ! str_contains($q, 'buku') && ! str_contains($q, 'perpustakaan')) {
                $penolakan = [
                    'Mohon maaf, saya adalah Asisten Pustakawan Lentera Pustaka. Saya hanya dapat melayani pertanyaan seputar koleksi buku, ketersediaan stok, aturan peminjaman, dan layanan perpustakaan kami.',
                    'Sebagai asisten perpustakaan Lentera Pustaka, tugas saya khusus membantu kebutuhan buku, stok, serta sirkulasi peminjaman. Silakan tanyakan hal seputar perpustakaan kami.',
                ];

                return $penolakan[array_rand($penolakan)];
            }
        }

        // 2. Pertanyaan aturan sirkulasi perpustakaan
        if (str_contains($q, 'aturan') || str_contains($q, 'syarat') || str_contains($q, 'durasi') || str_contains($q, 'kuota') || str_contains($q, 'berapa lama') || str_contains($q, 'cara pinjam')) {
            $kuota = config('perpustakaan.kuota_pinjam_aktif', 3);
            $durasi = config('perpustakaan.durasi_pinjam_hari', 7);
            $batasAmbil = config('perpustakaan.batas_ambil_hari', 2);

            $variasiAturan = [
                "Untuk aturan peminjaman di Lentera Pustaka, setiap anggota dapat meminjam maksimal {$kuota} buku sekaligus dengan durasi pinjam selama {$durasi} hari. Setelah pengajuan disetujui, buku perlu diambil di perpustakaan dalam waktu {$batasAmbil} hari. Perpustakaan kami juga tidak memberlakukan sistem denda.",
                "Peminjaman buku di Lentera Pustaka memiliki batas maksimal {$kuota} buku aktif per anggota dengan masa peminjaman {$durasi} hari sejak buku diserahkan. Batas pengambilan buku yang disetujui adalah {$batasAmbil} hari kerja, dan sistem kami tidak mengenakan denda apa pun.",
            ];

            return $variasiAturan[array_rand($variasiAturan)];
        }

        // 3. Pertanyaan status pinjaman aktif anggota
        if (str_contains($q, 'pinjaman saya') || str_contains($q, 'status pinjam') || str_contains($q, 'sedang saya pinjam')) {
            $aktif = Peminjaman::with('buku')->where('user_id', $user->id)->aktif()->get();
            if ($aktif->isEmpty()) {
                return 'Saat ini Anda tidak memiliki peminjaman buku yang sedang aktif di Lentera Pustaka. Anda dapat memilih buku di menu Katalog Buku jika ingin meminjam.';
            }

            $daftar = $aktif->map(function ($p): string {
                $tgl = $p->tanggal_jatuh_tempo ? $p->tanggal_jatuh_tempo->format('d M Y') : 'belum ditentukan';

                return "{$p->buku->judul} dengan status {$p->status} dan batas pengembalian tanggal {$tgl}";
            })->join('. Selain itu ada ');

            return "Saat ini Anda sedang meminjam {$daftar}. Mohon pastikan buku dikembalikan tepat waktu.";
        }

        // 4. Cari kecocokan buku secara langsung di database perpustakaan
        $bukuCocok = $this->cariBukuRelevan($pertanyaan);

        // Jika buku DITEMUKAN di database
        if ($bukuCocok->isNotEmpty()) {
            $b = $bukuCocok->first();
            $kat = $b->kategori->pluck('nama_kategori')->join(', ') ?: 'Umum';

            if ($b->stok_tersedia > 0) {
                $variasiAda = [
                    "Buku {$b->judul} karya {$b->penulis} tersedia di perpustakaan kami. Saat ini stoknya ada {$b->stok_tersedia} eksemplar di kategori {$kat} dan siap dipinjam melalui menu Katalog Buku.",
                    "Untuk buku {$b->judul} yang ditulis oleh {$b->penulis}, stoknya masih tersedia {$b->stok_tersedia} dari total {$b->stok_total} eksemplar. Anda dapat langsung mengajukan peminjaman sekarang di menu Katalog.",
                    "Iya, buku {$b->judul} karya {$b->penulis} ada di Lentera Pustaka. Kami memiliki {$b->stok_tersedia} eksemplar yang siap dipinjam oleh anggota.",
                ];

                return $variasiAda[array_rand($variasiAda)];
            }

            // Jika buku ada di database tapi stok habis
            $variasiHabis = [
                "Buku {$b->judul} karya {$b->penulis} terdaftar dalam koleksi kami, tetapi saat ini stoknya sedang habis karena semua eksemplar sedang dipinjam anggota lain. Silakan cek kembali beberapa hari ke depan.",
                "Koleksi buku {$b->judul} karangan {$b->penulis} ada di katalog Lentera Pustaka, namun stoknya saat ini sedang kosong karena sedang dipinjam. Anda bisa mengeceknya secara berkala.",
            ];

            return $variasiHabis[array_rand($variasiHabis)];
        }

        // 5. Jika anggota menanyakan apakah suatu buku ada, tetapi TIDAK ditemukan di database:
        // Cukup jawab tidak ada, JANGAN memberi tahu atau mendaftar buku-buku lain!
        $kataTanyaBuku = [
            'apakah ada', 'ada buku', 'cari buku', 'stok buku', 'apakah punya', 'punya buku',
            'ada gak', 'ada ga', 'buku ini ada', 'judul', 'nemu buku', 'buku',
        ];
        foreach ($kataTanyaBuku as $ktb) {
            if (str_contains($q, $ktb)) {
                $variasiTidakAda = [
                    'Mohon maaf, buku yang Anda tanyakan saat ini belum tersedia di perpustakaan Lentera Pustaka.',
                    'Buku tersebut belum ada dalam koleksi perpustakaan kami untuk saat ini.',
                    'Mohon maaf, kami belum memiliki koleksi buku tersebut di Lentera Pustaka.',
                    'Buku yang Anda cari saat ini tidak ditemukan di dalam katalog perpustakaan Lentera Pustaka.',
                ];

                return $variasiTidakAda[array_rand($variasiTidakAda)];
            }
        }

        // 6. Jawaban umum jika hanya menyapa atau meminta bantuan
        $variasiSapa = [
            "Halo {$user->nama_lengkap}! Saya Asisten Pustakawan Lentera Pustaka. Ada yang bisa saya bantu terkait ketersediaan buku, aturan peminjaman, atau layanan perpustakaan kami?",
            "Selamat datang {$user->nama_lengkap}. Silakan tanyakan judul buku yang ingin Anda cari, ketersediaan stok, maupun aturan peminjaman di Lentera Pustaka.",
        ];

        return $variasiSapa[array_rand($variasiSapa)];
    }
}
