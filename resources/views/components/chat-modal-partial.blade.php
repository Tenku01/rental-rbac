<div x-show="chatModalOpen" 
     style="display: none;"
     class="fixed inset-0 z-[80] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    
    <div @click.away="chatModalOpen = false"
         class="bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col h-[550px] max-h-[90vh] m-4 overflow-hidden transform transition-all"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
        
        <!-- Header Chat -->
        <div class="bg-blue-600 px-4 py-3 border-b flex justify-between items-center shadow-sm z-10">
            <div class="flex items-center gap-3">
                <div class="bg-white/20 p-2 rounded-full">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-bold text-sm">Chat Sopir</h3>
                    <p class="text-blue-100 text-xs">Pesanan: {{ $item->mobil->merek ?? 'Mobil' }}</p>
                </div>
            </div>
            <button @click="chatModalOpen = false"
                class="text-blue-100 hover:text-white transition bg-blue-700/50 hover:bg-blue-700 p-1.5 rounded-full focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <!-- Area Pesan -->
        <div id="kotak-chat-{{ $item->id }}"
             class="flex-1 p-4 overflow-y-auto bg-gray-50 flex flex-col space-y-3 scroll-smooth">
            
            @php
                // Mengambil riwayat pesan lama dari database
                $riwayat = \App\Models\Message::where('peminjaman_id', $item->id)
                    ->orderBy('created_at', 'asc')
                    ->get();
            @endphp
            
            @forelse($riwayat as $chat)
                @if($chat->sender_id == auth()->id())
                    <!-- Pesan Pelanggan (Kanan) -->
                    <div class="self-end max-w-[80%] bg-green-500 text-white p-3 rounded-l-2xl rounded-tr-2xl rounded-br-sm shadow-sm mt-1">
                        <p class="text-sm">{{ $chat->message }}</p>
                        <span class="text-[10px] text-green-100 flex justify-end mt-1">
                            {{ \Carbon\Carbon::parse($chat->created_at)->format('H:i') }}
                        </span>
                    </div>
                @else
                    <!-- Pesan Sopir (Kiri) -->
                    <div class="self-start max-w-[80%] bg-white border border-gray-100 p-3 rounded-r-2xl rounded-tl-2xl rounded-bl-sm shadow-sm mt-1 text-gray-800">
                        <p class="text-xs text-blue-600 font-bold mb-0.5">Sopir</p>
                        <p class="text-sm">{{ $chat->message }}</p>
                        <span class="text-[10px] text-gray-400 flex justify-start mt-1">
                            {{ \Carbon\Carbon::parse($chat->created_at)->format('H:i') }}
                        </span>
                    </div>
                @endif
            @empty
                <!-- Tampilan Jika Chat Masih Kosong -->
                <div class="empty-message flex-1 flex flex-col items-center justify-center text-center opacity-70">
                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                    <p class="text-gray-500 text-sm">Belum ada pesan.<br>Kirim pesan untuk mulai ngobrol dengan sopir.</p>
                </div>
            @endforelse
        </div>

        <!-- Form Input -->
        <form @submit.prevent="kirimPesan" class="p-3 bg-white border-t border-gray-200 flex gap-2 items-end shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
            <textarea 
                x-model="pesanBaru"
                rows="1"
                required
                placeholder="Ketik pesan Anda..."
                class="flex-1 px-4 py-2.5 bg-gray-100 border-transparent focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 rounded-2xl resize-none text-sm transition"
                @keydown.enter.prevent="if(!$event.shiftKey) kirimPesan()"
            ></textarea>
            
            <button type="submit"
                class="w-11 h-11 rounded-full bg-blue-600 text-white flex items-center justify-center hover:bg-blue-700 transition shadow-sm shrink-0 focus:ring-2 focus:ring-offset-1 focus:ring-blue-500">
                <svg class="w-5 h-5 ml-0.5 transform rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
            </button>
        </form>
    </div>
</div>