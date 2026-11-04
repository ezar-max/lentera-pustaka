<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\KategoriBuku;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Administrator Tunggal
        User::create([
            'nama_lengkap' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
            'alamat' => 'Jl. Taman Pustaka No. 1, Jakarta Pusat',
            'no_hp' => '08111000200',
            'role' => 'admin',
            'status' => 'aktif',
            'total_poin_denda' => 0,
        ]);

        // 2. Master Kategori Buku (8 kategori)
        $kategoriList = [
            'Sastra & Fiksi',
            'Sejarah & Budaya',
            'Sains & Teknologi',
            'Filsafat & Pemikiran',
            'Pengembangan Diri',
            'Ekonomi & Bisnis',
            'Biografi & Memoar',
            'Arsitektur & Seni',
        ];

        $kategoriMap = [];
        foreach ($kategoriList as $nama) {
            $kategoriMap[$nama] = KategoriBuku::create(['nama_kategori' => $nama]);
        }

        // 3. Koleksi 20 Buku Contoh (seluruh stok utuh / belum ada pinjaman)
        $bukuData = [
            [
                'judul' => 'Bumi Manusia',
                'penulis' => 'Pramoedya Ananta Toer',
                'penerbit' => 'Lentera Dipantara',
                'tahun_terbit' => 2005,
                'isbn' => '978-979-97312-3-4',
                'sinopsis' => 'Roman tetralogi Pulau Buru yang mengisahkan perjalanan Minke, seorang pribumi terpelajar di masa kolonial Hindia Belanda yang memperjuangkan martabat dan keadilan melalui tulisan.',
                'stok_total' => 6,
                'stok_tersedia' => 6,
                'kategori' => ['Sastra & Fiksi', 'Sejarah & Budaya'],
            ],
            [
                'judul' => 'Laut Bercerita',
                'penulis' => 'Leila S. Chudori',
                'penerbit' => 'Kepustakaan Populer Gramedia',
                'tahun_terbit' => 2017,
                'isbn' => '978-602-424-694-5',
                'sinopsis' => 'Kisah persahabatan, idealisme, dan kehilangan para aktivis mahasiswa di penghujung era Orde Baru yang dituturkan dari sudut pandang Biru Laut dan adiknya, Asmara Jati.',
                'stok_total' => 5,
                'stok_tersedia' => 5,
                'kategori' => ['Sastra & Fiksi', 'Sejarah & Budaya'],
            ],
            [
                'judul' => 'Filosofi Teras',
                'penulis' => 'Henry Manampiring',
                'penerbit' => 'Penerbit Buku Kompas',
                'tahun_terbit' => 2018,
                'isbn' => '978-602-412-518-9',
                'sinopsis' => 'Pengantar filsafat Stoa Yunani-Romawi kuno yang relevan untuk mengatasi emosi negatif dan membangun ketangguhan mental di tengah hiruk-pikuk kehidupan modern.',
                'stok_total' => 8,
                'stok_tersedia' => 8,
                'kategori' => ['Filsafat & Pemikiran', 'Pengembangan Diri'],
            ],
            [
                'judul' => 'Cantik Itu Luka',
                'penulis' => 'Eka Kurniawan',
                'penerbit' => 'Gramedia Pustaka Utama',
                'tahun_terbit' => 2002,
                'isbn' => '978-602-03-1258-3',
                'sinopsis' => 'Perpaduan epik sejarah kolonialisme, mitologi lokal, dan tragedi keluarga Dewi Ayu di kota pelabuhan Halimunda.',
                'stok_total' => 4,
                'stok_tersedia' => 4,
                'kategori' => ['Sastra & Fiksi'],
            ],
            [
                'judul' => 'Sejarah Dunia yang Disembunyikan',
                'penulis' => 'Jonathan Black',
                'penerbit' => 'Pustaka Alvabet',
                'tahun_terbit' => 2015,
                'isbn' => '978-602-9193-67-1',
                'sinopsis' => 'Penelusuran mendalam terhadap sejarah peradaban manusia, tradisi intelektual kuno, dan perkembangan gagasan spiritual dari masa Mesir Kuno hingga era modern.',
                'stok_total' => 5,
                'stok_tersedia' => 5,
                'kategori' => ['Sejarah & Budaya', 'Filsafat & Pemikiran'],
            ],
            [
                'judul' => 'Sapiens: Riwayat Singkat Umat Manusia',
                'penulis' => 'Yuval Noah Harari',
                'penerbit' => 'Kepustakaan Populer Gramedia',
                'tahun_terbit' => 2017,
                'isbn' => '978-602-424-416-3',
                'sinopsis' => 'Penjelasan komprehensif bagaimana Homo sapiens berevolusi dari spesies kera biasa menjadi penguasa planet Bumi melalui revolusi kognitif, agrikultur, dan sains.',
                'stok_total' => 6,
                'stok_tersedia' => 6,
                'kategori' => ['Sejarah & Budaya', 'Sains & Teknologi'],
            ],
            [
                'judul' => 'Kosmos',
                'penulis' => 'Carl Sagan',
                'penerbit' => 'Kepustakaan Populer Gramedia',
                'tahun_terbit' => 2016,
                'isbn' => '978-602-424-224-4',
                'sinopsis' => 'Karya klasik astronomi populer yang menyingkap keindahan alam semesta, evolusi galaksi, dan perjalanan peradaban manusia dalam memahami ruang angkasa.',
                'stok_total' => 4,
                'stok_tersedia' => 4,
                'kategori' => ['Sains & Teknologi'],
            ],
            [
                'judul' => 'Madilog: Materialisme, Dialektika, dan Logika',
                'penulis' => 'Tan Malaka',
                'penerbit' => 'Narasi',
                'tahun_terbit' => 2014,
                'isbn' => '978-979-168-375-3',
                'sinopsis' => 'Karya pemikiran filosofis bangsa Indonesia yang mengajak masyarakat melepaskan diri dari logika mistika menuju cara berpikir ilmiah dan rasional.',
                'stok_total' => 3,
                'stok_tersedia' => 3,
                'kategori' => ['Filsafat & Pemikiran', 'Sejarah & Budaya'],
            ],
            [
                'judul' => 'Atomic Habits: Perubahan Kecil yang Memberikan Hasil Luar Biasa',
                'penulis' => 'James Clear',
                'penerbit' => 'Gramedia Pustaka Utama',
                'tahun_terbit' => 2019,
                'isbn' => '978-602-06-3317-6',
                'sinopsis' => 'Panduan praktis berbasis neurosains dan psikologi perilaku untuk membangun kebiasaan baik dan menghentikan kebiasaan buruk melalui perbaikan satu persen setiap hari.',
                'stok_total' => 7,
                'stok_tersedia' => 7,
                'kategori' => ['Pengembangan Diri'],
            ],
            [
                'judul' => 'Psikologi Uang (The Psychology of Money)',
                'penulis' => 'Morgan Housel',
                'penerbit' => 'Baca',
                'tahun_terbit' => 2021,
                'isbn' => '978-602-6486-57-8',
                'sinopsis' => 'Kumpulan pelajaran abadi mengenai kekayaan, ketamakan, dan kebahagiaan yang menunjukkan bahwa mengelola uang lebih berkaitan dengan perilaku dibandingkan kecerdasan teknis.',
                'stok_total' => 5,
                'stok_tersedia' => 5,
                'kategori' => ['Ekonomi & Bisnis', 'Pengembangan Diri'],
            ],
            [
                'judul' => 'Pulang',
                'penulis' => 'Leila S. Chudori',
                'penerbit' => 'Kepustakaan Populer Gramedia',
                'tahun_terbit' => 2012,
                'isbn' => '978-979-91-0515-8',
                'sinopsis' => 'Perjalanan hidup Dimas Suryo, eksil politik Indonesia di Paris setelah peristiwa 1965, beserta kerinduan mendalam pada tanah air dan keluarganya.',
                'stok_total' => 4,
                'stok_tersedia' => 4,
                'kategori' => ['Sastra & Fiksi', 'Sejarah & Budaya'],
            ],
            [
                'judul' => 'Laskar Pelangi',
                'penulis' => 'Andrea Hirata',
                'penerbit' => 'Bentang Pustaka',
                'tahun_terbit' => 2005,
                'isbn' => '978-979-3062-79-2',
                'sinopsis' => 'Kisah inspiratif sepuluh anak dari keluarga kurang mampu di Pulau Belitung yang menempuh pendidikan di sekolah Muhammadiyah dengan penuh semangat.',
                'stok_total' => 6,
                'stok_tersedia' => 6,
                'kategori' => ['Sastra & Fiksi'],
            ],
            [
                'judul' => 'Catatan Seorang Demonstran',
                'penulis' => 'Soe Hok Gie',
                'penerbit' => 'LP3ES',
                'tahun_terbit' => 2005,
                'isbn' => '978-979-3330-04-4',
                'sinopsis' => 'Catatan harian intelektual muda Indonesia tentang pergolakan sosial, kejujuran nurani, kecintaan pada alam, dan kritik terhadap kekuasaan.',
                'stok_total' => 3,
                'stok_tersedia' => 3,
                'kategori' => ['Biografi & Memoar', 'Sejarah & Budaya'],
            ],
            [
                'judul' => 'Hatta: Aku Datang karena Sejarah',
                'penulis' => 'Sergius Sutanto',
                'penerbit' => 'Qanita',
                'tahun_terbit' => 2013,
                'isbn' => '978-602-1637-10-4',
                'sinopsis' => 'Biografi naratif yang menggambarkan masa muda Mohammad Hatta, ketekunannya pada buku, dan perjuangannya merumuskan kemerdekaan Indonesia.',
                'stok_total' => 4,
                'stok_tersedia' => 4,
                'kategori' => ['Biografi & Memoar', 'Sejarah & Budaya'],
            ],
            [
                'judul' => 'Dunia Sophie',
                'penulis' => 'Jostein Gaarder',
                'penerbit' => 'Mizan Pustaka',
                'tahun_terbit' => 2014,
                'isbn' => '978-979-433-838-4',
                'sinopsis' => 'Novel pengantar sejarah filsafat Barat yang dikemas dalam surat-surat misterius kepada seorang gadis remaja bernama Sophie Amundsen.',
                'stok_total' => 5,
                'stok_tersedia' => 5,
                'kategori' => ['Filsafat & Pemikiran', 'Sastra & Fiksi'],
            ],
            [
                'judul' => 'Arsitektur Tradisional Nusantara',
                'penulis' => 'Yulianto Sumalyo',
                'penerbit' => 'Gadjah Mada University Press',
                'tahun_terbit' => 2019,
                'isbn' => '978-602-386-312-9',
                'sinopsis' => 'Kajian estetika, struktur kayu, dan kearifan ekologis bangunan tradisional di berbagai kepulauan Indonesia.',
                'stok_total' => 2,
                'stok_tersedia' => 2,
                'kategori' => ['Arsitektur & Seni', 'Sejarah & Budaya'],
            ],
            [
                'judul' => 'Clean Code: Panduan Praktis Rekayasa Perangkat Lunak',
                'penulis' => 'Robert C. Martin',
                'penerbit' => 'Prentice Hall Indonesia',
                'tahun_terbit' => 2020,
                'isbn' => '978-0-13-235088-4',
                'sinopsis' => 'Prinsip, pola, dan praktik terbaik dalam menulis kode program yang bersih, mudah dipelihara, dan terstruktur secara profesional.',
                'stok_total' => 5,
                'stok_tersedia' => 5,
                'kategori' => ['Sains & Teknologi'],
            ],
            [
                'judul' => 'Thinking, Fast and Slow (Berpikir Cepat dan Lambat)',
                'penulis' => 'Daniel Kahneman',
                'penerbit' => 'Gramedia Pustaka Utama',
                'tahun_terbit' => 2018,
                'isbn' => '978-602-03-8321-7',
                'sinopsis' => 'Penjelasan peraih Nobel Ekonomi mengenai dua sistem berpikir manusia yang membentuk penilaian dan pengambilan keputusan sehari-hari.',
                'stok_total' => 4,
                'stok_tersedia' => 4,
                'kategori' => ['Ekonomi & Bisnis', 'Pengembangan Diri'],
            ],
            [
                'judul' => 'Ronggeng Dukuh Paruk',
                'penulis' => 'Ahmad Tohari',
                'penerbit' => 'Gramedia Pustaka Utama',
                'tahun_terbit' => 2011,
                'isbn' => '978-979-22-7341-0',
                'sinopsis' => 'Potret kehidupan pedesaan Jawa yang bersahaja melalui kisah Srintil, penari ronggeng di Dukuh Paruk, dan Rasus sahabat masa kecilnya.',
                'stok_total' => 3,
                'stok_tersedia' => 3,
                'kategori' => ['Sastra & Fiksi', 'Arsitektur & Seni'],
            ],
            [
                'judul' => 'Ekonomi Pembangunan Berkelanjutan di Indonesia',
                'penulis' => 'Emil Salim',
                'penerbit' => 'LP3ES',
                'tahun_terbit' => 2021,
                'isbn' => '978-602-7984-45-5',
                'sinopsis' => 'Gagasan strategis mengenai keseimbangan antara pertumbuhan ekonomi nasional dan pelestarian lingkungan hidup jangka panjang.',
                'stok_total' => 2,
                'stok_tersedia' => 2,
                'kategori' => ['Ekonomi & Bisnis', 'Sains & Teknologi'],
            ],
        ];

        foreach ($bukuData as $item) {
            $kategoriNames = $item['kategori'];
            unset($item['kategori']);

            $buku = Buku::create($item);
            $kategoriIds = array_map(fn ($name) => $kategoriMap[$name]->id, $kategoriNames);
            $buku->kategori()->sync($kategoriIds);
        }
    }
}
