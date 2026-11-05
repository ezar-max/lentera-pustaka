<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MemberPendingController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Member\ChatController as MemberChatController;
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\KatalogController as MemberKatalogController;
use App\Http\Controllers\Member\KoleksiController as MemberKoleksiController;
use App\Http\Controllers\Member\PeminjamanController as MemberPeminjamanController;
use App\Http\Controllers\Member\ProfilController as MemberProfilController;
use App\Http\Controllers\Petugas\BukuController as PetugasBukuController;
use App\Http\Controllers\Petugas\ChatInboxController as PetugasChatInboxController;
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboardController;
use App\Http\Controllers\Petugas\KategoriController as PetugasKategoriController;
use App\Http\Controllers\Petugas\LaporanController as PetugasLaporanController;
use App\Http\Controllers\Petugas\StatistikController as PetugasStatistikController;
use App\Http\Controllers\Petugas\VerifikasiPeminjamanController;
use App\Http\Controllers\Petugas\VerifikasiPengembalianController;
use App\Http\Controllers\Publik\BerandaController;
use App\Http\Controllers\Publik\KatalogController as PublikKatalogController;
use Illuminate\Support\Facades\Route;

// =========================================================
// 1. GROUP PUBLIK (Guest & Umum)
// =========================================================
Route::get('/', [BerandaController::class, 'index'])->name('beranda');
Route::get('/katalog', [PublikKatalogController::class, 'index'])->name('publik.katalog');
Route::get('/buku/{buku}', [PublikKatalogController::class, 'show'])->name('publik.buku.show');

// Autentikasi
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// =========================================================
// 2. GROUP PEMINJAM (MEMBER)
// =========================================================
Route::prefix('member')
    ->name('member.')
    ->middleware(['auth', 'active', 'role:peminjam'])
    ->group(function (): void {
        Route::get('/dashboard', [MemberDashboardController::class, 'index'])->name('dashboard');

        // Katalog & Detail (Member Login)
        Route::get('/katalog', [MemberKatalogController::class, 'index'])->name('katalog');
        Route::get('/katalog/{buku}', [MemberKatalogController::class, 'show'])->name('katalog.show');
        Route::post('/katalog/{buku}/ulasan', [MemberKatalogController::class, 'simpanUlasan'])->name('katalog.ulasan');

        // Peminjaman Saya & Detail Timeline
        Route::get('/peminjaman', [MemberPeminjamanController::class, 'index'])->name('peminjaman.index');
        Route::post('/peminjaman', [MemberPeminjamanController::class, 'store'])->name('peminjaman.store');
        Route::get('/peminjaman/{peminjaman}', [MemberPeminjamanController::class, 'show'])->name('peminjaman.show');
        Route::post('/peminjaman/{peminjaman}/batalkan', [MemberPeminjamanController::class, 'batalkan'])->name('peminjaman.batalkan');
        Route::post('/peminjaman/{peminjaman}/ajukan-kembali', [MemberPeminjamanController::class, 'ajukanKembali'])->name('peminjaman.ajukan-kembali');

        // Koleksi Pribadi
        Route::get('/koleksi', [MemberKoleksiController::class, 'index'])->name('koleksi.index');
        Route::post('/koleksi/{buku}/toggle', [MemberKoleksiController::class, 'toggle'])->name('koleksi.toggle');
        Route::delete('/koleksi/{koleksi}', [MemberKoleksiController::class, 'destroy'])->name('koleksi.destroy');

        // Poin Denda (Dialihkan karena sistem denda telah ditiadakan)
        Route::get('/denda', fn () => redirect()->route('member.dashboard'))->name('denda.index');

        // Chat AI & Petugas
        Route::get('/chat', [MemberChatController::class, 'index'])->name('chat');
        Route::post('/chat/ai', [MemberChatController::class, 'kirimAi'])->name('chat.ai');
        Route::post('/chat/hubungi-petugas', [MemberChatController::class, 'hubungiPetugas'])->name('chat.hubungi-petugas');
        Route::post('/chat/petugas', [MemberChatController::class, 'kirimPetugas'])->name('chat.petugas');
        Route::get('/chat/petugas/polling', [MemberChatController::class, 'pollingPesanPetugas'])->name('chat.polling');

        // Profil Member
        Route::get('/profil', [MemberProfilController::class, 'index'])->name('profil');
        Route::put('/profil', [MemberProfilController::class, 'update'])->name('profil.update');
        Route::put('/profil/password', [MemberProfilController::class, 'updatePassword'])->name('profil.password');
    });

