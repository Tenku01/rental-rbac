{{-- 🔹 Midtrans & Axios --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    axios.defaults.headers.common['X-CSRF-TOKEN'] = '{{ csrf_token() }}';

    // =========================================================================
    // 🔹 FUNGSI PEMBAYARAN DENDA (DIPANGGIL DARI VIEW PESANAN SAYA)
    // =========================================================================

    /**
     * Memanggil Controller untuk mendapatkan Snap Token dan memunculkan pop-up Midtrans.
     * @param {string} kodePengembalian
     */
    function callMidtransSnap(kodePengembalian) {
        // Memanggil route POST: /pengembalian/{kode_pengembalian}/snap-token
        axios.post(`/pengembalian/${kodePengembalian}/snap-token`)
        .then(res => {
            const data = res.data;
            if (data.snap_token) {
                snap.pay(data.snap_token, {
                    onSuccess: function(result){
                        // Midtrans akan memanggil Webhook setelah sukses
                        alert("Pembayaran berhasil! Menunggu konfirmasi lunas dari sistem.");
                        window.location.reload(); 
                    },
                    onPending: function(result){
                        alert("Pembayaran tertunda. Silakan selesaikan pembayaran di Midtrans.");
                        window.location.reload();
                    },
                    onError: function(result){
                        alert("Terjadi kesalahan pada pembayaran Midtrans.");
                        window.location.reload();
                    },
                    onClose: function(){
                        // Jika user menutup pop-up tanpa menyelesaikan pembayaran
                        // Panggil endpoint cancel untuk menghapus transaksi pending
                        axios.post(`/pengembalian/${kodePengembalian}/cancel-midtrans`)
                        .then(() => {
                            // Menampilkan pesan sesuai keinginan Anda
                            alert('anda membatalkan transaksi pembayaran denda anda, silahkan segera bayar denda anda');
                            window.location.reload();
                        })
                        .catch(err => {
                            console.error('Gagal membatalkan transaksi:', err);
                            // Tetap tampilkan pesan meskipun route bermasalah agar feedback user tetap ada
                            alert('anda membatalkan transaksi pembayaran denda anda, silahkan segera bayar denda anda');
                            window.location.reload();
                        });
                    }
                });
            } else {
                alert('Gagal membuat transaksi Midtrans: ' + (data.error ?? 'Unknown error'));
            }
        })
        .catch(err => {
            console.error(err.response?.data?.error || err);
            alert('Terjadi kesalahan saat menginisiasi Midtrans: ' + (err.response?.data?.error || 'Silakan coba lagi.'));
        });
    }

    /**
     * Mencatat pilihan pembayaran Tunai atau Transfer Manual ke Controller.
     * @param {string} kodePengembalian
     * @param {string} metode ('tunai' atau 'transfer')
     */
    function callManualPayment(kodePengembalian, metode) {
        // Memanggil route POST: /pengembalian/{kode_pengembalian}/select-manual-payment
        axios.post(`/pengembalian/${kodePengembalian}/select-manual-payment`, {
            metode_pembayaran: metode
        })
        .then(res => {
            alert('Pilihan pembayaran dicatat. Menunggu konfirmasi dari Resepsionis.');
            window.location.reload();
        })
        .catch(err => {
            console.error(err.response?.data?.error || err);
            alert('Gagal mencatat pilihan pembayaran: ' + (err.response?.data?.error || 'Silakan coba lagi.'));
        });
    }

    // =========================================================================
    // 🔹 FUNGSI LAMA (Bayar Sisa & DP)
    // =========================================================================

    // 🔹 Bayar Sisa
    function bayarSisa(peminjamanId) {
        axios.get('/peminjaman/' + peminjamanId + '/pay-sisa')
            .then(res => {
                if (res.data.snap_token) {
                    snap.pay(res.data.snap_token, {
                        onSuccess: () => window.location.reload(),
                        onPending: () => window.location.reload(),
                        onError: () => alert('Gagal membayar sisa')
                    });
                } else {
                    alert('Gagal membuat transaksi sisa: ' + (res.data.error ?? 'Unknown'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan, silakan coba lagi.');
            });
    }

    // 🔹 Bayar DP
    function bayarDP(peminjamanId) {
        axios.get('/peminjaman/' + peminjamanId + '/pay')
            .then(res => {
                if (res.data.snap_token) {
                    snap.pay(res.data.snap_token, {
                        onSuccess: () => window.location.reload(),
                        onPending: () => window.location.reload(),
                        onError: () => alert('Gagal membayar DP')
                    });
                } else {
                    alert('Gagal membuat transaksi DP: ' + (res.data.error ?? 'Unknown'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan, silakan coba lagi.');
            });
    }
    
    // =========================================================================
    // 🔹 FUNGSI LAMA (Modal Cek Kondisi & Pembatalan)
    // =========================================================================

    // 🔹 Modal Cek Kondisi Mobil (Logika Lama)
    function bukaModal(peminjamanId) {
        document.getElementById('modalCekKondisi').classList.remove('hidden');
        document.getElementById('peminjaman_id').value = peminjamanId;
    }

    function tutupModal() {
        document.getElementById('modalCekKondisi').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('formCekKondisi');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const peminjamanId = document.getElementById('peminjaman_id').value;
                const kondisi = document.getElementById('kondisi').value;

                // Route lama. Harap pastikan ini adalah route yang benar.
                axios.post('/pesanan/' + peminjamanId + '/cek-kondisi', { kondisi }) 
                    .then(res => {
                        if (res.data.success) {
                            alert('Kondisi mobil berhasil dikonfirmasi dan diperbarui!');
                            tutupModal(); 
                            window.location.reload(); 
                        } else {
                            alert('Gagal mengirim data kondisi mobil.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Terjadi kesalahan saat memproses data.');
                    });
            });
        }
    });

    // 🔹 Modal Pembatalan
    function bukaModalBatal(peminjamanId) {
        document.getElementById('modalPembatalan').classList.remove('hidden');
        document.getElementById('peminjaman_id_batal').value = peminjamanId;
        document.getElementById('alasan_batal').value = '';
    }

    function tutupModalBatal() {
        document.getElementById('modalPembatalan').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const formPembatalan = document.getElementById('formPembatalan');
        if (formPembatalan) {
            formPembatalan.addEventListener('submit', function(e) {
                e.preventDefault();
                const id = document.getElementById('peminjaman_id_batal').value;
                const alasan = document.getElementById('alasan_batal').value;

                // Route: /peminjaman/{id}/cancel
                axios.post(`/peminjaman/${id}/cancel`, { alasan }) 
                    .then(res => {
                        if (res.data?.success) {
                            alert('Pengajuan pembatalan dikirim. Menunggu persetujuan admin.');
                            tutupModalBatal();
                            window.location.reload();
                        } else {
                            alert('Gagal membatalkan pesanan: ' + (res.data?.error ?? 'Unknown'));
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        const msg = err.response?.data?.error ?? 'Terjadi kesalahan.';
                        alert(msg);
                    });
            });
        }
    });
</script>


{{-- Modal Cek Kondisi Mobil --}}
<div id="modalCekKondisi" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <h3 class="text-lg font-semibold mb-4 text-center">Cek Kondisi Mobil</h3>

        <form id="formCekKondisi">
            @csrf
            <input type="hidden" id="peminjaman_id">

            <label for="kondisi" class="block mb-2 text-sm font-medium text-gray-700">
                Deskripsikan kondisi mobil saat ini:
            </label>
            <textarea id="kondisi" name="kondisi" rows="4" 
                class="w-full border-gray-300 rounded-lg focus:ring focus:ring-blue-200 p-2 mb-4" 
                placeholder="Kosongkan apabila tidak ada tambahan"></textarea>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="tutupModal()" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 transition">
                    Kirim
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 🔹 Modal Pembatalan Pesanan --}}
<div id="modalPembatalan" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <h3 class="text-lg font-semibold mb-4 text-center">Batalkan Pesanan</h3>

        <form id="formPembatalan">
            @csrf
            <input type="hidden" id="peminjaman_id_batal">

            <label for="alasan_batal" class="block mb-2 text-sm font-medium text-gray-700">Alasan pembatalan (opsional):</label>
            <textarea id="alasan_batal" name="alasan" rows="4"
                class="w-full border-gray-300 rounded-lg focus:ring focus:ring-blue-200 p-2 mb-4"
                placeholder="Tuliskan alasan pembatalan..."></textarea>

            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded p-3 text-sm mb-4">
                <strong>Catatan:</strong> Pembatalan pada status <em>Belum Diambil</em> akan diproses <em>refund</em> (pending).
                Pembatalan pada status <em>Berlangsung</em> mungkin tidak mendapatkan refund sesuai kebijakan.
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="tutupModalBatal()" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700 transition">
                    Konfirmasi Pembatalan
                </button>
            </div>
        </form>
    </div>
</div>