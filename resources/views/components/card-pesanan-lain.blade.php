<div class="bg-white shadow-sm hover:shadow-lg transition-shadow duration-300 rounded-xl overflow-hidden border border-gray-200 flex flex-col h-full"
     x-data="{ 
        openModal: false, 
        chatModalOpen: false,
        pesanBaru: '',
        scrollToBottom() { 
            $nextTick(() => { 
                const cb = document.getElementById('kotak-chat-{{ $item->id }}'); 
                if(cb) cb.scrollTop = cb.scrollHeight; 
            }) 
        },
        initChat() {
            const setupEcho = () => {
                if (window.Echo) {
                    window.Echo.private('chat.{{ $item->id }}')
                        .listen('MessageSent', (event) => {
                            if (event.sender_id !== {{ auth()->id() }}) {
                                const cb = document.getElementById('kotak-chat-{{ $item->id }}');
                                const emptyMsg = cb.querySelector('.empty-message');
                                if(emptyMsg) emptyMsg.remove();
                                
                                const html = `<div class=\'self-start max-w-[80%] bg-white border border-gray-100 text-gray-800 p-3 rounded-r-2xl rounded-tl-2xl rounded-bl-sm shadow-sm mt-2\'><p class=\'text-xs text-blue-600 font-bold mb-0.5\'>Sopir</p><p class=\'text-sm\'>${event.message}</p><span class=\'text-[10px] text-gray-400 flex justify-start mt-1\'>Baru saja</span></div>`;
                                cb.insertAdjacentHTML('beforeend', html);
                                this.scrollToBottom();
                            }
                        });
                } else {
                    setTimeout(setupEcho, 100);
                }
            };
            setupEcho();
        },
        kirimPesan() {
            if(this.pesanBaru.trim() === '') return;
            let pesan = this.pesanBaru;
            this.pesanBaru = ''; 
            
            const cb = document.getElementById('kotak-chat-{{ $item->id }}');
            const emptyMsg = cb.querySelector('.empty-message');
            if(emptyMsg) emptyMsg.remove();

            const html = `<div class=\'self-end max-w-[80%] bg-green-500 text-white p-3 rounded-l-2xl rounded-tr-2xl rounded-br-sm shadow-sm mt-2\'><p class=\'text-sm\'>${pesan}</p><span class=\'text-[10px] text-green-100 flex justify-end mt-1\'>Baru saja</span></div>`;
            cb.insertAdjacentHTML('beforeend', html);
            this.scrollToBottom();

            axios.post('{{ route('chat.kirim') }}', {
                peminjaman_id: '{{ $item->id }}',
                message: pesan
            }).catch(error => console.error('Gagal mengirim pesan', error));
        }
    }" x-init="initChat()">
    
    <!-- Bagian Gambar & Status Badge -->
    <div class="relative h-48 bg-gray-100">
        @if ($item->mobil && $item->mobil->foto)
            <img src="{{ asset('storage/' . $item->mobil->foto) }}" alt="{{ $item->mobil->merek }}" class="w-full h-full object-cover">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span class="text-sm">Tidak ada gambar</span>
            </div>
        @endif

        <!-- Status Overlay -->
        <div class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-bold shadow-sm backdrop-blur-sm
            @if($item->status === 'pembayaran dp') bg-yellow-100/90 text-yellow-700 border border-yellow-200
            @elseif($item->status === 'menunggu pembayaran') bg-blue-100/90 text-blue-700 border border-blue-200
            @else bg-gray-100/90 text-gray-700 border border-gray-200 @endif">
            {{ ucfirst($item->status) }}
        </div>
    </div>

    <!-- Bagian Konten Utama -->
    <div class="p-5 flex flex-col flex-1">
        <!-- Judul -->
        <h3 class="text-xl font-bold text-gray-900 mb-4 leading-tight">
            {{ $item->mobil->merek ?? '-' }} 
            <span class="font-normal text-gray-500 text-lg">| {{ $item->mobil->tipe ?? '-' }}</span>
        </h3>

        <!-- Informasi Tanggal (Grid layout) -->
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

        <!-- Rincian Biaya -->
        <div class="space-y-2.5 mb-5 border border-gray-100 rounded-lg p-4 bg-white shadow-sm">
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-500">Total Harga</span>
                <span class="font-medium text-gray-900">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-500">DP Dibayarkan</span>
                <span class="font-medium text-green-600">Rp {{ number_format($item->dp_dibayarkan, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center text-sm pt-2.5 border-t border-dashed border-gray-200">
                <span class="font-semibold text-gray-800">{{ $activeTab === 'sudah dibayar lunas' ? 'Pelunasan' : 'Sisa Bayar' }}</span>
                <span class="font-bold text-red-600">Rp {{ number_format($item->sisa_bayar, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Kondisi Mobil --}}
        @if($activeTab === 'sudah dibayar lunas' || $activeTab === 'berlangsung')
            <div class="mb-4 flex items-start gap-2.5 text-sm bg-blue-50/50 p-3 rounded-lg border border-blue-100">
                <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <span class="font-semibold text-gray-800 block">Kondisi Saat Ini:</span>
                    <span class="{{ $item->kondisi_mobil ? 'text-gray-700' : 'text-gray-500 italic' }}">
                        {{ $item->kondisi_mobil ?? 'Belum dicatat oleh Admin' }}
                    </span>
                </div>
            </div>
        @endif

        {{-- Status Pembatalan --}}
        @php
            $reqBatal = $item->pembatalan ?? null;
            $isPendingCancel = $reqBatal && $reqBatal->approval_status === 'pending';
        @endphp

        @if($isPendingCancel)
            <div class="mb-4 flex items-center gap-2 bg-yellow-50 text-yellow-800 border border-yellow-200 rounded-lg p-3 text-sm">
                <svg class="w-5 h-5 shrink-0 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Pembatalan <strong>menunggu persetujuan</strong> admin.</span>
            </div>
        @endif

        {{-- Rundown Pengembalian (Berlangsung) --}}
        @if($activeTab === 'berlangsung')
            <div class="mb-4 p-4 border border-indigo-100 bg-indigo-50 rounded-lg shadow-inner">
                <h4 class="text-sm font-bold text-indigo-900 mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Jadwal Pengembalian
                </h4>
                <div class="text-sm text-indigo-800 space-y-1">
                    <p class="flex justify-between"><span>Tanggal</span> <span class="font-medium">{{ \Carbon\Carbon::parse($item->tanggal_kembali)->format('d M Y') }}</span></p>
                    <p class="flex justify-between"><span>Jam Max</span> <span class="font-medium">{{ \Carbon\Carbon::parse($item->jam_sewa)->format('H:i') }} WIB</span></p>
                </div>
            </div>
        @endif

        <!-- Area Tombol Aksi (Otomatis ditarong ke bawah) -->
        <div class="mt-auto pt-2 flex flex-col gap-2.5">
            
            {{-- TAB: Menunggu Pembayaran --}}
            @if($activeTab === 'menunggu pembayaran')
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('mobils.show', $item->mobil_id) }}" class="flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:ring-2 focus:ring-gray-200 transition">
                        Detail Mobil
                    </a>
                    @unless($isPendingCancel)
                        <button onclick="bukaModalBatal({{ $item->id }})" class="flex justify-center items-center px-4 py-2 text-sm font-medium rounded-lg text-red-700 bg-red-50 border border-red-200 hover:bg-red-100 transition focus:ring-2 focus:ring-red-200">
                            Batal Pesan
                        </button>
                    @endunless
                </div>
            @endif

            {{-- TAB: Pembayaran DP --}}
            @if($activeTab === 'pembayaran dp')
                <button onclick="bayarSisa({{ $item->id }})" class="w-full flex justify-center items-center px-4 py-2.5 text-sm font-semibold rounded-lg text-white bg-green-600 hover:bg-green-700 transition shadow-sm focus:ring-2 focus:ring-green-500 focus:ring-offset-1">
                    Bayar Sisa Tagihan
                </button>
                <a href="{{ route('mobils.show', $item->mobil_id) }}" class="w-full flex justify-center items-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">
                    Lihat Mobil
                </a>
            @endif

            {{-- TAB: Lunas (Belum Diambil) --}}
            @if($activeTab === 'sudah dibayar lunas')
                <button onclick="bukaModal({{ $item->id }})" class="w-full flex justify-center items-center px-4 py-2.5 text-sm font-semibold rounded-lg text-gray-900 bg-yellow-400 hover:bg-yellow-500 transition shadow-sm focus:ring-2 focus:ring-yellow-400 focus:ring-offset-1">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Cek Kondisi Mobil
                </button>
                <div class="grid grid-cols-2 gap-2 mt-1">
                    <button @click="chatModalOpen = true; scrollToBottom()" class="flex justify-center items-center px-4 py-2 border border-blue-200 text-sm font-medium rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 transition shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        Chat Sopir
                    </button>
                    <a href="{{ route('mobils.show', $item->mobil_id) }}" class="flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">
                        Detail Mobil
                    </a>
                    @unless($isPendingCancel)
                        <button onclick="bukaModalBatal({{ $item->id }})" class="col-span-2 flex justify-center items-center px-4 py-2 text-sm font-medium rounded-lg text-red-700 bg-red-50 border border-red-200 hover:bg-red-100 transition">
                            Batalkan Pesanan
                        </button>
                    @endunless
                </div>
            @endif

            {{-- TAB: Berlangsung --}}
            @if($activeTab === 'berlangsung')
                <div class="w-full">
                    <button @click="openModal = true" class="w-full flex justify-center items-center px-4 py-2.5 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition shadow-sm focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                        Selesaikan Peminjaman
                    </button>
                    
                    <div class="grid grid-cols-2 gap-2 mt-2.5">
                        <button @click="chatModalOpen = true; scrollToBottom()" class="flex justify-center items-center px-4 py-2 border border-blue-200 text-sm font-medium rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 transition shadow-sm">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            Chat Sopir
                        </button>
                        <a href="{{ route('mobils.show', $item->mobil_id) }}" class="flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">
                            Detail Mobil
                        </a>
                        @unless($isPendingCancel)
                            <button onclick="bukaModalBatal({{ $item->id }})" class="col-span-2 flex justify-center items-center px-4 py-2 text-sm font-medium rounded-lg text-red-700 bg-red-50 border border-red-200 hover:bg-red-100 transition">
                                Batalkan Pesanan
                            </button>
                        @endunless
                    </div>

                    {{-- Modal Konfirmasi Modern (Penyelesaian) --}}
                    <div x-show="openModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0">
                        <div @click.away="openModal = false" class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm text-center transform transition-all m-4"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                            
                            <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                                <svg class="h-7 w-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            
                            <h2 class="text-lg font-bold text-gray-900 mb-2">Kembalikan Mobil?</h2>
                            <p class="text-gray-500 mb-6 text-sm">Pastikan Anda telah mengecek semua barang bawaan sebelum menyerahkan kunci.</p>
                            
                            <div class="flex gap-3">
                                <button @click="openModal = false" class="flex-1 px-4 py-2 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                                    Nanti Saja
                                </button>
                                <form action="{{ route('pengembalian.store', ['peminjaman_id' => $item->id]) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full h-full px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition shadow-sm">
                                        Ya, Selesai
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Fallback untuk status lain jika ada --}}
            @if(!in_array($activeTab, ['menunggu pembayaran', 'pembayaran dp', 'sudah dibayar lunas', 'berlangsung']))
                <a href="{{ route('mobils.show', $item->mobil_id) }}" class="w-full flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">
                    Lihat Detail Mobil
                </a>
            @endif

        </div>
    </div>
    
    {{-- Memanggil komponen partial modal yang tadi dipilih --}}
    @include('components.chat-modal-partial', ['item' => $item])
</div>