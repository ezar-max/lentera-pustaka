<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' — Panel Lentera Pustaka' : 'Panel Staf — Lentera Pustaka' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ sidebarOpen: false }" class="min-h-screen bg-ivory text-charcoal antialiased selection:bg-forest selection:text-ivory">
    {{-- Data Flash untuk ditampilkan oleh Popup JS / Toast JS --}}
    <div id="lentera-flash-data"
         class="hidden"
         data-success="{{ session('success') }}"
         data-error="{{ session('error') }}"
         data-warning="{{ session('warning') }}"
         data-validation="{{ $errors->any() ? $errors->first() : '' }}">
    </div>

    <div class="flex min-h-screen">
        {{-- Sidebar Desktop --}}
        <div class="hidden lg:block">
            <x-sidebar />
        </div>

        {{-- Drawer Sidebar Mobile --}}
        <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-charcoal/50 lg:hidden" @click="sidebarOpen = false"></div>
        <div x-show="sidebarOpen" x-transition class="fixed inset-y-0 left-0 z-50 w-64 lg:hidden">
            <x-sidebar />
        </div>

        {{-- Area Konten Utama --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Header Bar Mobile & Judul Halaman --}}
            <header class="bg-ivory-paper border-b border-sage/20 px-4 sm:px-8 py-4 flex items-center justify-between sticky top-0 z-20">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" type="button" class="lg:hidden p-2 rounded-card border border-sage/30 text-charcoal hover:bg-ivory-dark">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-lg sm:text-xl font-semibold text-forest tracking-tight">
                            {{ $header ?? ($title ?? 'Dashboard') }}
                        </h1>
                        @if (isset($subheader))
                            <p class="text-xs text-sage mt-0.5">{{ $subheader }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    @if (auth()->user()->isAdmin())
                        <span class="hidden sm:inline-flex items-center px-3 py-1 rounded-card text-xs font-semibold bg-gold/20 text-charcoal border border-gold/40">
                            Administrator
                        </span>
                    @else
                        <span class="hidden sm:inline-flex items-center px-3 py-1 rounded-card text-xs font-semibold bg-forest/10 text-forest">
                            Petugas Sirkulasi
                        </span>
                    @endif
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-8 max-w-7xl w-full mx-auto">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>

