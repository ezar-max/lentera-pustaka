<x-layouts.dashboard title="Chat Inbox Member" header="Kotak Masuk Percakapan Anggota" subheader="Balas pertanyaan anggota secara langsung. Percakapan dengan pesan belum dibaca ditampilkan paling atas.">
    @php
        $prefix = auth()->user()->isAdmin() && request()->is('admin*') ? 'admin' : 'petugas';
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6"
         x-data="{
            pesanInput: '',
            loading: false,
            daftarPesan: @js($percakapanAktif ? $percakapanAktif->pesan->map(fn($p) => ['id' => $p->id, 'pengirim' => $p->pengirim, 'isi' => $p->isi, 'waktu' => $p->created_at?->format('H:i')]) : []),
            statusSesi: '{{ $percakapanAktif?->status ?? '' }}',

            init() {
                this.scrollToBottom();
                @if ($percakapanAktif)
                setInterval(() => {
                    this.pollPesan();
                }, 4000);
                @endif
            },

            scrollToBottom() {
                this.$nextTick(() => {
                    const box = document.getElementById('staf-chat-box');
                    if (box) box.scrollTop = box.scrollHeight;
                });
            },

            async pollPesan() {
                @if ($percakapanAktif)
                try {
                    const res = await fetch('{{ route($prefix . '.chat.polling', $percakapanAktif) }}', {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        const berubah = data.pesan.length !== this.daftarPesan.length;
                        this.daftarPesan = data.pesan;
                        this.statusSesi = data.status_percakapan;
                        if (berubah) this.scrollToBottom();
                    }
                } catch (e) {}
                @endif
            }
         }">

        {{-- Kolom Kiri: Daftar Percakapan Masuk --}}
        <div class="lg:col-span-4 bg-ivory-paper border border-sage/25 rounded-card overflow-hidden">
            <div class="px-4 py-3.5 bg-ivory-dark/70 border-b border-sage/20">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-forest">
                    Daftar Percakapan ({{ $daftarPercakapan->count() }})
                </h2>
            </div>

            @if ($daftarPercakapan->isEmpty())
                <div class="p-8 text-center text-xs text-sage">
                    Belum ada percakapan masuk dari anggota.
                </div>
            @else
                <div class="divide-y divide-sage/15 max-h-[540px] overflow-y-auto">
                    @foreach ($daftarPercakapan as $item)
                        <a href="{{ route($prefix . '.chat.index', ['percakapan_id' => $item->id]) }}"
                           class="block p-4 transition-colors {{ $percakapanAktif && $percakapanAktif->id === $item->id ? 'bg-forest/10' : 'hover:bg-ivory' }}">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-semibold text-sm text-charcoal truncate">{{ $item->user->nama_lengkap }}</span>
                                @if ($item->belum_dibaca_count > 0)
                                    <span class="px-2 py-0.5 rounded-card text-[10px] font-bold bg-gold text-charcoal">
                                        {{ $item->belum_dibaca_count }} baru
                                    </span>
                                @else
                                    <x-badge-status :status="$item->status" />
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-sage line-clamp-1">
                                {{ $item->pesanTerakhir?->isi ?? 'Belum ada pesan' }}
                            </p>
                            <p class="mt-1 text-[10px] text-sage/80">
                                Staf: {{ $item->staf?->nama_lengkap ?? 'Belum diambil alih' }}
                            </p>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Kolom Kanan: Ruang Chat Aktif --}}
        <div class="lg:col-span-8 bg-ivory-paper border border-sage/25 rounded-card overflow-hidden flex flex-col">
            @if (! $percakapanAktif)
                <div class="p-12 text-center my-auto">
                    <p class="text-sm text-sage">Pilih salah satu percakapan di sebelah kiri untuk membaca dan membalas pesan anggota.</p>
                </div>
            @else
                <div class="px-6 py-4 bg-ivory-dark/70 border-b border-sage/20 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold text-forest">{{ $percakapanAktif->user->nama_lengkap }} ({{ '@' . $percakapanAktif->user->username }})</h2>
                        <p class="text-xs text-sage">
                            Ditangani oleh: <strong>{{ $percakapanAktif->staf?->nama_lengkap ?? 'Belum ada' }}</strong>
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        @if ($percakapanAktif->staf_id !== auth()->id())
                            <form action="{{ route($prefix . '.chat.ambil-alih', $percakapanAktif) }}" method="POST">
                                @csrf
                                <x-button type="submit" variant="secondary" size="sm">
                                    Ambil Alih Percakapan
                                </x-button>
                            </form>
                        @endif

                        @if ($percakapanAktif->status === 'aktif')
                            <form action="{{ route($prefix . '.chat.tutup', $percakapanAktif) }}" method="POST"
                                  data-confirm="Tutup dan tandai selesai sesi percakapan dengan {{ $percakapanAktif->user->nama_lengkap }}?"
                                  data-confirm-title="Tutup Percakapan"
                                  data-confirm-button="Ya, Tutup Sesi">
                                @csrf
                                <x-button type="submit" variant="danger" size="sm">
                                    Tutup Percakapan
                                </x-button>
                            </form>
                        @endif
                    </div>
                </div>

                <div id="staf-chat-box" class="p-6 h-96 overflow-y-auto space-y-4 flex-1">
                    <template x-for="p in daftarPesan" :key="p.id">
                        <div class="flex" :class="p.pengirim === 'staf' ? 'justify-end' : 'justify-start'">
                            <div class="max-w-xl rounded-card px-4 py-3 text-sm leading-relaxed"
                                 :class="p.pengirim === 'staf' ? 'bg-forest text-ivory' : 'bg-ivory border border-sage/25 text-charcoal'">
                                <p class="text-[11px] font-semibold mb-1"
                                   :class="p.pengirim === 'staf' ? 'text-gold' : 'text-forest'"
                                   x-text="p.pengirim === 'staf' ? 'Staf Perpustakaan' : '{{ addslashes($percakapanAktif->user->nama_lengkap) }}'"></p>
                                <div class="whitespace-pre-line" x-text="p.isi"></div>
                                <p class="text-[10px] mt-1.5 opacity-65 text-right" x-text="p.waktu"></p>
                            </div>
                        </div>
                    </template>
                </div>

                <form action="{{ route($prefix . '.chat.balas', $percakapanAktif) }}" method="POST"
                      @submit.prevent="
                        if (!pesanInput.trim() || loading) return;
                        loading = true;
                        fetch('{{ route($prefix . '.chat.balas', $percakapanAktif) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                            },
                            body: JSON.stringify({ isi: pesanInput })
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.pesan) {
                                daftarPesan.push(data.pesan);
                                pesanInput = '';
                                scrollToBottom();
                            }
                        })
                        .finally(() => loading = false);
                      "
                      class="p-4 border-t border-sage/20 bg-ivory/50 flex gap-3">
                    @csrf
                    <input
                        type="text"
                        name="isi"
                        x-model="pesanInput"
                        required
                        placeholder="Ketik balasan untuk {{ $percakapanAktif->user->nama_lengkap }}..."
                        class="flex-1 rounded-card border border-sage/35 bg-ivory-paper px-4 py-2.5 text-sm text-charcoal focus:border-forest focus:outline-none"
                    >
                    <x-button type="submit" variant="primary" x-bind:disabled="loading">
                        Kirim Balasan
                    </x-button>
                </form>
            @endif
        </div>
    </div>
</x-layouts.dashboard>

