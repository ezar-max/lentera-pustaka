import Swal from 'sweetalert2';

const baseConfig = {
    position: 'center',
    backdrop: true,
    showConfirmButton: true,
    showCancelButton: false,
    allowOutsideClick: false,
    allowEscapeKey: false,
    customClass: {
        popup: 'lentera-swal-popup',
        title: 'lentera-swal-title',
        htmlContainer: 'lentera-swal-html',
        actions: 'lentera-swal-actions',
        confirmButton: 'lentera-swal-confirm',
        cancelButton: 'lentera-swal-cancel',
        input: 'lentera-swal-input',
        validationMessage: 'lentera-swal-validation',
    },
    buttonsStyling: false,
    reverseButtons: true,
    showClass: {
        popup: 'swal2-show',
    },
    hideClass: {
        popup: 'swal2-hide',
    },
};

export const LenteraPopup = {
    /**
     * Menampilkan notifikasi popup di tengah layar yang tidak auto-close dan wajib ditekan tombol 'Selesai'.
     */
    toast(tipe = 'success', pesan = '', judul = '') {
        const defaultJudul = {
            success: 'Berhasil',
            error: 'Terjadi Kesalahan',
            warning: 'Perhatian',
            info: 'Pemberitahuan',
        };

        const iconColorMap = {
            success: '#183B32',
            error: '#87332A',
            warning: '#D9A441',
            info: '#6B7C70',
        };

        return Swal.fire({
            ...baseConfig,
            toast: false,
            position: 'center',
            icon: tipe,
            iconColor: iconColorMap[tipe] || '#183B32',
            title: judul || defaultJudul[tipe] || 'Pemberitahuan',
            text: pesan,
            showConfirmButton: true,
            showCancelButton: false,
            confirmButtonText: 'Selesai',
            allowOutsideClick: false,
            allowEscapeKey: false,
        });
    },

    /**
     * Menampilkan popup informasi / peringatan / error di tengah layar yang wajib ditekan tombol 'Selesai'.
     */
    alert({ judul = 'Informasi', pesan = '', tipe = 'info', teksTombol = 'Selesai' }) {
        const iconColorMap = {
            success: '#183B32',
            error: '#87332A',
            warning: '#D9A441',
            info: '#6B7C70',
        };

        return Swal.fire({
            ...baseConfig,
            position: 'center',
            icon: tipe,
            iconColor: iconColorMap[tipe] || '#183B32',
            title: judul,
            text: pesan,
            showConfirmButton: true,
            showCancelButton: false,
            confirmButtonText: teksTombol || 'Selesai',
            allowOutsideClick: false,
            allowEscapeKey: false,
        });
    },

    /**
     * Menampilkan popup konfirmasi di tengah layar sebelum mengeksekusi aksi berisiko.
     */
    async confirm({
        judul = 'Konfirmasi Tindakan',
        pesan = 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
        teksKonfirmasi = 'Ya, Lanjutkan',
        teksBatal = 'Batal',
    }) {
        const result = await Swal.fire({
            ...baseConfig,
            position: 'center',
            icon: 'question',
            iconColor: '#D9A441',
            title: judul,
            text: pesan,
            showCancelButton: true,
            confirmButtonText: teksKonfirmasi,
            cancelButtonText: teksBatal,
            allowOutsideClick: false,
        });

        return Boolean(result.isConfirmed);
    },

    /**
     * Menampilkan popup dengan textarea alasan wajib diisi (untuk tolak peminjaman / tolak member).
     */
    async promptAlasan({
        judul = 'Alasan Penolakan',
        pesan = 'Tuliskan alasan penolakan secara jelas untuk diinformasikan kepada peminjam.',
        placeholder = 'Contoh: Eksemplar buku sedang dalam perawatan sampul...',
        teksKonfirmasi = 'Kirim Penolakan',
        teksBatal = 'Batal',
    }) {
        const result = await Swal.fire({
            ...baseConfig,
            position: 'center',
            title: judul,
            text: pesan,
            input: 'textarea',
            inputPlaceholder: placeholder,
            inputAttributes: {
                rows: 3,
                'aria-label': placeholder,
            },
            customClass: {
                ...baseConfig.customClass,
                input: 'lentera-swal-input swal2-textarea',
            },
            showCancelButton: true,
            confirmButtonText: teksKonfirmasi,
            cancelButtonText: teksBatal,
            allowOutsideClick: false,
            preConfirm: (value) => {
                const teks = (value || '').trim();
                if (!teks) {
                    Swal.showValidationMessage('Alasan wajib diisi sebelum mengirim keputusan.');
                    return false;
                }
                if (teks.length < 5) {
                    Swal.showValidationMessage('Alasan penolakan minimal terdiri dari 5 karakter.');
                    return false;
                }
                return teks;
            },
        });

        if (result.isConfirmed && result.value) {
            return result.value;
        }

        return null;
    },

    /**
     * Menampilkan popup form konfirmasi pengembalian buku fisik (pilih kondisi + catatan).
     */
    async promptKonfirmasiPengembalian({
        judulBuku = '',
        namaPeminjam = '',
        infoKeterlambatan = 'Tepat Waktu',
    }) {
        const { value: formValues, isConfirmed } = await Swal.fire({
            ...baseConfig,
            position: 'center',
            title: 'Konfirmasi Pengembalian Buku',
            customClass: {
                ...baseConfig.customClass,
                popup: 'lentera-swal-popup lentera-swal-popup-wide',
            },
            html: `
                <div class="text-left space-y-3 mt-2">
                    <div class="p-3.5 rounded-card bg-ivory-dark/70 border border-sage/20 text-xs space-y-1.5">
                        <div class="flex items-center justify-between"><span class="text-sage">Peminjam:</span> <strong class="text-charcoal">${namaPeminjam}</strong></div>
                        <div class="flex items-center justify-between"><span class="text-sage">Buku:</span> <strong class="text-charcoal">${judulBuku}</strong></div>
                        <div class="flex items-center justify-between"><span class="text-sage">Status Waktu:</span> <strong class="text-forest">${infoKeterlambatan}</strong></div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-charcoal mb-1.5">Kondisi Fisik Buku Saat Diterima <span class="text-status-ditolak-text">*</span></label>
                        <select id="swal-kondisi-buku" class="w-full rounded-card border border-sage/40 bg-white px-3.5 py-2.5 text-sm text-charcoal focus:border-forest focus:outline-none">
                            <option value="baik">Baik (Stok tersedia +1, tanpa denda kondisi)</option>
                            <option value="rusak">Rusak (Stok tersedia +1, denda rusak +25 poin)</option>
                            <option value="hilang">Hilang (Stok total & tersedia berkurang, denda +100 poin)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-charcoal mb-1.5">Catatan Pemeriksaan Petugas (Opsional)</label>
                        <textarea id="swal-catatan-petugas" rows="2" placeholder="Catatan kondisi buku atau penyelesaian..." class="w-full rounded-card border border-sage/40 bg-white px-3.5 py-2 text-sm text-charcoal focus:border-forest focus:outline-none"></textarea>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Simpan & Selesaikan',
            cancelButtonText: 'Batal',
            allowOutsideClick: false,
            preConfirm: () => {
                const kondisi = document.getElementById('swal-kondisi-buku')?.value;
                const catatan = document.getElementById('swal-catatan-petugas')?.value || '';
                if (!kondisi) {
                    Swal.showValidationMessage('Kondisi fisik buku wajib dipilih.');
                    return false;
                }
                return { kondisi_buku: kondisi, catatan_petugas: catatan.trim() };
            },
        });

        if (isConfirmed && formValues) {
            return formValues;
        }

        return null;
    },
};

/**
 * Pasang event listener delegasi untuk form yang memerlukan konfirmasi atau input alasan via atribut data-*.
 */
export function initGlobalPopupListeners() {
    document.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (form.dataset.confirmed === 'true') {
            return;
        }

        // 1. Form dengan popup konfirmasi (data-confirm)
        if (form.hasAttribute('data-confirm')) {
            event.preventDefault();
            const judul = form.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan';
            const pesan = form.getAttribute('data-confirm') || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
            const teksKonfirmasi = form.getAttribute('data-confirm-button') || 'Ya, Lanjutkan';

            const disetujui = await LenteraPopup.confirm({
                judul,
                pesan,
                teksKonfirmasi,
            });

            if (disetujui) {
                form.dataset.confirmed = 'true';
                form.submit();
            }
            return;
        }

        // 2. Form dengan popup alasan wajib (data-reason)
        if (form.hasAttribute('data-reason')) {
            event.preventDefault();
            const judul = form.getAttribute('data-reason-title') || 'Alasan Penolakan';
            const pesan = form.getAttribute('data-reason') || 'Tuliskan alasan penolakan secara jelas.';
            const placeholder = form.getAttribute('data-reason-placeholder') || 'Tuliskan alasan di sini...';
            const fieldName = form.getAttribute('data-reason-field') || 'alasan_penolakan';

            const alasan = await LenteraPopup.promptAlasan({
                judul,
                pesan,
                placeholder,
            });

            if (alasan) {
                let input = form.querySelector(`input[name="${fieldName}"]`);
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = fieldName;
                    form.appendChild(input);
                }
                input.value = alasan;
                form.dataset.confirmed = 'true';
                form.submit();
            }
            return;
        }

        // 3. Form konfirmasi pengembalian buku fisik (data-return-confirm)
        if (form.hasAttribute('data-return-confirm')) {
            event.preventDefault();
            const judulBuku = form.getAttribute('data-buku') || 'Buku';
            const namaPeminjam = form.getAttribute('data-peminjam') || 'Anggota';
            const infoKeterlambatan = form.getAttribute('data-keterlambatan') || 'Tepat Waktu';

            const hasil = await LenteraPopup.promptKonfirmasiPengembalian({
                judulBuku,
                namaPeminjam,
                infoKeterlambatan,
            });

            if (hasil) {
                let inputKondisi = form.querySelector('input[name="kondisi_buku"]');
                if (!inputKondisi) {
                    inputKondisi = document.createElement('input');
                    inputKondisi.type = 'hidden';
                    inputKondisi.name = 'kondisi_buku';
                    form.appendChild(inputKondisi);
                }
                inputKondisi.value = hasil.kondisi_buku;

                let inputCatatan = form.querySelector('input[name="catatan_petugas"]');
                if (!inputCatatan) {
                    inputCatatan = document.createElement('input');
                    inputCatatan.type = 'hidden';
                    inputCatatan.name = 'catatan_petugas';
                    form.appendChild(inputCatatan);
                }
                inputCatatan.value = hasil.catatan_petugas;

                form.dataset.confirmed = 'true';
                form.submit();
            }
        }
    });
}
