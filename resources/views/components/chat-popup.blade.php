<div x-data="lenteraChatApp()"
     x-cloak
     x-on:open-lentera-chat.window="openChat($event.detail?.tab)"
     x-on:toggle-lentera-chat.window="toggleChat()"
     class="select-none">

    {{-- 1. TASKBAR PILL KETIKA DIMINIMIZE (Minimalis, Rapi & Elegan) --}}
    <div x-show="isOpen && isMinimized"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="fixed bottom-5 right-5 z-50 flex items-center shadow-2xl rounded-full bg-forest text-ivory border border-gold/40 py-1.5 px-3 gap-2.5 backdrop-blur-md hover:scale-105 transition-transform duration-200">

        <button type="button" @click="restore()" class="flex items-center gap-2 text-left cursor-pointer">
            <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
            <div class="leading-tight">
                <span class="text-xs font-semibold text-ivory block">Lentera Chat</span>
                <span class="text-[10px] text-gold uppercase tracking-wider block font-medium" x-text="tab === 'ai' ? 'Asisten AI' : 'Petugas'"></span>
            </div>
        </button>

        <div class="flex items-center gap-1 border-l border-ivory/20 pl-2">
            <button type="button"
                    @click="restore()"
                    title="Buka Chat"
                    class="text-xs font-semibold text-gold hover:text-gold-hover px-1.5 py-0.5 rounded cursor-pointer">
                Buka
            </button>
            <button type="button"
                    @click="close()"
                    title="Tutup Chat"
                    class="p-1 rounded-full text-ivory/60 hover:text-ivory hover:bg-status-ditolak-text transition-colors cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- 2. JENDELA POPUP CHAT (DRAGGABLE, PERSISTEN, MINIMALIS) --}}
    <div x-show="isOpen && !isMinimized"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-3 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-3 scale-95"
         :style="`left: ${posX}px; top: ${posY}px;`"
         class="fixed z-50 w-[94vw] sm:w-[420px] h-[560px] max-h-[88vh] flex flex-col bg-ivory-paper rounded-2xl shadow-2xl border border-sage/25 overflow-hidden ring-1 ring-black/10">

        {{-- BILAH JUDUL WINDOW (DRAG HANDLE - BERSIH TANPA ICON BERLEBIHAN) --}}
        <div @mousedown="startDrag($event)"
             @touchstart="startDrag($event)"
             :class="isDragging ? 'cursor-grabbing' : 'cursor-grab'"
             class="px-4 py-2.5 bg-forest text-ivory border-b border-ivory/15 flex items-center justify-between gap-3 shrink-0 select-none">

            {{-- Nama Aplikasi & Indikator --}}
            <div class="flex items-center gap-2 min-w-0">
                <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
                <span class="font-sans text-xs font-semibold tracking-tight text-ivory block truncate">Lentera Chat</span>
            </div>

            {{-- Segmented Tab Switcher (Teks Rapi Tanpa Emoji) --}}
            <div class="flex items-center bg-forest-dark/70 p-0.5 rounded-card border border-ivory/15 shrink-0"
                 @mousedown.stop @touchstart.stop>
                <button type="button"
                        @click="switchTab('ai')"
                        :class="tab === 'ai' ? 'bg-ivory text-forest font-semibold shadow-xs' : 'text-ivory/70 hover:text-ivory'"
                        class="px-3 py-1 rounded-card text-[11px] transition-all duration-150 cursor-pointer">
                    Asisten AI
                </button>
                <button type="button"
                        @click="switchTab('petugas')"
                        :class="tab === 'petugas' ? 'bg-ivory text-forest font-semibold shadow-xs' : 'text-ivory/70 hover:text-ivory'"
                        class="px-3 py-1 rounded-card text-[11px] transition-all duration-150 cursor-pointer">
                    Petugas
                </button>
            </div>

            {{-- Kontrol Window: Minimize & Tutup (Tanpa Icon Tambahan yang Mengganggu) --}}
            <div class="flex items-center gap-1 shrink-0" @mousedown.stop @touchstart.stop>
                {{-- Minimize Button --}}
                <button type="button"
                        @click="minimize()"
                        title="Minimize"
                        class="w-6 h-6 rounded-card flex items-center justify-center text-ivory/70 hover:text-ivory hover:bg-ivory/15 transition-colors cursor-pointer">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
                    </svg>
                </button>

                {{-- Close Button --}}
                <button type="button"
                        @click="close()"
                        title="Tutup"
                        class="w-6 h-6 rounded-card flex items-center justify-center text-ivory/70 hover:text-ivory hover:bg-status-ditolak-text transition-colors cursor-pointer">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- TAB 1: ASISTEN AI --}}
        <div x-show="tab === 'ai'" class="flex-1 flex flex-col min-h-0 bg-ivory">
            {{-- Sub-header AI --}}
            <div class="px-3.5 py-1.5 bg-ivory-dark/60 border-b border-sage/20 flex items-center justify-between text-xs shrink-0">
                <span class="text-charcoal font-medium text-[11px]">Asisten Pustakawan Digital</span>
                <button type="button"
                        @click="switchTab('petugas')"
                        class="text-[11px] font-semibold text-forest hover:underline cursor-pointer">
                    Chat Staf &rarr;
                </button>
            </div>

            {{-- Box Pesan AI --}}
            <div id="popup-chat-ai-box"
                 class="flex-1 p-3.5 overflow-y-auto space-y-3 min-h-0 [scrollbar-width:thin] [scrollbar-color:rgba(107,124,112,0.3)_transparent]">

                {{-- Sambutan jika pesan kosong --}}
                <template x-if="aiMessages.length === 0">
                    <div class="p-3.5 rounded-xl bg-ivory-paper border border-sage/25 text-xs text-charcoal space-y-2">
                        <p class="font-semibold text-forest">Halo! Ada yang bisa dibantu?</p>
                        <p class="text-sage text-[11px] leading-relaxed">
                            Tanyakan rekomendasi buku, ketersediaan stok, atau aturan peminjaman & pengembalian buku.
                        </p>
                        <div class="pt-1 flex flex-wrap gap-1.5">
                            <button type="button" @click="sendAiPreset('Apa saja aturan peminjaman buku?')"
                                    class="text-[11px] px-2.5 py-1 rounded-full bg-ivory text-forest border border-sage/30 hover:bg-forest hover:text-ivory transition-colors cursor-pointer">
                                Aturan Pinjam
                            </button>
                            <button type="button" @click="sendAiPreset('Rekomendasikan buku sastra populer')"
                                    class="text-[11px] px-2.5 py-1 rounded-full bg-ivory text-forest border border-sage/30 hover:bg-forest hover:text-ivory transition-colors cursor-pointer">
                                Rekomendasi Buku
                            </button>
                            <button type="button" @click="sendAiPreset('Bagaimana alur pengembalian buku?')"
                                    class="text-[11px] px-2.5 py-1 rounded-full bg-ivory text-forest border border-sage/30 hover:bg-forest hover:text-ivory transition-colors cursor-pointer">
                                Alur Pengembalian
                            </button>
                        </div>
                    </div>
                </template>

                {{-- Loop Pesan AI --}}
                <template x-for="item in aiMessages" :key="item.id">
                    <div class="flex" :class="item.pengirim === 'user' ? 'justify-end' : 'justify-start'">
                        <div class="max-w-[85%] rounded-2xl px-3.5 py-2.5 text-xs leading-relaxed shadow-xs"
                             :class="item.pengirim === 'user'
                                ? 'bg-forest text-ivory rounded-br-xs'
                                : 'bg-ivory-paper border border-sage/25 text-charcoal rounded-bl-xs'">
                            <p class="text-[10px] font-semibold mb-0.5"
                               :class="item.pengirim === 'user' ? 'text-gold' : 'text-forest'"
                               x-text="item.pengirim === 'user' ? 'Anda' : 'Asisten AI'"></p>
                            <div class="whitespace-pre-line text-[12px] leading-relaxed" x-text="formatCleanText(item.isi)"></div>
                            <p class="text-[9px] mt-1 text-right opacity-60" x-text="item.waktu"></p>
                        </div>
                    </div>
                </template>

                {{-- Indikator Mengetik --}}
                <div x-show="loadingAi" class="flex justify-start">
                    <div class="rounded-2xl px-3.5 py-2 bg-ivory-paper border border-sage/25 text-xs text-sage flex items-center gap-1.5 shadow-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-forest animate-pulse"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-forest animate-pulse delay-100"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-forest animate-pulse delay-200"></span>
                        <span class="text-[11px] font-medium ml-1 text-forest">Mengetik...</span>
                    </div>
                </div>
            </div>

            {{-- Form Input AI --}}
            <form @submit.prevent="sendAi()" class="p-2.5 bg-ivory-paper border-t border-sage/20 flex gap-2 shrink-0">
                <input type="text"
                       x-model="inputAi"
                       placeholder="Tanyakan buku, aturan, stok..."
                       :disabled="loadingAi"
                       class="flex-1 rounded-xl border border-sage/30 bg-ivory px-3 py-2 text-xs text-charcoal focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest">
                <button type="submit"
                        :disabled="!inputAi.trim() || loadingAi"
                        class="px-4 py-2 rounded-xl bg-forest text-gold font-semibold text-xs hover:bg-forest-hover disabled:opacity-50 transition-colors cursor-pointer shrink-0">
                    Kirim
                </button>
            </form>
        </div>

        {{-- TAB 2: CHAT PETUGAS / ADMIN --}}
        <div x-show="tab === 'petugas'" class="flex-1 flex flex-col min-h-0 bg-ivory">
            {{-- Sub-header Petugas --}}
            <div class="px-3.5 py-1.5 bg-ivory-dark/60 border-b border-sage/20 flex items-center justify-between text-xs shrink-0">
                <div class="truncate">
                    <span class="text-charcoal font-medium text-[11px]">
                        Staf: <strong class="text-forest" x-text="petugasName"></strong>
                    </span>
                </div>
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full shrink-0"
                      :class="petugasStatus === 'aktif' ? 'bg-status-disetujui text-status-disetujui-text' : 'bg-status-dibatalkan text-status-dibatalkan-text'"
                      x-text="petugasStatus === 'aktif' ? 'Aktif' : 'Selesai'"></span>
            </div>

            {{-- Box Pesan Petugas --}}
            <div id="popup-chat-petugas-box"
                 class="flex-1 p-3.5 overflow-y-auto space-y-3 min-h-0 [scrollbar-width:thin] [scrollbar-color:rgba(107,124,112,0.3)_transparent]">

                {{-- Jika Belum Ada Sesi --}}
                <template x-if="!petugasExists">
                    <div class="h-full flex flex-col items-center justify-center p-6 text-center space-y-2.5">
                        <h4 class="font-sans text-sm font-bold text-forest">Percakapan dengan Petugas</h4>
                        <p class="text-xs text-sage leading-relaxed max-w-xs">
                            Mulai sesi chat langsung dengan staf perpustakaan untuk bantuan sirkulasi atau verifikasi buku.
                        </p>
                        <button type="button"
                                @click="startPetugasChat()"
                                :disabled="startingPetugasChat"
                                class="px-4 py-2 rounded-xl bg-forest text-gold font-semibold text-xs hover:bg-forest-hover transition-colors shadow-xs cursor-pointer">
                            <span x-text="startingPetugasChat ? 'Membuka Sesi...' : 'Mulai Chat dengan Petugas'"></span>
                        </button>
                    </div>
                </template>

                {{-- Loop Pesan Petugas --}}
                <template x-for="item in petugasMessages" :key="item.id">
                    <div class="flex" :class="item.pengirim === 'user' ? 'justify-end' : 'justify-start'">
                        <div class="max-w-[85%] rounded-2xl px-3.5 py-2.5 text-xs leading-relaxed shadow-xs"
                             :class="item.pengirim === 'user'
                                ? 'bg-forest text-ivory rounded-br-xs'
                                : 'bg-ivory-paper border border-sage/25 text-charcoal rounded-bl-xs'">
                            <p class="text-[10px] font-semibold mb-0.5"
                               :class="item.pengirim === 'user' ? 'text-gold' : 'text-forest'"
                               x-text="item.pengirim === 'user' ? 'Anda' : petugasName"></p>
                            <div class="whitespace-pre-line text-[12px] leading-relaxed" x-text="formatCleanText(item.isi)"></div>
                            <p class="text-[9px] mt-1 text-right opacity-60" x-text="item.waktu"></p>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Form Input Petugas --}}
            <template x-if="petugasExists">
                <form @submit.prevent="sendPetugas()" class="p-2.5 bg-ivory-paper border-t border-sage/20 flex gap-2 shrink-0">
                    <input type="text"
                           x-model="inputPetugas"
                           placeholder="Tulis pesan untuk staf perpustakaan..."
                           :disabled="loadingPetugas"
                           class="flex-1 rounded-xl border border-sage/30 bg-ivory px-3 py-2 text-xs text-charcoal focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest">
                    <button type="submit"
                            :disabled="!inputPetugas.trim() || loadingPetugas"
                            class="px-4 py-2 rounded-xl bg-forest text-gold font-semibold text-xs hover:bg-forest-hover disabled:opacity-50 transition-colors cursor-pointer shrink-0">
                        Kirim
                    </button>
                </form>
            </template>
        </div>
    </div>
