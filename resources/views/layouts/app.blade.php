<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' — Lentera Pustaka' : 'Lentera Pustaka — Perpustakaan Digital' }}</title>

    {{-- Fonts: Fraunces (600, 700) & Plus Jakarta Sans (400, 500, 600, 700) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $useNavbar = ! request()->routeIs('member.*', 'petugas.*', 'admin.*');
@endphp
<body x-data="{
          desktopOpen: localStorage.getItem('lentera_sidebar') !== 'closed',
          mobileOpen: false,
          toggleSidebar() {
              if (window.innerWidth >= 1024) {
                  this.desktopOpen = !this.desktopOpen;
                  localStorage.setItem('lentera_sidebar', this.desktopOpen ? 'open' : 'closed');
              } else {
                  this.mobileOpen = !this.mobileOpen;
              }
          }
      }"
      class="min-h-screen flex flex-col bg-ivory text-charcoal antialiased selection:bg-forest selection:text-ivory">
    {{-- Data Flash untuk ditampilkan oleh Popup JS --}}
    <div id="lentera-flash-data"
         class="hidden"
         data-success="{{ session('success') }}"
         data-error="{{ session('error') }}"
         data-warning="{{ session('warning') }}"
         data-validation="{{ $errors->any() ? $errors->first() : '' }}">
    </div>

    @if ($useNavbar)
        {{-- Halaman Publik & Autentikasi (Beranda, Katalog Publik, Detail Buku Publik, Login, Register): Menggunakan Navbar Horizontal --}}
        <x-navbar />

        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="border-t border-sage/20 bg-ivory-dark/50 mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-sage">
                <div>
                    <span class="font-sans font-semibold text-forest text-sm">Lentera Pustaka</span>
                    <span class="mx-2">·</span>
                    <span>Sistem Informasi Sirkulasi & Koleksi Perpustakaan Digital</span>
                </div>
                <div>
                    &copy; {{ date('Y') }} Lentera Pustaka. Seluruh hak cipta dilindungi.
                </div>
            </div>
        </footer>
    @else
        {{-- Seluruh Halaman Selain Landing Page: Menggunakan Sidebar Collapsible Smooth --}}
        <div class="flex min-h-screen flex-1">
            {{-- Sidebar Desktop (Collapsible Smooth) --}}
            <div class="hidden lg:block shrink-0 sticky top-0 h-screen overflow-hidden transition-[width] duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]"
                 :class="desktopOpen ? 'w-64' : 'w-0'">
                <div class="w-64 h-full transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]"
                     :class="desktopOpen ? 'translate-x-0 opacity-100' : '-translate-x-8 opacity-0'">
                    <x-sidebar />
                </div>
            </div>

            {{-- Drawer Sidebar Mobile (Slide Smooth) --}}
            <div x-cloak
                 x-show="mobileOpen"
                 x-transition:enter="transition-opacity duration-300 ease-out"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity duration-200 ease-in"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-40 bg-charcoal/50 backdrop-blur-xs lg:hidden"
                 @click="mobileOpen = false">
            </div>
            <div x-cloak
                 x-show="mobileOpen"
                 x-transition:enter="transition-transform duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition-transform duration-250 ease-in"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="fixed inset-y-0 left-0 z-50 w-64 lg:hidden">
                <x-sidebar />
            </div>

            {{-- Area Konten Utama --}}
            <div class="flex-1 flex flex-col min-w-0 transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]">
                {{-- Top Header Bar --}}
                <header class="bg-ivory-paper/95 backdrop-blur-xs border-b border-sage/20 px-4 sm:px-8 py-4 flex items-center justify-between sticky top-0 z-20">
                    <div class="flex items-center gap-3.5">
                        <button @click="toggleSidebar()"
                                type="button"
                                title="Buka / Tutup Sidebar"
                                class="p-2 rounded-card border border-sage/25 bg-ivory text-forest hover:bg-forest hover:text-ivory hover:border-forest transition-all duration-200 cursor-pointer shrink-0">
                            <span class="sr-only">Buka atau tutup menu sidebar</span>
                            <svg class="w-5 h-5 transition-transform duration-300" :class="!desktopOpen ? 'lg:rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
                            </svg>
                        </button>
                        <div>
                            <h2 class="text-base sm:text-lg font-semibold text-forest tracking-tight">
                                {{ $header ?? ($title ?? 'Lentera Pustaka') }}
                            </h2>
                            @if (isset($subheader))
                                <p class="text-xs text-sage mt-0.5">{{ $subheader }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        @auth
                            @if (auth()->user()->isAdmin())
                                <span class="hidden sm:inline-flex items-center px-3 py-1 rounded-card text-xs font-semibold bg-gold/20 text-charcoal border border-gold/40">
                                    Administrator
                                </span>
                            @elseif (auth()->user()->isPetugas())
                                <span class="hidden sm:inline-flex items-center px-3 py-1 rounded-card text-xs font-semibold bg-forest/10 text-forest">
                                    Petugas Sirkulasi
                                </span>
                            @else
                                <span class="hidden sm:inline-flex items-center px-3 py-1 rounded-card text-xs font-semibold bg-forest/10 text-forest">
                                    Anggota: {{ auth()->user()->nama_lengkap }}
                                </span>
                            @endif
                        @else
                            <span class="hidden sm:inline-flex items-center px-3 py-1 rounded-card text-xs font-medium bg-ivory-dark text-sage border border-sage/20">
                                Pengunjung Publik
                            </span>
                        @endauth
                    </div>
                </header>

                <main class="flex-1 w-full">
                    {{ $slot }}
                </main>

                <footer class="border-t border-sage/20 bg-ivory-dark/40 mt-12">
                    <div class="max-w-7xl mx-auto px-4 sm:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-sage">
                        <div>
                            <span class="font-sans font-semibold text-forest">Lentera Pustaka</span>
                            <span class="mx-1.5">·</span>
                            <span>Perpustakaan Digital</span>
                        </div>
                        <div>
                            &copy; {{ date('Y') }} Lentera Pustaka. Seluruh hak cipta dilindungi.
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    @endif

    @stack('scripts')
</body>
</html>
