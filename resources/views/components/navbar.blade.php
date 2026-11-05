<header x-data="{ mobileOpen: false }" class="bg-forest text-ivory border-b border-sage/20 sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            {{-- Brand Lentera Pustaka --}}
            <a href="{{ route('beranda') }}" class="flex items-center gap-3 group">
                <div class="w-9 h-9 rounded-card bg-gold/15 border border-gold/40 flex items-center justify-center text-gold">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                </div>
                <div>
                    <span class="font-sans text-lg font-semibold tracking-tight text-ivory block leading-none">Lentera Pustaka</span>
                    <span class="text-[11px] text-ivory/65 tracking-wider uppercase block mt-0.5">Perpustakaan Digital</span>
                </div>
            </a>

            {{-- Menu Desktop Landing Page --}}
            <nav class="hidden md:flex items-center gap-1">
                <a href="{{ route('beranda') }}" class="px-3.5 py-2 rounded-card text-sm font-medium transition-colors {{ request()->routeIs('beranda') ? 'bg-ivory/15 text-gold' : 'text-ivory/85 hover:text-ivory hover:bg-ivory/10' }}">
                    Beranda
                </a>
                <a href="{{ auth()->check() && auth()->user()->isPeminjam() ? route('member.katalog') : route('publik.katalog') }}" class="px-3.5 py-2 rounded-card text-sm font-medium transition-colors {{ request()->routeIs('publik.katalog*') || request()->routeIs('publik.buku*') ? 'bg-ivory/15 text-gold' : 'text-ivory/85 hover:text-ivory hover:bg-ivory/10' }}">
                    Katalog Koleksi
                </a>
                @auth
                    <a href="{{ auth()->user()->isPeminjam() ? route('member.dashboard') : (auth()->user()->isAdmin() ? route('admin.dashboard') : route('petugas.dashboard')) }}" class="px-3.5 py-2 rounded-card text-sm font-medium text-ivory/85 hover:text-ivory hover:bg-ivory/10 transition-colors">
                        Dashboard {{ auth()->user()->isPeminjam() ? 'Anggota' : ucfirst(auth()->user()->role) }}
                    </a>
                @endauth
            </nav>

            {{-- Kanan: Profil / Tombol Auth --}}
            <div class="hidden md:flex items-center gap-3">
                @auth
                    <a href="{{ auth()->user()->isPeminjam() ? route('member.dashboard') : (auth()->user()->isAdmin() ? route('admin.dashboard') : route('petugas.dashboard')) }}" class="px-4 py-2 rounded-card text-xs font-semibold bg-gold text-charcoal hover:bg-gold-hover transition-colors">
                        Buka Panel ({{ auth()->user()->nama_lengkap }})
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3.5 py-2 rounded-card text-xs font-medium border border-ivory/25 text-ivory hover:bg-ivory/10 transition-colors">
                            Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 rounded-card text-sm font-medium text-ivory hover:bg-ivory/10 transition-colors">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="px-4 py-2 rounded-card text-sm font-semibold bg-gold text-charcoal hover:bg-gold-hover transition-colors">
                        Daftar Anggota
                    </a>
                @endauth
            </div>

            {{-- Tombol Hamburger Mobile --}}
            <div class="flex md:hidden">
                <button @click="mobileOpen = !mobileOpen" type="button" class="p-2 rounded-card text-ivory/85 hover:text-ivory hover:bg-ivory/10">
                    <span class="sr-only">Buka menu</span>
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Menu Mobile Landing Page --}}
    <div x-show="mobileOpen" x-transition class="md:hidden border-t border-ivory/15 bg-forest-dark px-4 pt-3 pb-5 space-y-1.5">
        <a href="{{ route('beranda') }}" class="block px-3.5 py-2.5 rounded-card text-sm font-medium {{ request()->routeIs('beranda') ? 'bg-ivory/15 text-gold' : 'text-ivory/85' }}">Beranda</a>
        <a href="{{ auth()->check() && auth()->user()->isPeminjam() ? route('member.katalog') : route('publik.katalog') }}" class="block px-3.5 py-2.5 rounded-card text-sm font-medium text-ivory/85">Katalog Koleksi</a>
        @auth
            <a href="{{ auth()->user()->isPeminjam() ? route('member.dashboard') : (auth()->user()->isAdmin() ? route('admin.dashboard') : route('petugas.dashboard')) }}" class="block px-3.5 py-2.5 rounded-card text-sm font-semibold text-gold">
                Buka Panel ({{ auth()->user()->nama_lengkap }})
            </a>
            <form action="{{ route('logout') }}" method="POST" class="pt-2 border-t border-ivory/10">
                @csrf
                <button type="submit" class="w-full text-left px-3.5 py-2.5 rounded-card text-sm font-medium text-ivory/80 hover:bg-ivory/10">
                    Keluar
                </button>
            </form>
        @else
            <div class="pt-3 flex gap-2">
                <a href="{{ route('login') }}" class="flex-1 text-center py-2.5 rounded-card text-sm font-medium border border-ivory/25 text-ivory">Masuk</a>
                <a href="{{ route('register') }}" class="flex-1 text-center py-2.5 rounded-card text-sm font-semibold bg-gold text-charcoal">Daftar</a>
            </div>
        @endauth
    </div>
</header>
