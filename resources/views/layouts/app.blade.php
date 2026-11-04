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
<body class="min-h-screen flex flex-col bg-ivory text-charcoal antialiased selection:bg-forest selection:text-ivory">
    {{-- Data Flash untuk ditampilkan oleh Popup JS / Toast JS --}}
    <div id="lentera-flash-data"
         class="hidden"
         data-success="{{ session('success') }}"
         data-error="{{ session('error') }}"
         data-warning="{{ session('warning') }}"
         data-validation="{{ $errors->any() ? $errors->first() : '' }}">
    </div>

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

    @stack('scripts')
</body>
</html>

