<x-layouts.dashboard title="Persetujuan Member" header="Persetujuan Registrasi Anggota Baru" subheader="Tinjau data calon anggota baru. Setujui agar akun aktif, atau tolak dengan memberikan alasan.">
    {{-- Tab Status Member --}}
    <div class="flex flex-wrap items-center gap-2 pb-6 border-b border-sage/20">
        <a href="{{ route('admin.member-pending.index', ['tab' => 'pending']) }}"
           class="px-4 py-2 rounded-card text-xs font-semibold transition-colors {{ $tab === 'pending' ? 'bg-forest text-ivory' : 'bg-ivory-paper text-charcoal border border-sage/30' }}">
            Menunggu Persetujuan ({{ $counts['pending'] }})
        </a>
        <a href="{{ route('admin.member-pending.index', ['tab' => 'ditolak']) }}"
           class="px-4 py-2 rounded-card text-xs font-semibold transition-colors {{ $tab === 'ditolak' ? 'bg-forest text-ivory' : 'bg-ivory-paper text-charcoal border border-sage/30' }}">
            Ditolak ({{ $counts['ditolak'] }})
        </a>
        <a href="{{ route('admin.member-pending.index', ['tab' => 'aktif']) }}"
           class="px-4 py-2 rounded-card text-xs font-semibold transition-colors {{ $tab === 'aktif' ? 'bg-forest text-ivory' : 'bg-ivory-paper text-charcoal border border-sage/30' }}">
            Anggota Aktif ({{ $counts['aktif'] }})
        </a>
    </div>

    <div class="mt-6">
        @if ($memberList->isEmpty())
            <x-empty-state judul="Tidak Ada Data Anggota pada Tab Ini" deskripsi="Belum ada akun anggota yang berada dalam status ini." />
        @else
            <x-table>
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-3.5">Nama Lengkap & Username</th>
                        <th class="px-4 py-3.5">Email & No. HP</th>
                        <th class="px-4 py-3.5">Alamat Domisili</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Keputusan Admin</th>
                    </tr>
                </x-slot:head>

                @foreach ($memberList as $member)
                    <tr>
                        <td class="px-4 py-4">
                            <p class="font-semibold text-charcoal">{{ $member->nama_lengkap }}</p>
                            <p class="text-xs text-sage">{{ '@' . $member->username }} · Daftar: {{ $member->created_at?->format('d M Y') }}</p>
                        </td>
                        <td class="px-4 py-4 text-xs">
                            <p class="text-charcoal">{{ $member->email }}</p>
                            <p class="text-sage">{{ $member->no_hp }}</p>
                        </td>
                        <td class="px-4 py-4 text-xs text-charcoal max-w-xs">
                            {{ $member->alamat }}
                        </td>
                        <td class="px-4 py-4">
                            <x-badge-status :status="$member->status" />
                            @if ($member->alasan_penolakan)
                                <p class="mt-1 text-[11px] text-status-ditolak-text max-w-xs">{{ $member->alasan_penolakan }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-2">
                                @if ($member->status !== 'aktif')
                                    <form action="{{ route('admin.member-pending.setujui', $member) }}" method="POST"
                                          data-confirm="Setujui dan aktifkan akun keanggotaan {{ $member->nama_lengkap }}?"
                                          data-confirm-title="Setujui Pendaftaran Member"
                                          data-confirm-button="Ya, Setujui & Aktifkan">
                                        @csrf
                                        <x-button type="submit" variant="primary" size="sm">
                                            Setujui
                                        </x-button>
                                    </form>
                                @endif

                                @if ($member->status !== 'ditolak')
                                    <form action="{{ route('admin.member-pending.tolak', $member) }}" method="POST"
                                          data-reason="Tuliskan alasan penolakan untuk ditampilkan saat {{ $member->nama_lengkap }} mencoba masuk."
                                          data-reason-title="Tolak Pendaftaran Member"
                                          data-reason-placeholder="Contoh: Data alamat atau nomor telepon belum lengkap...">
                                        @csrf
                                        <x-button type="submit" variant="danger" size="sm">
                                            Tolak
                                        </x-button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <div class="mt-6">
                <x-pagination :paginator="$memberList" />
            </div>
        @endif
    </div>
</x-layouts.dashboard>

