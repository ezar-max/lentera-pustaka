@php
    $user = auth()->user();
    $isAdminArea = $user->isAdmin() && request()->is('admin*');
    $prefix = $isAdminArea ? 'admin' : 'petugas';

    $antreanPinjam = \App\Models\Peminjaman::where('status', 'diajukan')->count();
    $antreanKembali = \App\Models\Peminjaman::whereIn('status', ['pengajuan_kembali', 'kembali_disetujui'])->count();
    $antreanMember = $user->isAdmin() ? \App\Models\User::where('role', 'peminjam')->where('status', 'pending')->count() : 0;
    $antreanChat = \App\Models\Pesan::whereHas('percakapan', fn ($q) => $q->where('tipe', 'petugas'))
        ->where('pengirim', 'user')
        ->where('dibaca', false)
        ->count();

    $menus = [
        [
            'label' => 'Dashboard',
            'route' => $prefix . '.dashboard',
            'active' => request()->routeIs($prefix . '.dashboard'),
            'badge' => 0,
        ],
        [
            'label' => 'Data Buku & Stok',
            'route' => $prefix . '.buku.index',
            'active' => request()->routeIs($prefix . '.buku*'),
            'badge' => 0,
        ],
        [
            'label' => 'Kategori Buku',
            'route' => $prefix . '.kategori.index',
            'active' => request()->routeIs($prefix . '.kategori*'),
            'badge' => 0,
        ],
        [
            'label' => 'Verifikasi Peminjaman',
            'route' => $prefix . '.peminjaman.index',
            'active' => request()->routeIs($prefix . '.peminjaman*'),
            'badge' => $antreanPinjam,
        ],
        [
            'label' => 'Verifikasi Pengembalian',
            'route' => $prefix . '.pengembalian.index',
            'active' => request()->routeIs($prefix . '.pengembalian*'),
            'badge' => $antreanKembali,
        ],
        [
            'label' => 'Data Poin Denda',
            'route' => $prefix . '.denda.index',
            'active' => request()->routeIs($prefix . '.denda*'),
            'badge' => 0,
        ],
        [
            'label' => 'Laporan & PDF',
            'route' => $prefix . '.laporan.index',
            'active' => request()->routeIs($prefix . '.laporan*'),
            'badge' => 0,
        ],
        [
            'label' => 'Statistik Grafik',
            'route' => $prefix . '.statistik.index',
            'active' => request()->routeIs($prefix . '.statistik*'),
            'badge' => 0,
        ],
        [
            'label' => 'Chat Inbox Member',
            'route' => $prefix . '.chat.index',
            'active' => request()->routeIs($prefix . '.chat*'),
            'badge' => $antreanChat,
        ],
    ];
@endphp

<aside class="w-64 bg-forest text-ivory shrink-0 flex flex-col justify-between min-h-screen">
    <div class="p-6">
        {{-- Logo --}}
        <a href="{{ route($prefix . '.dashboard') }}" class="flex items-center gap-3 pb-6 border-b border-ivory/15">
            <div class="w-9 h-9 rounded-card bg-gold/15 border border-gold/40 flex items-center justify-center text-gold shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
            </div>
            <div>
                <span class="font-sans text-lg font-semibold tracking-tight text-ivory block leading-none">Lentera Pustaka</span>
                <span class="text-[11px] text-gold tracking-wider uppercase block mt-1">
                    Panel {{ $user->isAdmin() ? 'Administrator' : 'Petugas' }}
                </span>
            </div>
        </a>

        {{-- Menu Sirkulasi & Pustaka --}}
        <div class="mt-6">
            <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-ivory/50 mb-2">Layanan & Sirkulasi</p>
            <nav class="space-y-1">
                @foreach ($menus as $menu)
                    <a href="{{ route($menu['route']) }}"
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-card text-sm font-medium transition-colors {{ $menu['active'] ? 'bg-ivory/15 text-gold font-semibold' : 'text-ivory/80 hover:text-ivory hover:bg-ivory/10' }}">
                        <span>{{ $menu['label'] }}</span>
                        @if ($menu['badge'] > 0)
                            <span class="px-2 py-0.5 text-[11px] font-bold rounded-card bg-gold text-charcoal">
                                {{ $menu['badge'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- Menu Khusus Administrator --}}
        @if ($user->isAdmin())
            <div class="mt-7 pt-5 border-t border-ivory/15">
                <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-ivory/50 mb-2">Administrasi Sistem</p>
                <nav class="space-y-1">
                    <a href="{{ route('admin.member-pending.index') }}"
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-card text-sm font-medium transition-colors {{ request()->routeIs('admin.member-pending*') ? 'bg-ivory/15 text-gold font-semibold' : 'text-ivory/80 hover:text-ivory hover:bg-ivory/10' }}">
                        <span>Persetujuan Member</span>
                        @if ($antreanMember > 0)
                            <span class="px-2 py-0.5 text-[11px] font-bold rounded-card bg-gold text-charcoal">
                                {{ $antreanMember }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('admin.pengguna.index') }}"
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-card text-sm font-medium transition-colors {{ request()->routeIs('admin.pengguna*') ? 'bg-ivory/15 text-gold font-semibold' : 'text-ivory/80 hover:text-ivory hover:bg-ivory/10' }}">
                        <span>Kelola Pengguna</span>
                    </a>
                    <a href="{{ route('admin.pengaturan.index') }}"
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-card text-sm font-medium transition-colors {{ request()->routeIs('admin.pengaturan*') ? 'bg-ivory/15 text-gold font-semibold' : 'text-ivory/80 hover:text-ivory hover:bg-ivory/10' }}">
                        <span>Pengaturan Aturan</span>
                    </a>
                </nav>
            </div>
        @endif
    </div>

    {{-- Profil Staf & Logout --}}
    <div class="p-5 border-t border-ivory/15 bg-forest-dark/60">
        <div class="mb-3">
            <p class="text-sm font-semibold text-ivory truncate">{{ $user->nama_lengkap }}</p>
            <p class="text-xs text-ivory/65 truncate">{{ $user->email }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('beranda') }}" class="flex-1 text-center py-2 px-3 rounded-card text-xs font-medium border border-ivory/20 text-ivory/85 hover:bg-ivory/10 transition-colors">
                Lihat Web
            </a>
            <form action="{{ route('logout') }}" method="POST" class="flex-1">
                @csrf
                <button type="submit" class="w-full py-2 px-3 rounded-card text-xs font-medium bg-ivory/15 text-ivory hover:bg-status-ditolak-text transition-colors">
                    Keluar
                </button>
            </form>
        </div>
    </div>
</aside>

