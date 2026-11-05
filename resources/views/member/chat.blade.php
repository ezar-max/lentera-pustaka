<x-layouts.app title="Bantuan & Chat">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10"
         x-data="{
            tab: '{{ $tab }}',
            pesanAiInput: '',
            loadingAi: false,
            pesanPetugasInput: '',
            loadingPetugas: false,
            daftarPesanPetugas: @js($percakapanPetugas ? $percakapanPetugas->pesan->map(fn($p) => ['id' => $p->id, 'pengirim' => $p->pengirim, 'isi' => $p->isi, 'waktu' => $p->created_at?->format('H:i')]) : []),
            statusPercakapanPetugas: '{{ $percakapanPetugas?->status ?? '' }}',
            namaStaf: '{{ $percakapanPetugas?->staf?->nama_lengkap ?? 'Petugas Perpustakaan' }}',

            init() {
                this.scrollToBottom('chat-ai-box');
                this.scrollToBottom('chat-petugas-box');

                setInterval(() => {
                    if (this.tab === 'petugas' && {{ $percakapanPetugas ? 'true' : 'false' }}) {
                        this.pollPetugas();
                    }
                }, 4000);
            },

            scrollToBottom(id) {
                this.$nextTick(() => {
                    const el = document.getElementById(id);
                    if (el) el.scrollTop = el.scrollHeight;
                });
            },

            async pollPetugas() {
                try {
                    const res = await fetch('{{ route('member.chat.polling') }}', {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        const berubah = data.pesan.length !== this.daftarPesanPetugas.length;
                        this.daftarPesanPetugas = data.pesan;
                        this.statusPercakapanPetugas = data.status_percakapan;
                        this.namaStaf = data.nama_staf;
                        if (berubah) this.scrollToBottom('chat-petugas-box');
                    }
                } catch (e) {}
            }
         }">

        {{-- Header & Tombol Hubungi Petugas --}}
        <div class="pb-6 border-b border-sage/20 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-sage">Pusat Bantuan Anggota</p>
                <h1 class="mt-1 font-sans text-2xl sm:text-3xl font-semibold text-forest">Layanan Konsultasi & Chat</h1>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="tab = 'ai'; scrollToBottom('chat-ai-box')"
                        :class="tab === 'ai' ? 'bg-forest text-ivory font-semibold' : 'bg-ivory-paper text-charcoal border border-sage/30'"
                        class="px-4 py-2.5 rounded-card text-xs transition-colors">
                    Asisten AI (Gemini)
                </button>
                <button type="button" @click="tab = 'petugas'; scrollToBottom('chat-petugas-box')"
                        :class="tab === 'petugas' ? 'bg-forest text-ivory font-semibold' : 'bg-ivory-paper text-charcoal border border-sage/30'"
                        class="px-4 py-2.5 rounded-card text-xs transition-colors">
                    Chat Petugas / Admin
                    @if ($belumDibacaPetugas > 0)
                        <span class="ml-1 px-1.5 py-0.5 rounded-card bg-gold text-charcoal font-bold">{{ $belumDibacaPetugas }}</span>
                    @endif
                </button>
            </div>
        </div>

        {{-- TAB 1: ASISTEN AI --}}
        <div x-show="tab === 'ai'" class="mt-6 bg-ivory-paper border border-sage/25 rounded-card overflow-hidden">
            <div class="px-6 py-4 bg-ivory-dark/70 border-b border-sage/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-forest">Asisten Pustakawan Digital (Gemini AI)</h2>
                    <p class="text-xs text-sage">Menjawab pertanyaan seputar katalog buku, stok tersedia, aturan pinjam, dan status pinjaman Anda.</p>
                </div>

                <form action="{{ route('member.chat.hubungi-petugas') }}" method="POST">
                    @csrf
                    <x-button type="submit" variant="gold" size="sm">
                        Hubungi Petugas Manusia
                    </x-button>
                </form>
            </div>

            <div id="chat-ai-box" class="p-6 h-96 overflow-y-auto space-y-4">
                @foreach ($percakapanAi->pesan as $pesan)
                    <div class="flex {{ $pesan->pengirim === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-xl rounded-card px-4 py-3 text-sm leading-relaxed {{ $pesan->pengirim === 'user' ? 'bg-forest text-ivory' : 'bg-ivory border border-sage/25 text-charcoal' }}">
                            <p class="text-[11px] font-semibold mb-1 {{ $pesan->pengirim === 'user' ? 'text-gold' : 'text-forest' }}">
                                {{ $pesan->pengirim === 'user' ? 'Anda' : 'Asisten AI Lentera Pustaka' }}
                            </p>
                            <div class="whitespace-pre-line">{{ $pesan->isi }}</div>
                            <p class="text-[10px] mt-1.5 opacity-65 text-right">{{ $pesan->created_at?->format('H:i') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <form action="{{ route('member.chat.ai') }}" method="POST" class="p-4 border-t border-sage/20 bg-ivory/50 flex gap-3">
                @csrf
                <input
                    type="text"
                    name="isi"
                    required
                    placeholder="Tanyakan rekomendasi buku, stok tersedia, atau aturan sirkulasi..."
                    class="flex-1 rounded-card border border-sage/35 bg-ivory-paper px-4 py-2.5 text-sm text-charcoal focus:border-forest focus:outline-none"
                >
                <x-button type="submit" variant="primary">
                    Kirim ke AI
                </x-button>
            </form>
        </div>

        {{-- TAB 2: CHAT PETUGAS / ADMIN --}}
        <div x-show="tab === 'petugas'" class="mt-6 bg-ivory-paper border border-sage/25 rounded-card overflow-hidden">
            @if (! $percakapanPetugas)
                <div class="p-12 text-center">
                    <h2 class="font-sans text-xl font-semibold text-forest">Mulai Percakapan dengan Petugas</h2>
                    <p class="mt-2 text-sm text-sage max-w-md mx-auto">
                        Butuh bantuan langsung mengenai verifikasi peminjaman, pengambilan, atau pengembalian buku? Hubungi petugas kami.
                    </p>
                    <form action="{{ route('member.chat.hubungi-petugas') }}" method="POST" class="mt-6">
                        @csrf
                        <x-button type="submit" variant="primary" size="lg">
                            Hubungi Petugas Sekarang
                        </x-button>
                    </form>
                </div>
            @else
                <div class="px-6 py-4 bg-ivory-dark/70 border-b border-sage/20 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-forest">Percakapan Langsung dengan Staf Perpustakaan</h2>
                        <p class="text-xs text-sage">
                            Ditangani oleh: <strong class="text-charcoal" x-text="namaStaf"></strong>
                            · Pembaruan otomatis setiap 4 detik
                        </p>
                    </div>
                    <span class="px-3 py-1 rounded-card text-xs font-semibold"
                          :class="statusPercakapanPetugas === 'aktif' ? 'bg-status-disetujui text-status-disetujui-text' : 'bg-status-dibatalkan text-status-dibatalkan-text'"
                          x-text="statusPercakapanPetugas === 'aktif' ? 'Sesi Aktif' : 'Sesi Selesai'">
                    </span>
                </div>

                <div id="chat-petugas-box" class="p-6 h-96 overflow-y-auto space-y-4">
                    <template x-for="item in daftarPesanPetugas" :key="item.id">
                        <div class="flex" :class="item.pengirim === 'user' ? 'justify-end' : 'justify-start'">
                            <div class="max-w-xl rounded-card px-4 py-3 text-sm leading-relaxed"
                                 :class="item.pengirim === 'user' ? 'bg-forest text-ivory' : 'bg-ivory border border-sage/25 text-charcoal'">
                                <p class="text-[11px] font-semibold mb-1"
                                   :class="item.pengirim === 'user' ? 'text-gold' : 'text-forest'"
                                   x-text="item.pengirim === 'user' ? 'Anda' : namaStaf"></p>
                                <div class="whitespace-pre-line" x-text="item.isi"></div>
                                <p class="text-[10px] mt-1.5 opacity-65 text-right" x-text="item.waktu"></p>
                            </div>
                        </div>
                    </template>
                </div>

                <form action="{{ route('member.chat.petugas') }}" method="POST"
                      @submit.prevent="
                        if (!pesanPetugasInput.trim() || loadingPetugas) return;
                        loadingPetugas = true;
                        fetch('{{ route('member.chat.petugas') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                            },
                            body: JSON.stringify({ isi: pesanPetugasInput })
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.pesan) {
                                daftarPesanPetugas.push(data.pesan);
                                pesanPetugasInput = '';
                                statusPercakapanPetugas = 'aktif';
                                scrollToBottom('chat-petugas-box');
                            }
                        })
                        .finally(() => loadingPetugas = false);
                      "
                      class="p-4 border-t border-sage/20 bg-ivory/50 flex gap-3">
                    @csrf
                    <input
                        type="text"
                        name="isi"
                        x-model="pesanPetugasInput"
                        required
                        placeholder="Tulis pesan untuk petugas perpustakaan..."
                        class="flex-1 rounded-card border border-sage/35 bg-ivory-paper px-4 py-2.5 text-sm text-charcoal focus:border-forest focus:outline-none"
                    >
                    <x-button type="submit" variant="primary" x-bind:disabled="loadingPetugas">
                        Kirim Pesan
                    </x-button>
                </form>
            @endif
        </div>
    </div>
</x-layouts.app>

