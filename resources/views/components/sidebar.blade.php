@php
    $user = auth()->user();
@endphp

<aside class="w-64 bg-forest text-ivory shrink-0 flex flex-col justify-between min-h-screen h-full select-none border-r border-ivory/10 shadow-xl lg:shadow-none">
    <div class="p-5 sm:p-6 overflow-y-auto flex-1">
        @if ($user && ($user->isAdmin() || $user->isPetugas()))
            @php
                $prefix = $user->isAdmin() ? 'admin' : 'petugas';
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

            {{-- Header & Logo Staf --}}
            <div class="flex items-center justify-between gap-2 pb-5 border-b border-ivory/15">
                <a href="{{ route($prefix . '.dashboard') }}" class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-card bg-gold/15 border border-gold/40 flex items-center justify-center text-gold shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <div class="truncate">
                        <span class="font-sans text-base font-semibold tracking-tight text-ivory block leading-none truncate">Lentera Pustaka</span>
                        <span class="text-[10px] text-gold tracking-wider uppercase block mt-1 truncate">
                            Panel {{ $user->isAdmin() ? 'Administrator' : 'Petugas' }}
                        </span>
                    </div>
                </a>
                <button @click="toggleSidebar()" type="button" title="Tutup Sidebar" class="p-1.5 rounded-card text-ivory/60 hover:text-ivory hover:bg-ivory/10 transition-colors duration-200 shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </button>
            </div>

            {{-- Menu Sirkulasi & Pustaka --}}
            <div class="mt-6">
                <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-ivory/50 mb-2">Layanan & Sirkulasi</p>
                <nav class="space-y-1">
                    @foreach ($menus as $menu)
                        <a href="{{ route($menu['route']) }}"
                           class="flex items-center justify-between px-3.5 py-2.5 rounded-card text-sm font-medium transition-all duration-200 ease-out {{ $menu['active'] ? 'bg-ivory/15 text-gold font-semibold translate-x-0.5' : 'text-ivory/80 hover:text-ivory hover:bg-ivory/10 hover:translate-x-0.5' }}">
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
                           class="flex items-center justify-between px-3.5 py-2.5 rounded-card text-sm font-medium transition-all duration-200 ease-out {{ request()->routeIs('admin.member-pending*') ? 'bg-ivory/15 text-gold font-semibold translate-x-0.5' : 'text-ivory/80 hover:text-ivory hover:bg-ivory/10 hover:translate-x-0.5' }}">
                            <span>Persetujuan Member</span>
                            @if ($antreanMember > 0)
                                <span class="px-2 py-0.5 text-[11px] font-bold rounded-card bg-gold text-charcoal">
                                    {{ $antreanMember }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('admin.pengguna.index') }}"
                           class="flex items-center justify-between px-3.5 py-2.5 rounded-card text-sm font-medium transition-all duration-200 ease-out {{ request()->routeIs('admin.pengguna*') ? 'bg-ivory/15 text-gold font-semibold translate-x-0.5' : 'text-ivory/80 hover:text-ivory hover:bg-ivory/10 hover:translate-x-0.5' }}">
                            <span>Kelola Pengguna</span>
                        </a>
                        <a href="{{ route('admin.pengaturan.index') }}"
                           class="flex items-center justify-between px-3.5 py-2.5 rounded-card text-sm font-medium transition-all duration-200 ease-out {{ request()->routeIs('admin.pengaturan*') ? 'bg-ivory/15 text-gold font-semibold translate-x-0.5' : 'text-ivory/80 hover:text-ivory hover:bg-ivory/10 hover:translate-x-0.5' }}">
                            <span>Pengaturan Aturan</span>
                        </a>
                    </nav>
                </div>
            @endif

        @elseif ($user && $user->isPeminjam())
            @php
                $unreadChatCount = \App\Models\Percakapan::where('user_id', $user->id)
                    ->where('tipe', 'petugas')
                    ->withCount(['pesan as unread' => fn ($q) => $q->where('pengirim', 'staf')->where('dibaca', false)])
                    ->get()
                    ->sum('unread');

                $memberMenus = [
                    [
                        'label' => 'Dashboard',
                        'route' => 'member.dashboard',
                        'active' => request()->routeIs('member.dashboard'),
                        'badge' => 0,
                    ],
                    [
                        'label' => 'Katalog Buku',
                        'route' => 'member.katalog',
                        'active' => request()->routeIs('member.katalog*') || request()->routeIs('publik.katalog*') || request()->routeIs('publik.buku*'),
                        'badge' => 0,
                    ],
                    [
                        'label' => 'Peminjaman Saya',
                        'route' => 'member.peminjaman.index',
                        'active' => request()->routeIs('member.peminjaman*'),
                        'badge' => 0,
                    ],
                    [
                        'label' => 'Koleksi Pribadi',
                        'route' => 'member.koleksi.index',
                        'active' => request()->routeIs('member.koleksi*'),
                        'badge' => 0,
                    ],
                    [
                        'label' => 'Poin Denda',
                        'route' => 'member.denda.index',
                        'active' => request()->routeIs('member.denda*'),
                        'badge' => $user->total_poin_denda,
                    ],
                    [
                        'label' => 'Bantuan & Chat',
                        'route' => 'member.chat',
                        'active' => request()->routeIs('member.chat*'),
                        'badge' => $unreadChatCount,
                    ],
                ];
            @endphp

            {{-- Header & Logo Anggota --}}
            <div class="flex items-center justify-between gap-2 pb-5 border-b border-ivory/15">
                <a href="{{ route('member.dashboard') }}" class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-card bg-gold/15 border border-gold/40 flex items-center justify-center text-gold shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <div class="truncate">
                        <span class="font-sans text-base font-semibold tracking-tight text-ivory block leading-none truncate">Lentera Pustaka</span>
                        <span class="text-[10px] text-gold tracking-wider uppercase block mt-1 truncate">
                            Panel Anggota
                        </span>
                    </div>
                </a>
                <button @click="toggleSidebar()" type="button" title="Tutup Sidebar" class="p-1.5 rounded-card text-ivory/60 hover:text-ivory hover:bg-ivory/10 transition-colors duration-200 shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </button>
            </div>

            {{-- Menu Utama Anggota --}}
            <div class="mt-6">
                <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-ivory/50 mb-2">Menu Anggota</p>
                <nav class="space-y-1">
                    @foreach ($memberMenus as $menu)
                        <a href="{{ route($menu['route']) }}"
                           class="flex items-center justify-between px-3.5 py-2.5 rounded-card text-sm font-medium transition-all duration-200 ease-out {{ $menu['active'] ? 'bg-ivory/15 text-gold font-semibold translate-x-0.5' : 'text-ivory/80 hover:text-ivory hover:bg-ivory/10 hover:translate-x-0.5' }}">
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

            {{-- Pengaturan Akun Anggota --}}
            <div class="mt-7 pt-5 border-t border-ivory/15">
                <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-ivory/50 mb-2">Pengaturan Akun</p>
                <nav class="space-y-1">
                    <a href="{{ route('member.profil') }}"
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-card text-sm font-medium transition-all duration-200 ease-out {{ request()->routeIs('member.profil*') ? 'bg-ivory/15 text-gold font-semibold translate-x-0.5' : 'text-ivory/80 hover:text-ivory hover:bg-ivory/10 hover:translate-x-0.5' }}">
                        <span>Profil Saya</span>
                    </a>
                </nav>
            </div>

        @else
            {{-- Header & Logo Tamu / Guest --}}
            <div class="flex items-center justify-between gap-2 pb-5 border-b border-ivory/15">
                <a href="{{ route('beranda') }}" class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-card bg-gold/15 border border-gold/40 flex items-center justify-center text-gold shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <div class="truncate">
                        <span class="font-sans text-base font-semibold tracking-tight text-ivory block leading-none truncate">Lentera Pustaka</span>
                        <span class="text-[10px] text-gold tracking-wider uppercase block mt-1 truncate">
                            Perpustakaan Digital
                        </span>
                    </div>
                </a>
                <button @click="toggleSidebar()" type="button" title="Tutup Sidebar" class="p-1.5 rounded-card text-ivory/60 hover:text-ivory hover:bg-ivory/10 transition-colors duration-200 shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </button>
            </div>

            {{-- Navigasi Publik --}}
            <div class="mt-6">
                <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-ivory/50 mb-2">Eksplorasi Pustaka</p>
                <nav class="space-y-1">
                    <a href="{{ route('beranda') }}"
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-card text-sm font-medium transition-all duration-200 ease-out {{ request()->routeIs('beranda') ? 'bg-ivory/15 text-gold font-semibold translate-x-0.5' : 'text-ivory/80 hover:text-ivory hover:bg-ivory/10 hover:translate-x-0.5' }}">
                        <span>Beranda Utama</span>
                    </a>
                    <a href="{{ route('publik.katalog') }}"
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-card text-sm font-medium transition-all duration-200 ease-out {{ request()->routeIs('publik.katalog*') || request()->routeIs('publik.buku*') ? 'bg-ivory/15 text-gold font-semibold translate-x-0.5' : 'text-ivory/80 hover:text-ivory hover:bg-ivory/10 hover:translate-x-0.5' }}">
                        <span>Katalog Koleksi</span>
                    </a>
                </nav>
            </div>

            {{-- Menu Akses Akun --}}
            <div class="mt-7 pt-5 border-t border-ivory/15">
                <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-ivory/50 mb-2">Keanggotaan</p>
                <nav class="space-y-1">
                    <a href="{{ route('login') }}"
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-card text-sm font-medium transition-all duration-200 ease-out {{ request()->routeIs('login') ? 'bg-ivory/15 text-gold font-semibold translate-x-0.5' : 'text-ivory/80 hover:text-ivory hover:bg-ivory/10 hover:translate-x-0.5' }}">
                        <span>Masuk ke Sistem</span>
                    </a>
                    <a href="{{ route('register') }}"
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-card text-sm font-medium transition-all duration-200 ease-out {{ request()->routeIs('register') ? 'bg-ivory/15 text-gold font-semibold translate-x-0.5' : 'text-ivory/80 hover:text-ivory hover:bg-ivory/10 hover:translate-x-0.5' }}">
                        <span>Daftar Anggota Baru</span>
                    </a>
                </nav>
            </div>
        @endif
    </div>

    {{-- Bagian Bawah Sidebar: Info User / Tombol Auth --}}
    <div class="p-5 border-t border-ivory/15 bg-forest-dark/60 shrink-0">
        @if ($user)
            <div class="mb-3">
                <p class="text-sm font-semibold text-ivory truncate">{{ $user->nama_lengkap }}</p>
                <p class="text-xs text-ivory/65 truncate">{{ $user->email }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('beranda') }}" class="flex-1 text-center py-2 px-3 rounded-card text-xs font-medium border border-ivory/20 text-ivory/85 hover:bg-ivory/10 transition-colors">
                    Beranda
                </a>
                <form action="{{ route('logout') }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-2 px-3 rounded-card text-xs font-medium bg-ivory/15 text-ivory hover:bg-status-ditolak-text transition-colors">
                        Keluar
                    </button>
                </form>
            </div>
        @else
            <p class="text-xs text-ivory/70 leading-relaxed mb-3">
                Bergabung menjadi anggota untuk meminjam buku dan menyimpan koleksi pribadi.
            </p>
            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}" class="flex-1 text-center py-2 px-3 rounded-card text-xs font-medium border border-ivory/25 text-ivory hover:bg-ivory/10 transition-colors">
                    Masuk
                </a>
                <a href="{{ route('register') }}" class="flex-1 text-center py-2 px-3 rounded-card text-xs font-semibold bg-gold text-charcoal hover:bg-gold-hover transition-colors">
                    Daftar
                </a>
            </div>
        @endif
    </div>
</aside>
