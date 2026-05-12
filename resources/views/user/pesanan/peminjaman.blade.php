<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Form Peminjaman Mobil') }}
        </h2>
    </x-slot>
<script type="text/javascript" src="https://app.midtrans.com/snap/snap.js"
        data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    <!-- Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css">

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div id="booking-root" class="bg-white p-8 sm:p-10 shadow-2xl rounded-3xl overflow-hidden transition duration-500 hover:shadow-cyan-400/50 text-gray-900"
                x-data="{
                    tanggalSewa: '',
                    jamSewa: '{{ now()->format('H:i') }}',
                    tanggalKembali: '',
                
                    // STATE UNTUK SOPIR
                    sopirAvailable: true, 
                    sisaSopir: 0,
                    isCheckingDriver: false,
                    addOnSopir: 0, // 0 = tidak, 1 = ya
                    hargaSopirPerHari: 150000, // Disesuaikan dengan controller (150.000)
                
                    // HARGA & PEMBAYARAN
                    hargaMobil: {{ $mobil->harga }},
                    tipePembayaran: 'dp',
                    metodePembayaran: 'transfer', // Default rencana pelunasan
                    dpManual: {{ $mobil->harga * 0.5 }}, // Default 50%
                    dpError: '',
                
                    // HELPER FUNCTIONS
                    toIsoDate(dateStr) {
                        if (!dateStr) return null;
                        const parts = dateStr.split('-');
                        if (parts.length !== 3) return null;
                        return `${parts[2]}-${parts[1]}-${parts[0]}`;
                    },
                
                    durasiHari() {
                        if (!this.tanggalSewa || !this.tanggalKembali) return 0;
                        const start = new Date(this.toIsoDate(this.tanggalSewa) + 'T' + this.jamSewa);
                        const end = new Date(this.toIsoDate(this.tanggalKembali) + 'T' + this.jamSewa);
                        if (isNaN(start.getTime()) || isNaN(end.getTime())) return 0;
                        
                        // Hitung selisih waktu dalam milidetik
                        const diffTime = end - start;
                        
                        // Jika waktu kembali <= waktu sewa (tidak valid untuk durasi sewa)
                        if (diffTime <= 0) return 0;

                        // Konversi ke jam
                        const diffHours = diffTime / (1000 * 60 * 60);
                        
                        // Pembulatan ke atas (per hari)
                        return Math.ceil(diffHours / 24);
                    },
                
                    totalHarga() {
                        const durasi = this.durasiHari();
                        if (durasi <= 0) return 0;
                        const hargaDasar = durasi * this.hargaMobil;
                        const biayaSopir = (this.addOnSopir == 1) ? durasi * this.hargaSopirPerHari : 0;
                        return hargaDasar + biayaSopir;
                    },
                
                    // ✅ FUNGSI UTAMA CEK SOPIR
                    checkDriver() {
                        this.sopirAvailable = true; 
                        
                        // Jika belum ada tanggalSewa, jangan check
                        if (!this.tanggalSewa) {
                            return;
                        }

                        // Jika tanggalKembali belum ada, gunakan tanggalSewa + 1 hari untuk preview
                        let tanggalKembaliForCheck = this.tanggalKembali;
                        if (!tanggalKembaliForCheck) {
                            const parts = this.tanggalSewa.split('-');
                            const date = new Date(parts[2], parts[1] - 1, parts[0]);
                            date.setDate(date.getDate() + 1);
                            tanggalKembaliForCheck = 
                                String(date.getDate()).padStart(2, '0') + '-' +
                                String(date.getMonth() + 1).padStart(2, '0') + '-' +
                                date.getFullYear();
                        }
                
                        this.isCheckingDriver = true;
                        
                        const postData = {
                            _token: '{{ csrf_token() }}',
                            tanggal_sewa: this.tanggalSewa,
                            tanggal_kembali: tanggalKembaliForCheck
                        };
                        
                        console.log('  Checking Driver:', postData);
                
                        axios.post('{{ route('check.driver') }}', postData)
                            .then(response => {
                                console.log('  Driver Check Response:', response.data);
                                
                                this.sopirAvailable = response.data.available;
                                this.sisaSopir = response.data.sisa || response.data.sisa_sopir || 0;
                                
                                console.log('   sopirAvailable:', this.sopirAvailable, 'sisaSopir:', this.sisaSopir);
                
                                // 🔥 LOGIKA BARU: Jika Tidak Available / 0
                                if (!this.sopirAvailable || this.sisaSopir === 0) {
                                    this.sopirAvailable = false; // Paksa false jika sisa 0
                                    this.addOnSopir = 0;         // Otomatis pindah ke Lepas Kunci
                                }
                            })
                            .catch(error => {
                                console.error('❌ Driver Check Error:', error);
                                this.sopirAvailable = false;
                                this.addOnSopir = 0;
                            })
                            .finally(() => {
                                this.isCheckingDriver = false;
                            });
                    },
                
                    validateDP() {
                        if (this.tipePembayaran === 'lunas') {
                            this.dpError = '';
                            return;
                        }
                
                        this.dpManual = Number(this.dpManual);
                        const total = this.totalHarga();
                        
                        const minDp = 1000; // Ubah minimal DP sesuai kebutuhan (contoh 50.000)
                        
                        if (this.dpManual < minDp) {
                             this.dpError = 'Minimal DP adalah ' + this.formatRupiah(minDp);
                        } else if (this.dpManual > total) {
                            this.dpError = 'DP tidak boleh melebihi Total Harga (' + this.formatRupiah(total) + ')';
                        } else {
                            this.dpError = '';
                        }
                    },
                
                    sisaBayar() {
                        if (this.tipePembayaran === 'lunas') return 0;
                        const sisa = this.totalHarga() - this.dpManual;
                        return sisa < 0 ? 0 : sisa;
                    },
                
                    updateDP() {
                        if (this.tipePembayaran === 'lunas') {
                            this.dpError = '';
                            this.metodePembayaran = 'transfer'; // Paksa transfer jika dibayar lunas
                        } else {
                            this.validateDP();
                        }
                    },
                
                    formatRupiah(number) {
                        return 'Rp ' + (Number(number) || 0).toLocaleString('id-ID');
                    }
                }" x-effect="updateDP()">
                
                <h1 class="text-3xl font-extrabold text-center text-cyan-700 mb-8 border-b-2 border-cyan-100 pb-4">
                    FORM PENYEWAAN MOBIL
                </h1>

                <form id="peminjaman-form" method="POST" action="{{ route('peminjaman.store') }}"
                    enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    <input type="hidden" name="mobil_id" value="{{ $mobil->id }}">
                    <!-- Input hidden untuk x-model agar terkirim di form submit -->
                    <input type="hidden" name="tambahan_sopir" x-model="addOnSopir"> 
                    <input type="hidden" name="tambahan_sopir" x-bind:value="addOnSopir">
                    
                    <input type="hidden" name="tanggal_kembali_field" x-bind:value="tanggalKembali">
                    <input type="hidden" name="tipe_pembayaran" x-bind:value="tipePembayaran">

                    <!-- Detail Mobil & Gambar -->
                    <div class="bg-cyan-50 p-6 rounded-xl shadow-inner border border-cyan-200">
                        <div class="flex flex-col sm:flex-row items-center gap-6">
                            <div class="w-full sm:w-1/3 flex-shrink-0">
                                <img src="{{ asset('storage/' . $mobil->foto) }}"
                                    onerror="this.onerror=null;this.src='https://placehold.co/256x160/22C55E/FFFFFF?text=CAR';"
                                    alt="Mobil"
                                    class="w-full h-40 object-cover rounded-xl shadow-md transition duration-300 hover:scale-[1.02]">
                            </div>
                            <div class="w-full sm:w-2/3 text-left">
                                <x-input-label value="Mobil yang Dipilih" class="text-gray-500" />
                                <p class="text-2xl font-bold text-cyan-800 mt-1">{{ $mobil->merek }}
                                    {{ $mobil->tipe }}</p>
                                <x-input-label value="Harga Sewa / Hari" class="mt-3 text-gray-500" />
                                <p class="text-xl font-extrabold text-green-600">Rp
                                    {{ number_format($mobil->harga, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pilihan Tanggal & Jam (GRID) -->
                    <div class="space-y-6 pt-4 border-t border-gray-100">
                        <h2 class="text-xl font-semibold text-gray-700">Periode Sewa</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <div>
                                <x-input-label for="tanggal_sewa" :value="__('Tanggal Sewa')" class="text-cyan-700 font-medium" />
                                <x-text-input id="tanggal_sewa" name="tanggal_sewa" type="text"
                                    placeholder="Pilih tanggal sewa"
                                    class="mt-1 block w-full border-cyan-300 focus:border-cyan-500 focus:ring-cyan-500 rounded-lg cursor-pointer"
                                    autocomplete="off" x-model="tanggalSewa" readonly required />
                            </div>
                            <div>
                                <x-input-label for="jam_sewa" :value="__('Jam Sewa (WIB)')" class="text-cyan-700 font-medium" />
                                <x-text-input id="jam_sewa" name="jam_sewa" type="time"
                                    class="mt-1 block w-full border-cyan-300 focus:border-cyan-500 focus:ring-cyan-500 rounded-lg cursor-pointer"
                                    x-model="jamSewa" required />
                            </div>
                            <div class="sm:col-span-1">
                                <x-input-label for="tanggal_kembali" :value="__('Tanggal Kembali')"
                                    class="text-cyan-700 font-medium" />
                                <x-text-input id="tanggal_kembali" name="tanggal_kembali" type="text"
                                    placeholder="Pilih tanggal kembali"
                                    class="mt-1 block w-full border-cyan-300 focus:border-cyan-500 focus:ring-cyan-500 rounded-lg cursor-pointer"
                                    autocomplete="off" x-model="tanggalKembali" readonly required />

                            </div>
                        </div>
                        <!-- Info Deadline -->
                        <p id="deadline-info"
                            class="mt-2 text-sm text-cyan-600 italic p-3 bg-cyan-50 border-l-4 border-cyan-400 rounded">
                        </p>
                    </div>

                    <!-- --- LAYANAN PENGEMUDI (MODIFIED) --- -->
               <div class="pt-4 border-t border-gray-100" x-show="tanggalSewa && tanggalKembali && durasiHari() > 0" x-transition>
                        
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Layanan Pengemudi</h3>
                            
                            <!-- Badge Status -->
                            <div class="flex items-center gap-2">
                                <template x-if="isCheckingDriver">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-700 animate-pulse flex items-center gap-1">
                                        <svg class="animate-spin h-3 w-3 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        Mengecek...
                                    </span>
                                </template>
                                    <span 
                                    x-show="!isCheckingDriver && sopirAvailable"
                                    class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200 flex items-center gap-1"
                                    >
                                        <span x-text="sisaSopir" x-cloak></span> Tersedia
                                    </span>
                                <template x-if="!isCheckingDriver && !sopirAvailable">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        Penuh
                                    </span>
                                </template>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            
                            <!-- Opsi 1: Lepas Kunci -->
                            <label class="relative flex items-center justify-between p-4 border rounded-xl cursor-pointer hover:border-blue-500 transition-all duration-200 group bg-white shadow-sm"
                                :class="addOnSopir == 0 ? 'border-blue-500 ring-1 ring-blue-500 bg-blue-50/50' : 'border-gray-200'">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 text-gray-500 group-hover:bg-blue-100 group-hover:text-blue-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </div>
                                    <div>
                                        <span class="block text-sm font-medium text-gray-900">Lepas Kunci</span>
                                        <span class="block text-xs text-gray-500">Setir sendiri</span>
                                    </div>
                                </div>
                                <input type="radio" name="tambahan_sopir_radio" value="0" x-model="addOnSopir" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            </label>

                            <!-- Opsi 2: Dengan Sopir (UPDATED) -->
                            <label class="relative flex items-center justify-between p-4 border rounded-xl transition-all duration-200 group bg-white shadow-sm"
                                :class="{
                                    'border-blue-500 ring-1 ring-blue-500 bg-blue-50/50': addOnSopir == 1,
                                    'border-gray-200 hover:border-blue-500 cursor-pointer': sopirAvailable,
                                    'border-gray-100 bg-gray-100 opacity-50 cursor-not-allowed': !sopirAvailable
                                }">
                                
                                <!-- Overlay Transparan untuk Mencegah Klik -->
                                <div x-show="!sopirAvailable" class="absolute inset-0 z-10 cursor-not-allowed" @click.prevent></div>

                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full"
                                        :class="sopirAvailable ? 'bg-blue-100 text-blue-600' : 'bg-gray-300 text-gray-500'">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    </div>
                                    <div>
                                        <span class="block text-sm font-medium text-gray-900">Dengan Sopir</span>
                                        
                                        <!-- Teks Berubah Tergantung Status -->
                                        <template x-if="sopirAvailable">
                                            <span class="block text-xs text-blue-600 font-medium">+ {{ number_format(150000, 0, ',', '.') }} /hari</span>
                                        </template>
                                        <template x-if="!sopirAvailable">
                                            <span class="block text-xs text-red-600 font-bold mt-1">HABIS / TIDAK TERSEDIA</span>
                                        </template>
                                    </div>
                                </div>
                                
                                <input type="radio" name="tambahan_sopir_radio" value="1" x-model="addOnSopir" 
                                    :disabled="!sopirAvailable"
                                    class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 disabled:opacity-50">
                            </label>
                        </div>

                        <!-- Pesan Alert Jika Penuh -->
                        <div x-show="!isCheckingDriver && !sopirAvailable" x-transition 
                            class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg flex items-start gap-2">
                            <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <div class="text-sm text-red-700">
                                <strong>Mohon Maaf, Sopir Penuh.</strong><br>
                                Semua sopir kami sedang bertugas atau tidak tersedia pada tanggal yang Anda pilih. Silakan pilih opsi Lepas Kunci atau ganti tanggal.
                            </div>
                        </div>
                    </div>

                    <!-- Jenis Pembayaran -->
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <x-input-label value="Jenis Pembayaran" class="text-cyan-700 font-medium mb-3" />
                        <div class="flex gap-6">
                            <label
                                class="inline-flex items-center space-x-2 p-3 border rounded-lg transition duration-150 cursor-pointer"
                                :class="{ 'bg-cyan-100 border-cyan-500 shadow-md': tipePembayaran === 'dp', 'bg-gray-50 border-gray-300 hover:border-cyan-400': tipePembayaran !== 'dp' }">
                                <input type="radio" value="dp" x-model="tipePembayaran"
                                    name="radio-tipe-bayar" class="text-cyan-600 focus:ring-cyan-500">
                                <span>Bayar DP</span>
                            </label>
                            <label
                                class="inline-flex items-center space-x-2 p-3 border rounded-lg transition duration-150 cursor-pointer"
                                :class="{ 'bg-cyan-100 border-cyan-500 shadow-md': tipePembayaran === 'lunas', 'bg-gray-50 border-gray-300 hover:border-cyan-400': tipePembayaran !== 'lunas' }">
                                <input type="radio" value="lunas" x-model="tipePembayaran"
                                    name="radio-tipe-bayar" class="text-cyan-600 focus:ring-cyan-500">
                                <span>Bayar Lunas</span>
                            </label>
                        </div>
                    </div>

                    <!-- Input DP (Conditional) -->
                    <div x-show="tipePembayaran === 'dp'" x-cloak x-transition.opacity
                        class="mt-4 p-4 border border-yellow-200 bg-yellow-50 rounded-lg">
                        <label for="dpManual" class="block text-sm font-medium text-gray-700">Nominal DP <span
                                class="text-xs text-gray-500">(Minimal Rp 50.000)</span></label>
                        <div class="relative mt-1">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">Rp</span>
                            <input type="number" id="dpManual" name="dp" x-model.number="dpManual"
                                @input="validateDP" :required="tipePembayaran === 'dp'"
                                :disabled="tipePembayaran === 'lunas'"
                                class="pl-10 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-cyan-500 focus:border-cyan-500 disabled:opacity-50"
                                min="1000" step="1000" placeholder="Masukkan nominal DP" />
                        </div>
                        <template x-if="dpError">
                            <p class="text-red-600 text-sm mt-1 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span x-text="dpError"></span>
                            </p>
                        </template>

                        <!-- Pilihan Rencana Pelunasan (Hanya Tampil Jika Pilih DP) -->
                        <div class="mt-5 pt-4 border-t border-yellow-200">
                            <x-input-label value="Rencana Pelunasan Sisa Tagihan" class="text-cyan-700 font-medium mb-2" />
                            <p class="text-xs text-gray-600 mb-3">Pilih bagaimana Anda ingin melunasi sisa pembayaran saat mengambil mobil nanti:</p>
                            <div class="flex flex-col sm:flex-row gap-4">
                                <label
                                    class="flex-1 inline-flex items-start space-x-3 p-3 border rounded-lg transition duration-150 cursor-pointer"
                                    :class="{ 'bg-white border-cyan-500 shadow-md ring-1 ring-cyan-500': metodePembayaran === 'transfer', 'bg-white border-gray-300 hover:border-cyan-400': metodePembayaran !== 'transfer' }">
                                    <input type="radio" value="transfer" x-model="metodePembayaran"
                                        name="metode_pembayaran" class="mt-1 text-cyan-600 focus:ring-cyan-500">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-800">Transfer / Online</span>
                                        <span class="text-xs text-gray-500">Bayar via sistem (Midtrans)</span>
                                    </div>
                                </label>
                                <label
                                    class="flex-1 inline-flex items-start space-x-3 p-3 border rounded-lg transition duration-150 cursor-pointer"
                                    :class="{ 'bg-white border-cyan-500 shadow-md ring-1 ring-cyan-500': metodePembayaran === 'cash', 'bg-white border-gray-300 hover:border-cyan-400': metodePembayaran !== 'cash' }">
                                    <input type="radio" value="cash" x-model="metodePembayaran"
                                        name="metode_pembayaran" class="mt-1 text-cyan-600 focus:ring-cyan-500">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-800">Bayar di Tempat</span>
                                        <span class="text-xs text-gray-500">Tunai saat serah terima mobil</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Ringkasan Pembayaran -->
                    <div class="mt-8 pt-4 border-t-2 border-cyan-400 bg-cyan-50 p-6 rounded-xl shadow-lg"
                        x-show="durasiHari() > 0">
                        <h2 class="text-xl font-bold text-cyan-800 mb-4">Ringkasan Pembayaran</h2>
                        <div class="space-y-2 text-gray-700">
                            <div class="flex justify-between"><span>Durasi Sewa:</span><span
                                    class="font-bold text-cyan-700" x-text="durasiHari() + ' hari'"></span></div>
                            <div class="flex justify-between"><span>Biaya Sewa Mobil:</span><span
                                    class="font-semibold text-cyan-700"
                                    x-text="formatRupiah(durasiHari() * hargaMobil)"></span></div>
                            <div class="flex justify-between" x-show="addOnSopir == 1"><span>Biaya Sopir:</span><span
                                    class="font-semibold text-cyan-700"
                                    x-text="formatRupiah(durasiHari() * hargaSopirPerHari)"></span></div>
                            <div class="border-t border-cyan-200 pt-3 flex justify-between text-lg font-bold">
                                <span>TOTAL HARGA:</span><span class="text-red-600"
                                    x-text="formatRupiah(totalHarga())"></span>
                            </div>
                            <div class="border-t border-cyan-200 pt-3 mt-3">
                                <div class="flex justify-between"><span class="font-bold"
                                        x-text="tipePembayaran === 'lunas' ? 'BAYAR LUNAS (Sekarang)' : 'Nominal DP (Dibayar Sekarang):'"></span><span
                                        class="font-bold text-green-700" x-text="formatRupiah(dpManual)"></span></div>
                                <template x-if="tipePembayaran === 'dp'">
                                    <div class="flex justify-between mt-1 text-sm text-gray-600"><span>Sisa
                                            Pembayaran <span x-text="metodePembayaran === 'cash' ? '(Di Tempat)' : '(Via Transfer)'"></span>:</span><span class="font-semibold"
                                            x-text="formatRupiah(sisaBayar())"></span></div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Submit -->
                    <div class="flex items-center justify-end mt-8 pt-4 border-t border-gray-200">
                        <x-primary-button type="button" id="pay-button"
                            class="ml-3 w-full sm:w-auto px-6 py-3 text-lg font-semibold bg-cyan-600 hover:bg-cyan-700 transition duration-200 shadow-xl hover:shadow-cyan-400/50 flex justify-center items-center gap-2">
                            <span x-text="tipePembayaran === 'lunas' ? 'Ajukan & Bayar Lunas' : 'Ajukan & Bayar DP Sekarang'"></span>
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script Datepicker & Toast -->
    <script>
        const bookedDates = @json($bookedDates ?? []);
        // Kita butuh array string simple untuk jQuery UI Datepicker
        const disabledDates = bookedDates.map(d => d);

        // 🔥 HELPER: Get Alpine Component Instance (with methods)
        function getAlpineComponent() {
            const alpineEl = document.querySelector('[x-data]');
            if (!alpineEl) {
                console.error('❌ Alpine element tidak ditemukan');
                return null;
            }
            
            // Alpine component instance contains both $data and methods
            if (alpineEl.__x) {
                console.log('  Alpine component accessed via __x');
                return alpineEl.__x;
            }
            
            console.error('❌ Tidak bisa akses Alpine component');
            return null;
        }
        
        // 🔥 HELPER: Get Alpine Data Only
        function getAlpineData() {
            const component = getAlpineComponent();
            if (component && component.$data) {
                console.log('  Alpine data accessed via component.$data');
                return component.$data;
            }
            
            // Fallback untuk _x_dataStack
            const alpineEl = document.querySelector('[x-data]');
            if (alpineEl && alpineEl._x_dataStack && alpineEl._x_dataStack[0]) {
                console.log('  Alpine data accessed via _x_dataStack');
                return alpineEl._x_dataStack[0];
            }
            
            console.error('❌ Tidak bisa akses Alpine data');
            return null;
        }

        // 🔥 SIMPLE HELPER: direct access to Alpine booking data via root id
        function alpineBooking() {
            const el = document.getElementById('booking-root');
            if (!el) return null;
            if (el._x_dataStack && el._x_dataStack[0]) return el._x_dataStack[0];
            return null;
        }
        
        // 🔥 HELPER: Check Driver - Standalone AJAX call (tidak bergantung Alpine methods)
        function checkDriverViaAjax(tanggalSewa, tanggalKembali) {
            if (!tanggalSewa) return;
            
            // Auto-generate tanggal kembali jika belum ada
            let tanggalKembaliForCheck = tanggalKembali;
            if (!tanggalKembaliForCheck) {
                const parts = tanggalSewa.split('-');
                const date = new Date(parts[2], parts[1] - 1, parts[0]);
                date.setDate(date.getDate() + 1);
                tanggalKembaliForCheck = 
                    String(date.getDate()).padStart(2, '0') + '-' +
                    String(date.getMonth() + 1).padStart(2, '0') + '-' +
                    date.getFullYear();
            }
            
            const alpineData = getAlpineData();
            if (alpineData) {
                alpineData.isCheckingDriver = true; // Set loading state
                console.log('⏳ isCheckingDriver set to true');
            }
            
            const postData = {
                _token: '{{ csrf_token() }}',
                tanggal_sewa: tanggalSewa,
                tanggal_kembali: tanggalKembaliForCheck
            };
            
            console.log('  AJAX Checking Driver:', postData);
            
            axios.post('{{ route('check.driver') }}', postData)
                .then(response => {
                    console.log('  Driver Check Response:', response.data);
                    
                    // Update Alpine data - Use wrapper to ensure reactivity
                    const alpineData = getAlpineData();
                    if (alpineData) {
                        // Update values in one go to trigger reactivity
                        alpineData.sisaSopir = response.data.sisa || response.data.sisa_sopir || 0;
                        alpineData.sopirAvailable = response.data.available;
                        alpineData.isCheckingDriver = false; // 🔥 SET FALSE TO SHOW RESULT
                        
                        console.log('     All states updated:');
                        console.log('   sisaSopir:', alpineData.sisaSopir);
                        console.log('   sopirAvailable:', alpineData.sopirAvailable);
                        console.log('   isCheckingDriver:', alpineData.isCheckingDriver);
                        
                        // Double-check display values
                        const spanSisa = document.querySelector('[x-text="sisaSopir"]');
                        if (spanSisa) {
                            console.log('   DOM text content:', spanSisa.textContent);
                        }
                        
                        // Jika tidak tersedia, auto-switch ke lepas kunci
                        if (!alpineData.sopirAvailable || alpineData.sisaSopir === 0) {
                            alpineData.sopirAvailable = false;
                            alpineData.addOnSopir = 0;
                            console.log('   ⚠️ Sopir not available - switched to Lepas Kunci');
                        }
                    }
                })
                .catch(error => {
                    console.error('❌ Driver Check Error:', error);
                    const alpineData = getAlpineData();
                    if (alpineData) {
                        alpineData.sopirAvailable = false;
                        alpineData.isCheckingDriver = false;
                    }
                });
        }

        function showToast(message) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = 'bg-red-600 text-white p-3 rounded-lg shadow-xl mb-2 animate-fadein border border-red-800 flex items-center gap-2';
            toast.innerHTML = '<span>' + message + '</span>';
            container.appendChild(toast);
            setTimeout(() => {
                toast.classList.add('animate-fadeout');
                toast.addEventListener('animationend', () => toast.remove());
            }, 3000);
        }

        // --- KONFIGURASI DATEPICKER SEWA ---
        $('#tanggal_sewa').datepicker({
            dateFormat: 'dd-mm-yy',
            minDate: 0,
            beforeShowDay: function(date) {
                var string = $.datepicker.formatDate('dd-mm-yy', date);
                var isAvailable = disabledDates.indexOf(string) == -1;
                return [isAvailable, isAvailable ? "" : "disabled-date"];
            },
            onSelect: function(selectedDate) {
                console.log('  tanggal_sewa SELECTED:', selectedDate);

                // Set minimal tanggal kembali = besoknya
                var parts = selectedDate.split("-");
                var minReturn = new Date(parts[2], parts[1] - 1, parts[0]);
                minReturn.setDate(minReturn.getDate() + 1);

                $('#tanggal_kembali').datepicker("option", "minDate", minReturn).val("");

                // Trigger event input manual agar Alpine.js mendeteksi perubahan
                this.dispatchEvent(new Event('input'));
                updateJamSewa();

                // 🔥 UPDATE STATE ALPINE secara eksplisit via helper
                const alpine = alpineBooking();
                if (alpine) {
                    console.log('  Setting Alpine data - tanggalSewa:', selectedDate);
                    alpine.tanggalSewa = selectedDate;
                    alpine.tanggalKembali = '';
                } else {
                    console.error('❌ Tidak bisa update Alpine data (tanggal_sewa)');
                }
            }
        });

        // --- KONFIGURASI DATEPICKER KEMBALI ---
        $('#tanggal_kembali').datepicker({
            dateFormat: 'dd-mm-yy',
            beforeShowDay: function(date) {
                var string = $.datepicker.formatDate('dd-mm-yy', date);
                var isAvailable = disabledDates.indexOf(string) == -1;
                return [isAvailable, isAvailable ? "" : "disabled-date"];
            },
            onSelect: function(selectedDate) {
                console.log('  tanggal_kembali SELECTED:', selectedDate);

                var sewa = $('#tanggal_sewa').val();
                console.log('  tanggal_sewa value:', sewa);

                if (sewa) {
                    var partsSewa = sewa.split("-");
                    var sewaDate = new Date(partsSewa[2], partsSewa[1] - 1, partsSewa[0]);
                    
                    var partsKembali = selectedDate.split("-");
                    var kembaliDate = new Date(partsKembali[2], partsKembali[1] - 1, partsKembali[0]);

                    // 1. Validasi Dasar (Tanggal Kembali harus > Tanggal Sewa)
                    if (kembaliDate <= sewaDate) {
                        showToast("⚠ Tanggal kembali harus lebih besar dari tanggal sewa!");
                        $(this).val('');
                        $('#deadline-info').text("");
                        this.dispatchEvent(new Event('input'));
                        return;
                    }

                    // 2. Validasi Overlap Booking
                    // Cek apakah di antara tanggal sewa dan kembali ada tanggal yg sudah dibooking
                    var isOverlap = false;
                    for (var d = new Date(sewaDate); d <= kembaliDate; d.setDate(d.getDate() + 1)) {
                        var checkStr = $.datepicker.formatDate('dd-mm-yy', d);
                        if (disabledDates.indexOf(checkStr) !== -1) {
                            isOverlap = true;
                            break;
                        }
                    }

                    if (isOverlap) {
                        showToast("⚠ Rentang tanggal mencakup tanggal yang sudah dibooking oleh orang lain!");
                        $(this).val(''); 
                        $('#deadline-info').text("");
                    } else {
                        updateDeadlineInfo();

                        // 🔥 TRIGGER CEK SOPIR - gunakan helper alpineBooking untuk sinkronisasi
                        const alpine = alpineBooking();
                        if (alpine) {
                            console.log('  Setting Alpine data - tanggalKembali:', selectedDate);
                            alpine.tanggalKembali = selectedDate;

                            // Delay kecil supaya Alpine dapat settle dan method tersedia
                            setTimeout(() => {
                                if (typeof alpine.checkDriver === 'function') {
                                    alpine.checkDriver();
                                } else {
                                    // fallback ke AJAX jika method tidak ada
                                    checkDriverViaAjax(alpine.tanggalSewa, selectedDate);
                                }
                            }, 100);
                        } else {
                            console.error('❌ Tidak bisa akses Alpine data');
                        }
                    }
                }

                this.dispatchEvent(new Event('input')); // Update Alpine
            }
        });

        function updateJamSewa() {
            var tanggalSewa = $('#tanggal_sewa').val();
            var jamInput = $('#jam_sewa');
            if (!tanggalSewa) return;
            var today = new Date();
            var parts = tanggalSewa.split("-");
            var selected = new Date(parts[2], parts[1] - 1, parts[0]);
            
            jamInput.removeAttr("min");
            
            // Jika sewa hari ini, jam minimal = jam sekarang + 1 jam
            if (selected.toDateString() === today.toDateString()) {
                var nextHour = new Date(today.getTime() + (60 * 60 * 1000));
                var minJam = String(nextHour.getHours()).padStart(2, '0') + ':' + String(nextHour.getMinutes()).padStart(2, '0');
                jamInput.attr("min", minJam);
                
                // Reset jam input jika kurang dari minJam
                if (jamInput.val() < minJam) jamInput.val(minJam);
            }
            jamInput[0].dispatchEvent(new Event('input'));
            updateDeadlineInfo(); 
        }

        function updateDeadlineInfo() {
            var tanggalKembali = $('#tanggal_kembali').val();
            var jamSewa = $('#jam_sewa').val();

            if (tanggalKembali && jamSewa) {
                const alpineEl = document.querySelector('[x-data]');
                if (alpineEl && alpineEl.__x) {
                    const durasi = alpineEl.__x.$data.durasiHari();

                    $('#deadline-info').html(
                        `✅ Durasi Sewa: <span class="font-bold text-cyan-800">${durasi} Hari</span>. Maksimal pengembalian pada pukul <span class="font-semibold text-cyan-800">${jamSewa} WIB</span> tanggal <span class="font-semibold text-cyan-800">${tanggalKembali}</span>.`
                    );
                } else {
                    // Fallback non-Alpine (jarang terjadi)
                    $('#deadline-info').html(
                        `⏰ Maksimal pengembalian pada pukul <span class="font-semibold text-cyan-800">${jamSewa} WIB</span> tanggal <span class="font-semibold text-cyan-800">${tanggalKembali}</span>.`
                    );
                }
            } else {
                $('#deadline-info').html("");
            }
        }

        $('#jam_sewa').on('change', function() {
            updateDeadlineInfo();
            const alpineData = getAlpineData();
            if (alpineData) {
                alpineData.jamSewa = this.value; 
            }
        });

        $(document).ready(function() {
            updateJamSewa();
        });

        // --- SUBMIT LOGIC (AXIOS) ---
        document.addEventListener('DOMContentLoaded', function() {
            const payButton = document.getElementById('pay-button');
            const form = document.getElementById('peminjaman-form');

            payButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (!form.checkValidity()) {
                    showToast('⚠️ Mohon lengkapi semua field yang diperlukan!');
                    form.reportValidity();
                    return;
                }
                
                const formData = new FormData(form);
                
                // UI Loading State
                payButton.disabled = true;
                payButton.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Memproses...';

                 axios.post(form.action, formData)
                    .then(function(response) {
                        // Reset Button
                        payButton.disabled = false;
                        payButton.textContent = 'Ajukan Peminjaman & Lanjutkan Pembayaran';

                        // 1. Tangkap Snap Token untuk Midtrans
                        if (response.data.snap_token) {
                            const peminjamanId = response.data.peminjaman_id;

                            // Buka Snap Midtrans
                         snap.pay(response.data.snap_token, {
                                onSuccess: function(result) {
                                    window.location.href = "{{ route('payment.success') }}";
                                },
                                onPending: function(result) {
                                    // KASUS ANDA: User pilih metode pembayaran (QRIS), lalu close.
                                    // Midtrans menganggap ini 'Pending'. Jika ingin tetap dihapus:
                                    console.log('User mendapatkan kode bayar tapi menutup Snap (Pending). Membersihkan data...');
                                    
                                    const peminjamanId = response.data.peminjaman_id;
                                    
                                    axios.post(`/peminjaman/force-cancel/${peminjamanId}`, {
                                        _token: '{{ csrf_token() }}'
                                    })
                                    .then(res => {
                                        // Setelah data dihapus, arahkan ke halaman utama agar tidak ada data "gantung"
                                       showToast('🚫 Pesanan dibatalkan.');
                                            setTimeout(() => { window.location.reload(); }, 1500);
                                    })
                                    .catch(err => {
                                        window.location.reload();
                                    });
                                },
                                onError: function(result) {
                                    window.location.href = "{{ route('payment.failed') }}";
                                },
                                onClose: function() {
                                    // Kasus: User klik "X" sebelum pilih metode pembayaran
                                    console.log('User menutup Snap sebelum memilih metode. Menjalankan pembersihan...');
                                    
                                    const peminjamanId = response.data.peminjaman_id;

                                    axios.post(`/peminjaman/force-cancel/${peminjamanId}`, {
                                        _token: '{{ csrf_token() }}'
                                    })
                                    .then(res => {
                                        if (res.data.status === 'success') {
                                            showToast('🚫 Pesanan dibatalkan.');
                                            setTimeout(() => { window.location.reload(); }, 1500);
                                        } else {
                                            window.location.href = "{{ route('pesanan.saya') }}";
                                        }
                                    })
                                    .catch(err => {
                                        window.location.reload();
                                    });
                                }
                            });
                        } 
                        // 2. Error dari Backend (Skenario bentrok/race condition)
                        else if (response.data.error) {
                            showToast('❌ ' + response.data.error);
                        }
                    })
                    .catch(function(error) {
                        payButton.disabled = false;
                        payButton.textContent = 'Ajukan Peminjaman & Lanjutkan Pembayaran';
                        
                        let msg = 'Terjadi kesalahan pada server.';
                        if (error.response && error.response.data) {
                            msg = error.response.data.message || error.response.data.error || msg;
                        }
                        showToast('❌ ' + msg);
                    });
            });
        });
    </script>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 pointer-events-none space-y-2"></div>
    
    <style>
        /* Styling untuk tanggal yang didisable di Datepicker */
        .ui-datepicker-unselectable.ui-state-disabled span,
        .disabled-date span,
        .disabled-date a {
            background: #ffecec !important; /* Merah muda lembut */
            color: #d80000 !important; /* Merah teks */
            opacity: 1 !important; /* Supaya warna jelas */
            cursor: not-allowed !important;
            text-decoration: line-through;
        }

        /* Animasi Toast */
        @keyframes fadein {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeout {
            from { opacity: 1; }
            to { opacity: 0; transform: translateY(-20px); }
        }
        .animate-fadein { animation: fadein 0.3s forwards; }
        .animate-fadeout { animation: fadeout 0.5s forwards; }

        /* Sembunyikan elemen Alpine sebelum dimuat */
        [x-cloak] { display: none !important; }
    </style>
</x-app-layout>