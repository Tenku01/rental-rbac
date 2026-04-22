<x-app-layout>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            @if(isset($payment) && $payment->peminjaman)
                <div id="invoice-area" class="bg-white overflow-hidden shadow-2xl sm:rounded-2xl border-t-8 border-cyan-600">
                    
                    <div class="bg-white px-8 py-8 border-b-2 border-dashed border-gray-200 flex flex-col sm:flex-row justify-between items-center sm:items-start gap-4">
                        <div class="text-center sm:text-left">
                           <img src="{{ asset('logoakarentcar.png') }}" alt="Logo" class="h-12 mb-3 mx-auto sm:mx-0">
<h2 class="text-4xl font-extrabold text-cyan-700 tracking-tight">INVOICE</h2>
                            <!-- 🔹 Diperbarui: midtrans_transaction_id -> id_transaksi_midtrans -->
                            <p class="text-gray-500 font-medium mt-1">#{{ $payment->id_transaksi_midtrans }}</p>
                            <p class="text-sm text-gray-400 mt-1">{{ \Carbon\Carbon::parse($payment->created_at)->translatedFormat('d F Y, H:i') }} WIB</p>
                        </div>
                        <div class="text-center sm:text-right">
                            <div class="inline-flex items-center justify-center px-4 py-2 bg-green-100 border border-green-300 rounded-full">
                                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="font-bold text-green-700 uppercase tracking-wider text-sm">Pembayaran Berhasil</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                            <div>
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Diterbitkan Kepada:</h3>
                                <p class="text-lg font-bold text-gray-800">{{ auth()->user()->name }}</p>
                                <p class="text-gray-600">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="md:text-right">
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Informasi Transaksi:</h3>
                                <p class="text-gray-600"><span class="font-medium text-gray-800">Tipe Transaksi:</span> 
                                    {{ $payment->tipe_transaksi === 'dp' ? 'Down Payment (DP)' : ($payment->tipe_transaksi === 'sisa' ? 'Pelunasan Sisa' : 'Pembayaran Lunas') }}
                                </p>
                                <p class="text-gray-600"><span class="font-medium text-gray-800">Metode:</span> {{ ucfirst($payment->peminjaman->metode_pembayaran) }}</p>
                                <p class="text-gray-600"><span class="font-medium text-gray-800">ID Booking:</span> #{{ $payment->peminjaman_id }}</p>
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-lg font-bold text-cyan-800 border-b border-gray-200 pb-2 mb-4">Detail Reservasi Kendaraan</h3>
                            <div class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-3">
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Mobil:</span>
                                            <span class="font-bold text-gray-800">{{ $payment->peminjaman->mobil->merek }} {{ $payment->peminjaman->mobil->tipe }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Plat Nomor:</span>
                                            <span class="font-semibold text-gray-800">{{ $payment->peminjaman->mobil_id }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Layanan:</span>
                                            <span class="font-semibold text-cyan-700 bg-cyan-100 px-2 py-0.5 rounded text-sm">
                                                <!-- 🔹 Diperbarui: add_on_sopir -> tambahan_sopir -->
                                                {{ $payment->peminjaman->tambahan_sopir ? 'Dengan Sopir' : 'Lepas Kunci' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Waktu Ambil:</span>
                                            <span class="font-semibold text-gray-800 text-right">
                                                {{ \Carbon\Carbon::parse($payment->peminjaman->tanggal_sewa)->translatedFormat('d M Y') }}<br>
                                                <span class="text-sm text-cyan-600">{{ $payment->peminjaman->jam_sewa }} WIB</span>
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Waktu Kembali:</span>
                                            <span class="font-semibold text-gray-800 text-right">
                                                {{ \Carbon\Carbon::parse($payment->peminjaman->tanggal_kembali)->translatedFormat('d M Y') }}<br>
                                                <span class="text-sm text-cyan-600">{{ $payment->peminjaman->jam_sewa }} WIB</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-cyan-800 border-b border-gray-200 pb-2 mb-4">Rincian Pembayaran</h3>
                            <div class="space-y-3 text-gray-700">
                                <div class="flex justify-between items-center px-2">
                                    <span>Total Harga Sewa Keseluruhan</span>
                                    <span class="font-semibold">Rp {{ number_format($payment->peminjaman->total_harga, 0, ',', '.') }}</span>
                                </div>
                                
                                @if($payment->peminjaman->sisa_bayar > 0)
                                <div class="flex justify-between items-center px-2">
                                    <span>Total Telah Dibayar (Termasuk transaksi ini)</span>
                                    <span class="font-semibold">Rp {{ number_format($payment->peminjaman->total_dibayarkan, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center px-2 text-red-600 bg-red-50 p-2 rounded">
                                    <span class="font-medium">Sisa Tagihan Belum Dibayar</span>
                                    <span class="font-bold">Rp {{ number_format($payment->peminjaman->sisa_bayar, 0, ',', '.') }}</span>
                                </div>
                                @endif

                                <div class="border-t-2 border-dashed border-gray-300 pt-4 mt-4 flex justify-between items-center px-2">
                                    <span class="text-xl font-bold text-gray-800">Nominal Transaksi Ini</span>
                                    <!-- 🔹 Diperbarui: amount -> jumlah -->
                                    <span class="text-3xl font-extrabold text-cyan-600">Rp {{ number_format($payment->jumlah, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-10 pt-6 border-t border-gray-200 text-center sm:text-left text-sm text-gray-500">
                            <p>Catatan: Simpan halaman ini atau cetak sebagai bukti pembayaran yang sah.</p>
                            <p class="mt-1">Terima kasih telah mempercayakan perjalanan Anda bersama kami!</p>
                        </div>
                    </div>
                </div>

                <div id="action-buttons" class="flex flex-wrap gap-4 justify-center mt-8 print:hidden">
                    <a href="{{ route('pesanan.saya') }}" class="w-full sm:w-auto text-center bg-white border-2 border-cyan-600 text-cyan-700 px-6 py-3 rounded-xl hover:bg-cyan-50 font-bold transition shadow-sm">
                        Pesanan Saya
                    </a>
                    <button onclick="downloadPDF()" class="w-full sm:w-auto text-center bg-green-600 text-white px-6 py-3 rounded-xl hover:bg-green-700 font-bold shadow-lg transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download PDF
                    </button>
                </div>

            @else
                <div class="flex flex-col items-center justify-center min-h-[60vh] text-center bg-white p-10 rounded-2xl shadow-xl border-t-8 border-cyan-500">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h2 class="text-3xl font-extrabold text-gray-800 mb-4">Pembayaran Diproses!</h2>
                    <p class="text-gray-500 mb-8 text-lg max-w-md">Terima kasih, transaksi kamu sedang kami sinkronisasi. Silakan cek menu riwayat pesanan.</p>
                    <a href="{{ route('mobils.index') }}" class="bg-cyan-600 text-white px-8 py-3 rounded-xl hover:bg-cyan-700 shadow-md font-bold transition">
                        Kembali ke Beranda
                    </a>
                </div>
            @endif

        </div>
    </div>

   @if(isset($payment) && $payment->peminjaman)
    <script>
        function downloadPDF() {
            // Target elemen yang ingin dijadikan PDF
            const element = document.getElementById('invoice-area');
            
            // Opsi konfigurasi resolusi PDF (SUDAH DIPERBAIKI)
            const opt = {
                margin:       [0.4, 0.3, 0.4, 0.3], // Margin: Atas, Kanan, Bawah, Kiri
                // 🔹 Diperbarui: midtrans_transaction_id -> id_transaksi_midtrans
                filename:     'Invoice_AKA_Rental_{{ $payment->id_transaksi_midtrans }}.pdf',
                image:        { type: 'jpeg', quality: 1 },
                html2canvas:  { 
                    scale: 2, 
                    useCORS: true, 
                    scrollY: 0, // 🔥 KUNCI 1: Memaksa render dari paling atas (mencegah kepotong saat layar ter-scroll)
                    windowWidth: document.documentElement.offsetWidth // 🔥 KUNCI 2: Memastikan lebar elemen terbaca utuh
                }, 
                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' },
                pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] } // 🔥 KUNCI 3: Mencegah teks terpotong di tengah jika struknya lebih dari 1 halaman A4
            };

            // Proses pembuatan PDF
            html2pdf().set(opt).from(element).save();
        }

        // TRIGGER OTOMATIS DOWNLOAD SAAT HALAMAN SELESAI DIMUAT
        window.addEventListener('load', function() {
            // Diberi delay 1.5 detik agar halaman render sempurna dulu
            setTimeout(function() {
                downloadPDF();
            }, 1500);
        });
    </script>
    @endif

    <style>
        /* Sembunyikan tombol-tombol saat menggunakan fitur Print biasa (Ctrl+P) */
        @media print {
            body * { visibility: hidden; }
            #invoice-area, #invoice-area * { visibility: visible; }
            #invoice-area { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none; border: none; }
            .print\:hidden { display: none !important; }
        }
    </style>
</x-app-layout>