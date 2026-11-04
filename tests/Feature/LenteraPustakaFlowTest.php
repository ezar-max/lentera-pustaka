<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Denda;
use App\Models\KategoriBuku;
use App\Models\Peminjaman;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LenteraPustakaFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_publik_beranda_katalog_dan_detail_buku_dapat_diakses(): void
    {
        $kategori = KategoriBuku::factory()->create(['nama_kategori' => 'Sastra']);
        $buku = Buku::factory()->create([
            'judul' => 'Bumi Manusia',
            'stok_total' => 5,
            'stok_tersedia' => 5,
        ]);
        $buku->kategori()->sync([$kategori->id]);

        $this->get('/')->assertStatus(200)->assertSee('Lentera Pustaka');
        $this->get('/katalog')->assertStatus(200)->assertSee('Bumi Manusia');
        $this->get("/buku/{$buku->id}")->assertStatus(200)->assertSee('Bumi Manusia');
    }

    public function test_aturan_1_registrasi_pending_dan_persetujuan_atau_penolakan_admin(): void
    {
        $response = $this->post('/register', [
            'nama_lengkap' => 'Budi Santoso',
            'username' => 'budisantoso',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'alamat' => 'Jl. Merdeka No. 10',
            'no_hp' => '081234567890',
        ]);

        $response->assertRedirect('/login');
        $user = User::where('username', 'budisantoso')->firstOrFail();
        $this->assertSame('pending', $user->status);

        // Coba login saat masih pending -> ditolak masuk
        $this->post('/login', [
            'identitas' => 'budisantoso',
            'password' => 'password123',
        ])->assertSessionHas('warning');
        $this->assertGuest();

        // Admin menolak dengan alasan -> alasan tampil saat coba login
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->post("/admin/member-pending/{$user->id}/tolak", [
                'alasan_penolakan' => 'Nomor telepon tidak aktif saat dihubungi.',
            ])
            ->assertSessionHas('success');

        $this->assertSame('ditolak', $user->fresh()->status);

        Auth()->logout();

        $this->post('/login', [
            'identitas' => 'budisantoso',
            'password' => 'password123',
        ])->assertSessionHas('error', 'Pendaftaran akun Anda ditolak oleh Administrator. Alasan: Nomor telepon tidak aktif saat dihubungi.');

        // Admin menyetujui -> member bisa login
        $this->actingAs($admin)
            ->post("/admin/member-pending/{$user->id}/setujui")
            ->assertSessionHas('success');

        Auth()->logout();

        $this->post('/login', [
            'identitas' => 'budisantoso',
            'password' => 'password123',
        ])->assertRedirect('/member/dashboard');
    }

    public function test_aturan_2_3_4_validasi_pinjaman_ganda_stok_dan_kuota(): void
    {
        $member = User::factory()->create();
        $buku1 = Buku::factory()->create(['stok_total' => 3, 'stok_tersedia' => 3]);
        $bukuHabis = Buku::factory()->create(['stok_total' => 2, 'stok_tersedia' => 0]);

        // Pinjam buku1 berhasil
        $this->actingAs($member)
            ->post('/member/peminjaman', ['buku_id' => $buku1->id])
            ->assertSessionHas('success');

        // Pinjam buku1 lagi (ganda) gagal
        $this->actingAs($member)
            ->post('/member/peminjaman', ['buku_id' => $buku1->id])
            ->assertSessionHas('error');

        // Pinjam buku yang stok_tersedia = 0 gagal
        $this->actingAs($member)
            ->post('/member/peminjaman', ['buku_id' => $bukuHabis->id])
            ->assertSessionHas('error');

        // Tambah 2 buku lagi hingga kuota 3 penuh
        $buku2 = Buku::factory()->create(['stok_total' => 2, 'stok_tersedia' => 2]);
        $buku3 = Buku::factory()->create(['stok_total' => 2, 'stok_tersedia' => 2]);
        $buku4 = Buku::factory()->create(['stok_total' => 2, 'stok_tersedia' => 2]);

        $this->actingAs($member)->post('/member/peminjaman', ['buku_id' => $buku2->id]);
        $this->actingAs($member)->post('/member/peminjaman', ['buku_id' => $buku3->id]);

        $this->assertEquals(3, Peminjaman::where('user_id', $member->id)->aktif()->count());

        // Pinjaman ke-4 ditolak karena kuota maksimal 3 buku
        $this->actingAs($member)
            ->post('/member/peminjaman', ['buku_id' => $buku4->id])
            ->assertSessionHas('error');
    }

    public function test_alur_lengkap_peminjaman_pengembalian_stok_dan_denda_serta_blokir_50_poin(): void
    {
        $petugas = User::factory()->petugas()->create();
        $member = User::factory()->create(['total_poin_denda' => 0]);
        $buku = Buku::factory()->create(['stok_total' => 4, 'stok_tersedia' => 4]);

        // 1. Member ajukan peminjaman
        $this->actingAs($member)->post('/member/peminjaman', ['buku_id' => $buku->id]);
        $pinjam = Peminjaman::firstOrFail();
        $this->assertSame('diajukan', $pinjam->status);
        $this->assertSame(4, $buku->fresh()->stok_tersedia);

        // 2. Petugas setujui -> stok_tersedia berkurang 1 menjadi 3
        $this->actingAs($petugas)
            ->post("/petugas/peminjaman/{$pinjam->id}/setuju")
            ->assertSessionHas('success');

        $this->assertSame('disetujui', $pinjam->fresh()->status);
        $this->assertSame(3, $buku->fresh()->stok_tersedia);

        // 3. Petugas serahkan buku -> status dipinjam
        $this->actingAs($petugas)
            ->post("/petugas/peminjaman/{$pinjam->id}/serahkan")
            ->assertSessionHas('success');

        $this->assertSame('dipinjam', $pinjam->fresh()->status);

        // Simulasikan buku terlambat 15 hari (15 * 2 = 30 poin) dan kondisi rusak (+25 poin) = 55 poin (>= 50 poin blokir)
        $pinjam->update([
            'tanggal_pinjam' => Carbon::today()->subDays(22),
            'tanggal_jatuh_tempo' => Carbon::today()->subDays(15),
        ]);

        // 4. Member ajukan pengembalian
        $this->actingAs($member)
            ->post("/member/peminjaman/{$pinjam->id}/ajukan-kembali")
            ->assertSessionHas('success');
        $this->assertSame('pengajuan_kembali', $pinjam->fresh()->status);

        // 5. Petugas setujui pengembalian
        $this->actingAs($petugas)
            ->post("/petugas/pengembalian/{$pinjam->id}/setujui")
            ->assertSessionHas('success');
        $this->assertSame('kembali_disetujui', $pinjam->fresh()->status);

        // 6. Petugas konfirmasi pengembalian fisik dengan kondisi rusak
        $this->actingAs($petugas)
            ->post("/petugas/pengembalian/{$pinjam->id}/konfirmasi", [
                'kondisi_buku' => 'rusak',
                'catatan_petugas' => 'Sampul belakang robek dan terlambat 15 hari.',
            ])
            ->assertSessionHas('success');

        $pinjamFresh = $pinjam->fresh();
        $this->assertSame('selesai', $pinjamFresh->status);
        $this->assertSame('rusak', $pinjamFresh->kondisi_buku);
        $this->assertSame('terlambat', $pinjamFresh->ketepatan_waktu);
        $this->assertSame(15, $pinjamFresh->hari_terlambat);

        // Stok tersedia kembali +1 menjadi 4
        $this->assertSame(4, $buku->fresh()->stok_tersedia);

        // Cek akumulasi poin denda user: 30 + 25 = 55 poin
        $memberFresh = $member->fresh();
        $this->assertSame(55, $memberFresh->total_poin_denda);
        $this->assertTrue($memberFresh->isBlockedByDenda());

        // Member mencoba meminjam lagi saat poin >= 50 -> gagal terblokir
        $this->actingAs($memberFresh)
            ->post('/member/peminjaman', ['buku_id' => $buku->id])
            ->assertSessionHas('error');

        // Petugas menandai lunas semua denda user -> total_poin_denda kembali 0 -> member bisa pinjam lagi
        $this->actingAs($petugas)
            ->post("/petugas/denda/user/{$member->id}/lunasi-semua")
            ->assertSessionHas('success');

        $this->assertSame(0, $member->fresh()->total_poin_denda);

        $this->actingAs($member->fresh())
            ->post('/member/peminjaman', ['buku_id' => $buku->id])
            ->assertSessionHas('success');
    }

    public function test_command_batalkan_pengajuan_kadaluarsa_mengembalikan_stok(): void
    {
        $member = User::factory()->create();
        $buku = Buku::factory()->create(['stok_total' => 5, 'stok_tersedia' => 4]);

        $pinjam = Peminjaman::create([
            'user_id' => $member->id,
            'buku_id' => $buku->id,
            'status' => 'disetujui',
            'tanggal_pengajuan' => Carbon::today()->subDays(4),
            'tanggal_disetujui' => Carbon::today()->subDays(3), // Lewat batas 2 hari
            'hari_terlambat' => 0,
        ]);

        $this->artisan('perpustakaan:batalkan-kadaluarsa')->assertExitCode(0);

        $this->assertSame('dibatalkan', $pinjam->fresh()->status);
        $this->assertSame(5, $buku->fresh()->stok_tersedia);
    }

    public function test_semua_halaman_member_petugas_admin_laporan_pdf_dan_chat_berjalan(): void
    {
        $admin = User::factory()->admin()->create();
        $petugas = User::factory()->petugas()->create();
        $member = User::factory()->create();
        $kategori = KategoriBuku::factory()->create(['nama_kategori' => 'Sejarah']);
        $buku = Buku::factory()->create(['stok_total' => 5, 'stok_tersedia' => 5]);
        $buku->kategori()->sync([$kategori->id]);

        // Halaman Member
        $this->actingAs($member)->get('/member/dashboard')->assertStatus(200);
        $this->actingAs($member)->get('/member/katalog')->assertStatus(200);
        $this->actingAs($member)->get("/member/katalog/{$buku->id}")->assertStatus(200);
        $this->actingAs($member)->post("/member/katalog/{$buku->id}/ulasan", [
            'rating' => 5,
            'ulasan' => 'Buku sejarah yang sangat informatif dan mendalam.',
        ])->assertSessionHas('success');
        $this->actingAs($member)->post("/member/koleksi/{$buku->id}/toggle")->assertSessionHas('success');
        $this->actingAs($member)->get('/member/koleksi')->assertStatus(200);
        $this->actingAs($member)->get('/member/peminjaman')->assertStatus(200);
        $this->actingAs($member)->get('/member/denda')->assertStatus(200);
        $this->actingAs($member)->get('/member/chat')->assertStatus(200);
        $this->actingAs($member)->post('/member/chat/ai', ['isi' => 'Apa saja aturan peminjaman?'])->assertRedirect();
        $this->actingAs($member)->post('/member/chat/hubungi-petugas')->assertRedirect();
        $this->actingAs($member)->post('/member/chat/petugas', ['isi' => 'Halo petugas, mohon bantuannya.'])->assertRedirect();
        $this->actingAs($member)->get('/member/chat/petugas/polling')->assertStatus(200);
        $this->actingAs($member)->get('/member/profil')->assertStatus(200);

        // Halaman Petugas
        $this->actingAs($petugas)->get('/petugas/dashboard')->assertStatus(200);
        $this->actingAs($petugas)->get('/petugas/buku')->assertStatus(200);
        $this->actingAs($petugas)->get('/petugas/buku/create')->assertStatus(200);
        $this->actingAs($petugas)->get("/petugas/buku/{$buku->id}/edit")->assertStatus(200);
        $this->actingAs($petugas)->get('/petugas/kategori')->assertStatus(200);
        $this->actingAs($petugas)->get('/petugas/peminjaman')->assertStatus(200);
        $this->actingAs($petugas)->get('/petugas/pengembalian')->assertStatus(200);
        $this->actingAs($petugas)->get('/petugas/denda')->assertStatus(200);
        $this->actingAs($petugas)->get('/petugas/laporan')->assertStatus(200);
        $this->actingAs($petugas)->get('/petugas/laporan/pdf?jenis=stok')->assertStatus(200);
        $this->actingAs($petugas)->get('/petugas/statistik')->assertStatus(200);
        $this->actingAs($petugas)->get('/petugas/chat')->assertStatus(200);

        // Halaman Admin
        $this->actingAs($admin)->get('/admin/dashboard')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/member-pending')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/pengguna')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/pengguna/create')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/pengaturan')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/laporan?jenis=member')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/statistik')->assertStatus(200);
    }

    public function test_pengembalian_buku_hilang_mengurangi_stok_total_dan_menambah_100_poin_denda(): void
    {
        $petugas = User::factory()->petugas()->create();
        $member = User::factory()->create();
        $buku = Buku::factory()->create(['stok_total' => 5, 'stok_tersedia' => 5]);

        $this->actingAs($member)->post('/member/peminjaman', ['buku_id' => $buku->id]);
        $pinjam = Peminjaman::firstOrFail();

        $this->actingAs($petugas)->post("/petugas/peminjaman/{$pinjam->id}/setuju");
        $this->actingAs($petugas)->post("/petugas/peminjaman/{$pinjam->id}/serahkan");

        // Saat dipinjam: stok_total = 5, stok_tersedia = 4
        $this->assertSame(5, $buku->fresh()->stok_total);
        $this->assertSame(4, $buku->fresh()->stok_tersedia);

        // Konfirmasi buku hilang
        $this->actingAs($petugas)->post("/petugas/pengembalian/{$pinjam->id}/konfirmasi", [
            'kondisi_buku' => 'hilang',
            'catatan_petugas' => 'Buku hilang oleh peminjam.',
        ]);

        // Setelah hilang: stok_total berkurang menjadi 4, stok_tersedia tetap 4 (berkurang 1 dari awal 5), dan denda 100 poin
        $this->assertSame(4, $buku->fresh()->stok_total);
        $this->assertSame(4, $buku->fresh()->stok_tersedia);
        $this->assertSame(100, $member->fresh()->total_poin_denda);
    }
}
