<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pesanan Saya') }}
        </h2>
    </x-slot>

    {{-- Script Midtrans --}}
    @push('scripts')
    <script type="text/javascript" src="https://app.midtrans.com/snap/snap.js"
        data-client-key="{{ config('services.midtrans.client_key') }}"></script>

        {{-- <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" 
         data-client-key="{{ config('services.midtrans.client_key') }}"></script> --}}
        {{-- Pastikan komponen scripts-pesanan memuat fungsi callMidtransSnap & callManualPayment --}}
        @include('components.scripts-pesanan')
    @endpush

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- 🔹 Tabs Filter Pesanan --}}
            <div class="mb-6 flex flex-wrap gap-2 justify-center">
                @php
                    $tabs = [
                        'semua' => 'Semua Pesanan',
                        'pembayaran dp' => 'Belum Lunas',
                        'sudah dibayar lunas' => 'Belum Diambil',
                        'berlangsung' => 'Berlangsung',
                        'selesai' => 'Selesai',
                        'dibatalkan' => 'Dibatalkan',
                    ];
                    $activeTab = request('status') ?? 'semua';
                @endphp

                @foreach ($tabs as $key => $label)
                    <a href="?status={{ $key }}"
                        class="px-4 py-2 rounded-full border text-sm font-medium
                        {{ $activeTab === $key ? 'bg-blue-600 text-white border-blue-600 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100' }}
                        transition">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- 🔹 Filter Berdasarkan Tab --}}
            @php
                $filtered = $activeTab === 'semua'
                    ? $peminjaman
                    : $peminjaman->filter(fn($item) => $item->status === $activeTab);
            @endphp

            @if ($filtered->isEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-10 text-center max-w-2xl mx-auto mt-10">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    <p class="text-gray-500 text-lg">Tidak ada pesanan pada kategori ini.</p>
                    <a href="{{ route('mobils.index') }}"
                        class="inline-block mt-6 bg-blue-600 text-white font-medium px-6 py-2.5 rounded-lg hover:bg-blue-700 transition shadow-sm">
                        Pesan Mobil Sekarang
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($filtered as $item)

                        {{-- ========================================================= --}}
                        {{-- 🔹 TAB SELESAI --}}
                        {{-- ========================================================= --}}
                        @if ($activeTab === 'selesai')
                            <div class="bg-white shadow-sm hover:shadow-lg transition-shadow duration-300 rounded-xl overflow-hidden border border-gray-200 flex flex-col h-full"
                                 x-data="{ 
                                    openModalBayar: false, 
                                    metodePilihan: 'transfer'
                                 }">
                                
                                {{-- Gambar & Overlay Status --}}
                                <div class="relative h-48 bg-gray-100">
                                    @if ($item->mobil && $item->mobil->foto)
                                        <img src="{{ asset('storage/' . $item->mobil->foto) }}" alt="{{ $item->mobil->merek }}" class="w-full h-full object-cover grayscale-[20%]">
                                    @else
                                        <div class="w-full h-48 flex items-center justify-center text-gray-500">Tidak ada gambar</div>
                                    @endif
                                    <div class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-bold shadow-sm backdrop-blur-sm bg-green-100/90 text-green-700 border border-green-200">
                                        {{ ucfirst($item->status) }}
                                    </div>
                                </div>

                                <div class="p-5 flex flex-col flex-1">
                                    @php
                                        $pengembalian = $item->pengembalian;
                                        
                                        // 🔹 MENGGUNAKAN ATRIBUT BARU DARI PENGGABUNGAN TABEL
                                        $totalDenda = $pengembalian ? (float) $pengembalian->total_denda : 0; 
                                        $statusPengembalian = $pengembalian ? strtolower(trim($pengembalian->status)) : null; 
                                        $statusDenda = $pengembalian ? strtolower(trim($pengembalian->status_denda)) : 'tidak ada denda';
                                        
                                        $isAdaDenda = $totalDenda > 0;
                                        
                                        // 🔹 Pengecekan lunas tidaknya denda sekarang sangat mudah, cukup cek kolom status_denda
                                        $isSudahDibayar = ($statusDenda === 'sudah dibayar');
                                    @endphp

                                    <h3 class="text-xl font-bold text-gray-900 mb-4 leading-tight">
                                        {{ $item->mobil->merek ?? '-' }} 
                                        <span class="font-normal text-gray-500 text-lg">| {{ $item->mobil->tipe ?? '-' }}</span>
                                    </h3>

                                    <div class="grid grid-cols-2 gap-3 mb-4 bg-gray-50 p-3 rounded-lg border border-gray-100">
                                        <div>
                                            <span class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Tgl Sewa</span>
                                            <span class="block text-sm text-gray-800 font-medium">{{ \Carbon\Carbon::parse($item->tanggal_sewa)->format('d M Y') }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Tgl Kembali</span>
                                            <span class="block text-sm text-gray-800 font-medium">{{ \Carbon\Carbon::parse($item->tanggal_kembali)->format('d M Y') }}</span>
                                        </div>
                                    </div>

                                    @if ($pengembalian)
                                        <div class="mb-5 bg-gray-50 border border-gray-200 rounded-lg p-4 text-sm text-gray-700 shadow-sm">
                                            
                                            <div class="flex justify-between items-center mb-2 pb-2 border-b border-gray-200 border-dashed">
                                                <span class="text-gray-500">Total Denda</span>
                                                <span class="font-bold {{ $isAdaDenda && !$isSudahDibayar ? 'text-red-600' : 'text-gray-900' }}">Rp {{ number_format($totalDenda, 0, ',', '.') }}</span>
                                            </div>

                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-500">Status</span>
                                                
                                                {{-- 🔹 Tampilan Status --}}
                                                @if ($statusPengembalian === 'menunggu pengecekan')
                                                    <span class="text-yellow-600 font-medium">Dalam Pengecekan</span>
                                                @elseif($statusPengembalian === 'selesai pengecekan' && $isAdaDenda && !$isSudahDibayar)
                                                    <span class="text-red-600 font-bold">Dicek (Belum Bayar Denda)</span>
                                                @elseif($statusPengembalian === 'selesai pengecekan' && !$isAdaDenda)
                                                    <span class="text-green-600 font-bold">Dicek (Aman)</span>
                                                @elseif($statusPengembalian === 'menunggu_pembayaran_midtrans')
                                                    <span class="text-blue-600 font-medium">Menunggu Midtrans</span>
                                                @elseif($statusPengembalian === 'menunggu_verifikasi_transfer' || $statusPengembalian === 'menunggu_pembayaran_tunai')
                                                    <span class="text-yellow-600 font-medium">Menunggu Verifikasi</span>
                                                @elseif($statusPengembalian === 'sudah di cek dan denda dibayarkan' || $isSudahDibayar)
                                                    <span class="text-green-600 font-medium">Selesai & Denda Lunas</span>
                                                @elseif($statusPengembalian === 'selesai')
                                                    <span class="text-green-600 font-medium">Selesai & Lunas</span>
                                                @else
                                                    <span class="text-gray-600 font-medium">{{ ucfirst(str_replace('_', ' ', $statusPengembalian)) }}</span>
                                                @endif
                                            </div>

                                            {{-- Pesan Keterangan Tambahan --}}
                                            @if($isAdaDenda && !$isSudahDibayar)
                                                <p class="mt-3 text-xs text-red-600 font-medium flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Harap selesaikan pembayaran denda.</p>
                                            @elseif(!$isAdaDenda && in_array($statusPengembalian, ['selesai pengecekan', 'selesai']))
                                                <p class="mt-3 text-xs text-green-600 font-medium flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Tidak ada tagihan denda.</p>
                                            @elseif($isSudahDibayar)
                                                <p class="mt-3 text-xs text-green-600 font-medium flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Tagihan denda sudah dibayar lunas.</p>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Tombol Aksi -->
                                    <div class="mt-auto pt-2 flex flex-col gap-2.5">
                                        <div class="grid grid-cols-2 gap-2">
                                            @if($item->sopir_id)
                                                <a href="{{ route('pesanan.chat', $item->id) }}" class="flex justify-center items-center px-4 py-2 border border-blue-200 text-sm font-medium rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 transition shadow-sm">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                                    Riwayat Chat
                                                </a>
                                            @endif
                                            <a href="{{ route('mobils.show', $item->mobil_id) }}" class="flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:ring-2 focus:ring-gray-200 transition {{ $item->sopir_id ? '' : 'col-span-2' }}">
                                                Detail Mobil
                                            </a>
                                        </div>

                                        {{-- 🔹 Tombol Bayar Denda --}}
                                        {{-- Logika diperpendek: Muncul HANYA JIKA ada denda dan belum lunas! --}}
                                        @if ($pengembalian && $isAdaDenda && !$isSudahDibayar)
                                            <button @click="openModalBayar = true" class="w-full flex justify-center items-center px-4 py-2.5 text-sm font-semibold rounded-lg text-white bg-red-600 hover:bg-red-700 transition shadow-sm focus:ring-2 focus:ring-red-500 animate-pulse">
                                                Bayar Denda (Rp {{ number_format($totalDenda, 0, ',', '.') }})
                                            </button>
                                        @endif
                                    </div>

                                    {{-- MODAL PEMBAYARAN DENDA --}}
                                    <div x-show="openModalBayar" style="display: none;" class="fixed inset-0 z-[70] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm" x-transition.opacity>
                                        <div @click.outside="openModalBayar = false" class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm text-center transform transition-all duration-300 m-4">
                                            <h2 class="text-lg font-bold text-gray-800 mb-2">Pilih Metode Pembayaran</h2>
                                            <p class="text-sm text-gray-600 mb-5">Total Denda: <span class="font-bold text-red-600">Rp {{ number_format($totalDenda ?? 0, 0, ',', '.') }}</span></p>

                                            <div class="flex flex-col gap-3 mb-6 text-left">
                                                <label class="border p-3 rounded-xl cursor-pointer transition" :class="metodePilihan === 'transfer' ? 'border-blue-600 bg-blue-50 shadow-sm' : 'border-gray-300 hover:bg-gray-50'">
                                                    <input type="radio" x-model="metodePilihan" value="transfer" class="hidden">
                                                    <span class="ml-2 font-medium text-gray-800">Transfer / E-Wallet</span>
                                                    <p class="text-xs text-gray-500 ml-6">Otomatis lunas (VA, QRIS, dll)</p>
                                                </label>
                                                <label class="border p-3 rounded-xl cursor-pointer transition" :class="metodePilihan === 'tunai' ? 'border-blue-600 bg-blue-50 shadow-sm' : 'border-gray-300 hover:bg-gray-50'">
                                                    <input type="radio" x-model="metodePilihan" value="tunai" class="hidden">
                                                    <span class="ml-2 font-medium text-gray-800">Bayar Tunai</span>
                                                    <p class="text-xs text-gray-500 ml-6">Bayar langsung di kantor</p>
                                                </label>
                                            </div>

                                            <div class="flex gap-3">
                                                <button type="button" @click="openModalBayar = false" class="flex-1 bg-white border border-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-50 transition font-medium">Batal</button>
                                                <button type="button" @click="
                                                        if (metodePilihan === 'transfer') {
                                                            callMidtransSnap('{{ $pengembalian->kode_pengembalian ?? '' }}');
                                                        } else if (metodePilihan === 'tunai') {
                                                            callManualPayment('{{ $pengembalian->kode_pengembalian ?? '' }}', 'tunai');
                                                        }
                                                        openModalBayar = false; 
                                                    " class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition font-medium shadow-sm">Konfirmasi</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        {{-- ========================================================= --}}
                        {{-- 🔹 TAB DIBATALKAN --}}
                        {{-- ========================================================= --}}
                        @elseif ($activeTab === 'dibatalkan')
                            <div class="bg-white shadow-sm hover:shadow-lg transition-shadow duration-300 rounded-xl overflow-hidden border border-gray-200 flex flex-col h-full">
                                
                                <div class="relative h-48 bg-gray-100">
                                    @if ($item->mobil && $item->mobil->foto)
                                        <img src="{{ asset('storage/' . $item->mobil->foto) }}" alt="{{ $item->mobil->merek }}" class="w-full h-full object-cover grayscale opacity-80">
                                    @else
                                        <div class="w-full h-48 flex items-center justify-center text-gray-500">Tidak ada gambar</div>
                                    @endif
                                    <div class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-bold shadow-sm backdrop-blur-sm bg-red-100/90 text-red-700 border border-red-200">
                                        {{ ucfirst($item->status) }}
                                    </div>
                                </div>

                                <div class="p-5 flex flex-col flex-1">
                                    <h3 class="text-xl font-bold text-gray-900 mb-4 leading-tight opacity-80">
                                        {{ $item->mobil->merek ?? '-' }} 
                                        <span class="font-normal text-gray-500 text-lg">| {{ $item->mobil->tipe ?? '-' }}</span>
                                    </h3>

                                    <div class="grid grid-cols-2 gap-3 mb-4 bg-gray-50 p-3 rounded-lg border border-gray-100 opacity-80">
                                        <div>
                                            <span class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Tgl Sewa</span>
                                            <span class="block text-sm text-gray-800 font-medium">{{ \Carbon\Carbon::parse($item->tanggal_sewa)->format('d M Y') }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Tgl Kembali</span>
                                            <span class="block text-sm text-gray-800 font-medium">{{ \Carbon\Carbon::parse($item->tanggal_kembali)->format('d M Y') }}</span>
                                        </div>
                                    </div>

                                    @php $batal = $item->pembatalan ?? null; @endphp
                                    <div class="mb-5 bg-rose-50 border border-rose-200 rounded-lg p-4 text-sm text-rose-900 shadow-sm">
                                        <p class="mb-2 flex justify-between border-b border-rose-200/50 pb-2">
                                            <span class="text-rose-700">Dibatalkan pada</span> 
                                            <span class="font-semibold">{{ optional($batal)->cancelled_at?->format('d M Y, H:i') ?? '-' }}</span>
                                        </p>
                                        <p class="mb-2 flex flex-col">
                                            <span class="text-rose-700 mb-1">Alasan:</span> 
                                            <span class="font-medium bg-white/50 p-2 rounded">{{ $batal->alasan ?? '-' }}</span>
                                        </p>
                                        <div class="flex justify-between items-center mt-3 pt-2 border-t border-rose-200/50">
                                            <span class="text-rose-700">Status Refund:</span>
                                            @if(optional($batal)->refund_status === 'pending_refund')
                                                <span class="px-2 py-1 rounded-md bg-yellow-100 text-yellow-800 text-xs font-bold border border-yellow-200">Pending</span>
                                            @elseif(optional($batal)->refund_status === 'refunded')
                                                <span class="px-2 py-1 rounded-md bg-green-100 text-green-800 text-xs font-bold border border-green-200">Refunded</span>
                                            @else
                                                <span class="px-2 py-1 rounded-md bg-gray-200 text-gray-700 text-xs font-bold border border-gray-300">No Refund</span>
                                            @endif
                                        </div>
                                        @if(optional($batal)->jumlah_refund > 0)
                                            <p class="mt-2 text-right font-bold text-lg">Rp {{ number_format(optional($batal)->jumlah_refund ?? 0, 0, ',', '.') }}</p>
                                        @endif
                                    </div>

                                    <!-- Tombol Aksi -->
                                    <div class="mt-auto pt-2 grid grid-cols-2 gap-2">
                                        @if($item->sopir_id)
                                            <a href="{{ route('pesanan.chat', $item->id) }}" class="flex justify-center items-center px-4 py-2 border border-blue-200 text-sm font-medium rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 transition shadow-sm">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                                Riwayat Chat
                                            </a>
                                        @endif
                                        <a href="{{ route('mobils.show', $item->mobil_id) }}" class="flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:ring-2 focus:ring-gray-200 transition {{ $item->sopir_id ? '' : 'col-span-2' }}">
                                            Detail Mobil
                                        </a>
                                    </div>

                                </div>
                            </div>

                        {{-- ========================================================= --}}
                        {{-- 🔹 TAB LAIN (Default Card) --}}
                        {{-- ========================================================= --}}
                        @else
                            @include('components.card-pesanan-lain', ['item' => $item, 'activeTab' => $activeTab])
                        @endif

                    @endforeach
                    @include('components.scripts-pesanan')
                </div>
            @endif
        </div>
    </div>
</x-app-layout>