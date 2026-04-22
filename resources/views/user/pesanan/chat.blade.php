<x-app-layout>
    <!-- Wrapper Utama 100% Full Screen Tanpa Padding/Margin di Sekelilingnya -->
    <div class="w-full h-[calc(100dvh-64px)] flex flex-col relative overflow-hidden bg-slate-50 mt-0"
         x-data="chatComponent()" 
         x-init="initChat()">
        
        <!-- 🔹 Header Chat (Full Width) -->
        <div class="bg-white border-b border-gray-200 px-4 sm:px-8 py-3 flex justify-between items-center shrink-0 shadow-sm z-10 w-full">
            <div class="flex items-center gap-4">
                <a href="{{ route('pesanan.saya') }}" class="p-2 -ml-2 text-gray-500 hover:text-cyan-600 hover:bg-gray-100 rounded-full transition focus:outline-none">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </a>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-cyan-100 text-cyan-600 rounded-full flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                </div>
                <div>
                    <!-- 🔹 Langsung tembak nama sopir dari relasi -->
                    <h3 class="text-gray-900 font-bold text-base md:text-lg leading-tight capitalize">
                        {{ $peminjaman->sopir->nama }}
                    </h3>
                    <p class="text-cyan-600 text-xs md:text-sm font-medium">Order #{{ $peminjaman->id }} • {{ $peminjaman->mobil->merek ?? 'Mobil' }}</p>
                </div>
            </div>
        </div>
        
        <!-- 🔹 Area Pesan (Scrollable, Full Width dengan Padding) -->
        <div id="chat-box" class="flex-1 p-4 pb-8 sm:px-12 md:px-8 lg:px-8 sm:py-8 overflow-y-auto bg-slate-50 flex flex-col space-y-3 sm:space-y-4 w-full">
            @forelse($riwayat as $chat)
                @if($chat->pengirim_id == auth()->id())
                    <!-- Pesan Saya -->
                    <div class="self-end max-w-[85%] md:max-w-[60%] lg:max-w-[50%] bg-blue-600 text-white p-3 sm:p-4 rounded-l-2xl rounded-tr-2xl rounded-br-sm shadow-sm mt-1">
                        <p class="text-sm sm:text-base leading-relaxed">{{ $chat->isi_pesan }}</p>
                        <span class="text-[10px] text-blue-200 flex justify-end mt-1.5">
                            {{ \Carbon\Carbon::parse($chat->created_at)->format('H:i') }}
                        </span>
                    </div>
                @else
                    <!-- Pesan Sopir -->
                    <div class="self-start max-w-[85%] md:max-w-[60%] lg:max-w-[50%] bg-white border border-gray-200 p-3 sm:p-4 rounded-r-2xl rounded-tl-2xl rounded-bl-sm shadow-sm mt-1 text-gray-800">
                        <!-- 🔹 Tampilkan nama asli sopir di setiap pesan -->
                        <p class="text-xs text-blue-600 font-bold mb-1">{{ $peminjaman->sopir->nama }}</p>
                        <p class="text-sm sm:text-base leading-relaxed">{{ $chat->isi_pesan }}</p>
                        <span class="text-[10px] text-gray-400 flex justify-start mt-1.5">
                            {{ \Carbon\Carbon::parse($chat->created_at)->format('H:i') }}
                        </span>
                    </div>
                @endif
            @empty
                <div class="empty-message flex-1 flex flex-col items-center justify-center text-center opacity-70 my-auto">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-blue-50 text-blue-300 rounded-full flex items-center justify-center mb-3 sm:mb-4 shadow-inner">
                        <svg class="w-8 h-8 sm:w-10 sm:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <p class="text-gray-500 text-sm sm:text-base font-medium">Mulai Percakapan</p>
                    <p class="text-gray-400 text-xs sm:text-sm mt-1">Tanyakan lokasi penjemputan atau informasi lainnya ke Sopir Anda.</p>
                </div>
            @endforelse
        </div>

        <!-- 🔹 Form Input (Axios Alpine.js) -->
        <form @submit.prevent="kirimPesan" class="w-full p-3 sm:px-12 md:px-24 lg:px-64 sm:py-5 bg-white border-t border-gray-200 shrink-0 flex gap-2 sm:gap-4 items-end shadow-[0_-4px_10px_-1px_rgba(0,0,0,0.05)] pb-safe">
            <textarea 
                x-model="pesanBaru"
                rows="1"
                required
                placeholder="Ketik pesan Anda..."
                class="flex-1 px-5 py-3 sm:py-3.5 min-h-[48px] sm:min-h-[52px] bg-gray-100 border-transparent focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 rounded-full resize-none text-sm sm:text-base transition shadow-inner flex items-center placeholder-gray-400"
                style="line-height: 1.5;"
                @keydown.enter.prevent="if(!$event.shiftKey) kirimPesan()"
                :disabled="isSending"
            ></textarea>
            
            <button type="submit" :disabled="isSending"
                class="w-12 h-12 sm:w-[52px] sm:h-[52px] rounded-full bg-blue-600 text-white flex items-center justify-center hover:bg-blue-700 transition shadow-md shrink-0 focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 disabled:opacity-50">
                <svg x-show="!isSending" class="w-5 h-5 sm:w-6 sm:h-6 ml-1 transform rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                <svg x-show="isSending" style="display: none;" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </button>
        </form>
    </div>

    <!-- 🔹 SCRIPT ALPINE JS -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('chatComponent', () => ({
                pesanBaru: '',
                isSending: false,
                scrollToBottom() { 
                    this.$nextTick(() => { 
                        const cb = document.getElementById('chat-box'); 
                        if(cb) cb.scrollTop = cb.scrollHeight; 
                    }) 
                },
                initChat() {
                    this.scrollToBottom(); 

                    if (window.Echo) {
                        window.Echo.private('chat.{{ $peminjaman->id }}')
                            .listen('MessageSent', (event) => {
                                let payload = event.pesan ? event.pesan : event;
                                
                                let senderId = String(payload.pengirim_id);
                                let myId = String('{{ auth()->id() }}');
                                
                                // Jika pesan dikirim oleh SOPIR
                                if (senderId !== myId) {
                                    const cb = document.getElementById('chat-box');
                                    const emptyMsg = cb.querySelector('.empty-message');
                                    if(emptyMsg) emptyMsg.remove();
                                    
                                    // 🔹 Inject nama asli sopir ke bubble chat
                                    const namaSopir = '{{ $peminjaman->sopir->nama }}';
                                    
                                    const html = `<div class='self-start max-w-[85%] md:max-w-[60%] lg:max-w-[50%] bg-white border border-gray-200 text-gray-800 p-3 sm:p-4 rounded-r-2xl rounded-tl-2xl rounded-bl-sm shadow-sm mt-2'>
                                                    <p class='text-xs text-blue-600 font-bold mb-0.5 capitalize'>${namaSopir}</p>
                                                    <p class='text-sm sm:text-base leading-relaxed'>${payload.isi_pesan}</p>
                                                    <span class='text-[10px] text-gray-400 flex justify-start mt-1.5'>Baru saja</span>
                                                  </div>`;
                                    cb.insertAdjacentHTML('beforeend', html);
                                    this.scrollToBottom();

                                    let audio = new Audio('/notifikasi.mp3');
                                    audio.play().catch(e => {});
                                }
                            });
                    }
                },
                kirimPesan() {
                    if(this.pesanBaru.trim() === '' || this.isSending) return;
                    
                    let pesan = this.pesanBaru;
                    this.pesanBaru = ''; 
                    this.isSending = true;
                    
                    const cb = document.getElementById('chat-box');
                    const emptyMsg = cb.querySelector('.empty-message');
                    if(emptyMsg) emptyMsg.remove();

                    // Render ke layar saya
                    const html = `<div class='self-end max-w-[85%] md:max-w-[60%] lg:max-w-[50%] bg-blue-600 text-white p-3 sm:p-4 rounded-l-2xl rounded-tr-2xl rounded-br-sm shadow-sm mt-2'>
                                    <p class='text-sm sm:text-base leading-relaxed'>${pesan}</p>
                                    <span class='text-[10px] text-blue-200 flex justify-end mt-1.5 status-mengirim'>Mengirim...</span>
                                  </div>`;
                    cb.insertAdjacentHTML('beforeend', html);
                    this.scrollToBottom();

                    // Kirim ke server via Axios
                    axios.post('{{ route('chat.kirim') }}', {
                        peminjaman_id: '{{ $peminjaman->id }}',
                        isi_pesan: pesan
                    }).then(() => {
                        const lastMsg = cb.lastElementChild.querySelector('.status-mengirim');
                        if(lastMsg) {
                            lastMsg.innerText = 'Baru saja';
                            lastMsg.classList.remove('text-blue-200');
                            lastMsg.classList.add('text-blue-100');
                        }
                    }).catch(error => {
                        console.error('Error:', error);
                        const lastMsg = cb.lastElementChild.querySelector('.status-mengirim');
                        if(lastMsg) {
                            lastMsg.innerText = 'Gagal terkirim ❌';
                            lastMsg.classList.add('text-red-300');
                        }
                    }).finally(() => {
                        this.isSending = false;
                    });
                }
            }));
        });
    </script>

    <style>
        .pb-safe { padding-bottom: max(2rem, env(safe-area-inset-bottom)); }
        body { overflow: hidden; background-color: #f8fafc; }
    </style>
</x-app-layout>