</div>

<script>
function lenteraChatApp() {
    return {
        isOpen: false,
        isMinimized: false,
        tab: 'ai',

        // Koordinat default (kanan bawah)
        posX: Math.max(16, window.innerWidth - 440),
        posY: Math.max(30, window.innerHeight - 600),

        // Dragging state
        isDragging: false,
        dragStartX: 0,
        dragStartY: 0,
        initialPosX: 0,
        initialPosY: 0,

        // Data Chat
        dataLoaded: false,
        aiMessages: [],
        petugasMessages: [],
        petugasExists: false,
        petugasStatus: 'aktif',
        petugasName: 'Petugas Perpustakaan',

        inputAi: '',
        loadingAi: false,
        inputPetugas: '',
        loadingPetugas: false,
        startingPetugasChat: false,
        pollTimer: null,

        init() {
            // 1. Pulihkan status persisten dari localStorage (agar tidak hilang saat pindah halaman)
            const savedOpen = localStorage.getItem('lentera_chat_open');
            const savedMinimized = localStorage.getItem('lentera_chat_minimized');
            const savedTab = localStorage.getItem('lentera_chat_tab');
            const savedX = parseInt(localStorage.getItem('lentera_chat_pos_x'), 10);
            const savedY = parseInt(localStorage.getItem('lentera_chat_pos_y'), 10);

            if (savedTab === 'ai' || savedTab === 'petugas') {
                this.tab = savedTab;
            }

            if (!isNaN(savedX) && !isNaN(savedY)) {
                const width = window.innerWidth < 640 ? window.innerWidth * 0.94 : 420;
                const maxX = window.innerWidth - width - 8;
                const maxY = window.innerHeight - 80;
                this.posX = Math.max(8, Math.min(maxX, savedX));
                this.posY = Math.max(10, Math.min(maxY, savedY));
            }

            // Jika sebelumnya dibuka di halaman sebelumnya, langsung tampilkan
            if (savedOpen === 'true') {
                this.isOpen = true;
                this.isMinimized = (savedMinimized === 'true');
                this.loadData();
                this.startPolling();
            } else if (new URLSearchParams(window.location.search).get('chat') === 'open') {
                this.openChat();
            }

            // Penyesuaian saat resize window
            window.addEventListener('resize', () => {
                const width = window.innerWidth < 640 ? window.innerWidth * 0.94 : 420;
                const maxX = window.innerWidth - width - 8;
                const maxY = window.innerHeight - 80;
                if (this.posX > maxX) this.posX = Math.max(8, maxX);
                if (this.posY > maxY) this.posY = Math.max(10, maxY);
            });
        },

        openChat(targetTab = null) {
            this.isOpen = true;
            this.isMinimized = false;
            localStorage.setItem('lentera_chat_open', 'true');
            localStorage.setItem('lentera_chat_minimized', 'false');

            if (targetTab) {
                this.tab = targetTab;
                localStorage.setItem('lentera_chat_tab', targetTab);
            }

            if (!this.dataLoaded) {
                this.loadData();
            } else {
                this.scrollToBottom();
            }

            this.startPolling();
        },

        toggleChat() {
            if (this.isOpen) {
                if (this.isMinimized) {
                    this.restore();
                } else {
                    this.minimize();
                }
            } else {
                this.openChat();
            }
        },

        minimize() {
            this.isMinimized = true;
            localStorage.setItem('lentera_chat_minimized', 'true');
        },

        restore() {
            this.isMinimized = false;
            localStorage.setItem('lentera_chat_minimized', 'false');
            this.scrollToBottom();
        },

        close() {
            this.isOpen = false;
            this.isMinimized = false;
            localStorage.setItem('lentera_chat_open', 'false');
            localStorage.setItem('lentera_chat_minimized', 'false');
            if (this.pollTimer) {
                clearInterval(this.pollTimer);
                this.pollTimer = null;
            }
        },

        switchTab(targetTab) {
            this.tab = targetTab;
            localStorage.setItem('lentera_chat_tab', targetTab);
            this.scrollToBottom();
        },

        async loadData() {
            try {
                const res = await fetch('{{ route('member.chat.data') }}', {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    this.aiMessages = data.ai.pesan || [];
                    this.petugasMessages = data.petugas.pesan || [];
                    this.petugasExists = data.petugas.exists;
                    this.petugasStatus = data.petugas.status;
                    this.petugasName = data.petugas.nama_staf;
                    this.dataLoaded = true;
                    this.scrollToBottom();
                }
            } catch (e) {
                console.error('Gagal memuat data chat', e);
            }
        },

        startPolling() {
            if (this.pollTimer) return;
            this.pollTimer = setInterval(() => {
                if (this.isOpen && this.petugasExists) {
                    this.pollPetugas();
                }
            }, 4000);
        },

        async pollPetugas() {
            try {
                const res = await fetch('{{ route('member.chat.polling') }}', {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.pesan && data.pesan.length !== this.petugasMessages.length) {
                        this.petugasMessages = data.pesan;
                        this.petugasStatus = data.status_percakapan;
                        this.petugasName = data.nama_staf;
                        if (this.tab === 'petugas') {
                            this.scrollToBottom('popup-chat-petugas-box');
                        }
                    }
                }
            } catch (e) {}
        },

        async sendAi() {
            const pesan = this.inputAi.trim();
            if (!pesan || this.loadingAi) return;

            // Optimistic bubble user
            this.aiMessages.push({
                id: 'tmp-' + Date.now(),
                pengirim: 'user',
                isi: pesan,
                waktu: this.getNow()
            });

            this.inputAi = '';
            this.loadingAi = true;
            this.scrollToBottom('popup-chat-ai-box');

            try {
                const res = await fetch('{{ route('member.chat.ai') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ isi: pesan })
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data.balasan) {
                        this.aiMessages.push(data.balasan);
                    }
                }
            } catch (e) {
                this.aiMessages.push({
                    id: 'err-' + Date.now(),
                    pengirim: 'ai',
                    isi: 'Terjadi kendala jaringan saat menghubungi Asisten AI. Silakan coba sesaat lagi.',
                    waktu: this.getNow()
                });
            } finally {
                this.loadingAi = false;
                this.scrollToBottom('popup-chat-ai-box');
            }
        },

        sendAiPreset(preset) {
            this.inputAi = preset;
            this.sendAi();
        },

        async sendPetugas() {
            const pesan = this.inputPetugas.trim();
            if (!pesan || this.loadingPetugas) return;

            this.loadingPetugas = true;
            try {
                const res = await fetch('{{ route('member.chat.petugas') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ isi: pesan })
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data.pesan) {
                        this.petugasMessages.push(data.pesan);
                        this.inputPetugas = '';
                        this.scrollToBottom('popup-chat-petugas-box');
                    }
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingPetugas = false;
            }
        },

        async startPetugasChat() {
            this.startingPetugasChat = true;
            try {
                const res = await fetch('{{ route('member.chat.hubungi-petugas') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                });

                if (res.ok) {
                    const data = await res.json();
                    this.petugasExists = true;
                    this.petugasStatus = data.status_percakapan;
                    this.petugasName = data.nama_staf;
                    this.petugasMessages = data.pesan || [];
                    this.scrollToBottom('popup-chat-petugas-box');
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.startingPetugasChat = false;
            }
        },

        scrollToBottom(elementId = null) {
            this.$nextTick(() => {
                const target = elementId || (this.tab === 'ai' ? 'popup-chat-ai-box' : 'popup-chat-petugas-box');
                const el = document.getElementById(target);
                if (el) {
                    el.scrollTop = el.scrollHeight;
                }
            });
        },

        getNow() {
            const d = new Date();
            return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
        },

        formatCleanText(text) {
            if (!text) return '';
            let clean = text
                .replaceAll('**', '')
                .replaceAll('__', '')
                .replaceAll('*', '')
                .replace(/^#+\s+/gm, '')
                .replace(/[\u{1F300}-\u{1F9FF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}\u{FE0F}]/gu, '')
                .replace(/[ \t]+/g, ' ');
            return clean.trim();
        },

        // ================= DRAG & DROP LOGIC =================
        startDrag(e) {
            const clientX = e.clientX || (e.touches && e.touches[0].clientX);
            const clientY = e.clientY || (e.touches && e.touches[0].clientY);
            if (clientX === undefined || clientY === undefined) return;

            this.isDragging = true;
            this.dragStartX = clientX;
            this.dragStartY = clientY;
            this.initialPosX = this.posX;
            this.initialPosY = this.posY;

            const onMouseMove = (moveEvent) => {
                if (!this.isDragging) return;
                const curX = moveEvent.clientX || (moveEvent.touches && moveEvent.touches[0].clientX);
                const curY = moveEvent.clientY || (moveEvent.touches && moveEvent.touches[0].clientY);
                if (curX === undefined || curY === undefined) return;

                const dx = curX - this.dragStartX;
                const dy = curY - this.dragStartY;

                const width = window.innerWidth < 640 ? window.innerWidth * 0.94 : 420;
                const maxX = window.innerWidth - width - 8;
                const maxY = window.innerHeight - 80;

                this.posX = Math.max(8, Math.min(maxX, this.initialPosX + dx));
                this.posY = Math.max(10, Math.min(maxY, this.initialPosY + dy));
            };

            const onMouseUp = () => {
                this.isDragging = false;
                localStorage.setItem('lentera_chat_pos_x', Math.round(this.posX));
                localStorage.setItem('lentera_chat_pos_y', Math.round(this.posY));

                window.removeEventListener('mousemove', onMouseMove);
                window.removeEventListener('mouseup', onMouseUp);
                window.removeEventListener('touchmove', onMouseMove);
                window.removeEventListener('touchend', onMouseUp);
            };

            window.addEventListener('mousemove', onMouseMove);
            window.addEventListener('mouseup', onMouseUp);
            window.addEventListener('touchmove', onMouseMove, { passive: false });
            window.addEventListener('touchend', onMouseUp);
        }
    };
}
</script>