// =========================================================
// 3. GROUP PETUGAS (Petugas & Admin boleh akses)
// =========================================================
$registerPetugasRoutes = function (): void {
    // Data Buku (CRUD)
    Route::resource('buku', PetugasBukuController::class)->except(['show']);

    // Master Kategori Buku
    Route::get('/kategori', [PetugasKategoriController::class, 'index'])->name('kategori.index');
    Route::post('/kategori', [PetugasKategoriController::class, 'store'])->name('kategori.store');
    Route::put('/kategori/{kategori}', [PetugasKategoriController::class, 'update'])->name('kategori.update');
    Route::delete('/kategori/{kategori}', [PetugasKategoriController::class, 'destroy'])->name('kategori.destroy');

    // Verifikasi Peminjaman
    Route::get('/peminjaman', [VerifikasiPeminjamanController::class, 'index'])->name('peminjaman.index');
    Route::post('/peminjaman/{peminjaman}/setuju', [VerifikasiPeminjamanController::class, 'setuju'])->name('peminjaman.setuju');
    Route::post('/peminjaman/{peminjaman}/tolak', [VerifikasiPeminjamanController::class, 'tolak'])->name('peminjaman.tolak');
    Route::post('/peminjaman/{peminjaman}/serahkan', [VerifikasiPeminjamanController::class, 'serahkan'])->name('peminjaman.serahkan');

    // Verifikasi Pengembalian
    Route::get('/pengembalian', [VerifikasiPengembalianController::class, 'index'])->name('pengembalian.index');
    Route::post('/pengembalian/{peminjaman}/setujui', [VerifikasiPengembalianController::class, 'setujui'])->name('pengembalian.setujui');
    Route::post('/pengembalian/{peminjaman}/konfirmasi', [VerifikasiPengembalianController::class, 'konfirmasi'])->name('pengembalian.konfirmasi');

    // Data Denda (Dialihkan karena sistem denda telah ditiadakan)
    Route::get('/denda', fn () => redirect()->route('petugas.dashboard'))->name('denda.index');

    // Laporan & Cetak PDF
    Route::get('/laporan', [PetugasLaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/pdf', [PetugasLaporanController::class, 'cetakPdf'])->name('laporan.pdf');

    // Statistik Grafik
    Route::get('/statistik', [PetugasStatistikController::class, 'index'])->name('statistik.index');

    // Chat Inbox
    Route::get('/chat', [PetugasChatInboxController::class, 'index'])->name('chat.index');
    Route::post('/chat/{percakapan}/balas', [PetugasChatInboxController::class, 'balas'])->name('chat.balas');
    Route::post('/chat/{percakapan}/ambil-alih', [PetugasChatInboxController::class, 'ambilAlih'])->name('chat.ambil-alih');
    Route::post('/chat/{percakapan}/tutup', [PetugasChatInboxController::class, 'tutup'])->name('chat.tutup');
    Route::get('/chat/{percakapan}/polling', [PetugasChatInboxController::class, 'pollingPesan'])->name('chat.polling');
};

Route::prefix('petugas')
    ->name('petugas.')
    ->middleware(['auth', 'active', 'role:petugas,admin'])
    ->group(function () use ($registerPetugasRoutes): void {
        Route::get('/dashboard', [PetugasDashboardController::class, 'index'])->name('dashboard');
        $registerPetugasRoutes();
    });

// =========================================================
// 4. GROUP ADMINISTRATOR (Semua fitur petugas di /admin/... + fitur khusus admin)
// =========================================================
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'active', 'role:admin'])
    ->group(function () use ($registerPetugasRoutes): void {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Persetujuan Member Baru
        Route::get('/member-pending', [MemberPendingController::class, 'index'])->name('member-pending.index');
        Route::post('/member-pending/{user}/setujui', [MemberPendingController::class, 'setujui'])->name('member-pending.setujui');
        Route::post('/member-pending/{user}/tolak', [MemberPendingController::class, 'tolak'])->name('member-pending.tolak');

        // Kelola Pengguna & Petugas
        Route::resource('pengguna', PenggunaController::class)->except(['show']);
        Route::post('/pengguna/{pengguna}/nonaktifkan', [PenggunaController::class, 'nonaktifkan'])->name('pengguna.nonaktifkan');
        Route::post('/pengguna/{pengguna}/aktifkan', [PenggunaController::class, 'aktifkan'])->name('pengguna.aktifkan');
        Route::post('/pengguna/{pengguna}/reset-password', [PenggunaController::class, 'resetPassword'])->name('pengguna.reset-password');

        // Pengaturan Parameter Aturan
        Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
        Route::put('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');

        // Mewarisi seluruh route petugas di bawah prefix /admin/...
        $registerPetugasRoutes();
    });
