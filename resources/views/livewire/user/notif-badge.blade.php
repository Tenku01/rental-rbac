<li class="relative list-none flex items-center" x-data="livewireUserNotif">
    
    <!-- 🔹 Ikon Lonceng Notifikasi -->
    <button @click="notifMenuOpen = !notifMenuOpen" @click.outside="notifMenuOpen = false" class="relative p-2 text-gray-500 hover:text-cyan-600 transition focus:outline-none bg-gray-50 rounded-full hover:bg-gray-100 mt-1">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        {{-- Indikator Bel Unread dari Livewire --}}
        @if ($totalUnreadAll > 0)
            <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative flex rounded-full h-5 w-5 bg-red-500 border-2 border-white items-center justify-center text-[10px] font-bold text-white shadow-sm">
                    {{ $totalUnreadAll }}
                </span>
            </span>
        @endif
    </button>

    <!-- 🔹 Dropdown List Chat -->
    <div x-show="notifMenuOpen" style="display: none;" x-transition.opacity class="absolute right-0 top-full mt-4 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl z-50 border border-gray-100 overflow-hidden origin-top-right">
        
        <div class="bg-gray-50/80 px-4 py-3 border-b border-gray-100 flex justify-between items-center backdrop-blur-sm">
            <h3 class="font-bold text-gray-800 text-sm">Pesan Masuk</h3>
            @if ($totalUnreadAll > 0 || count($chatList) > 0)
                <button wire:click="markAsRead" class="text-[10px] text-cyan-600 hover:underline font-medium focus:outline-none">Tandai sudah dibaca</button>
            @endif
        </div>

        <div class="max-h-80 overflow-y-auto overscroll-contain">
            @forelse($chatList as $chat)
                <a href="{{ route('pesanan.chat', $chat['id']) }}" class="block p-4 border-b border-gray-50 transition cursor-pointer group {{ $chat['unread'] > 0 ? 'bg-cyan-50/40 hover:bg-cyan-50' : 'bg-white hover:bg-gray-50' }}">
                    <div class="flex justify-between items-start mb-1.5">
                        <div class="flex-1">
                            <p class="font-bold text-sm transition truncate pr-2 {{ $chat['unread'] > 0 ? 'text-cyan-800' : 'text-gray-800 group-hover:text-cyan-600' }}">
                                Order #{{ $chat['id'] }} - {{ $chat['mobil_merek'] }}
                            </p>
                            <p class="text-xs font-medium flex items-center gap-1 mt-0.5 {{ $chat['unread'] > 0 ? 'text-cyan-700' : 'text-gray-500' }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                Sopir / Admin
                            </p>
                        </div>
                        <span class="shrink-0 text-[10px] font-bold px-2 py-1 rounded-md {{ $chat['unread'] > 0 ? 'bg-cyan-600 text-white' : 'text-gray-500 bg-gray-100' }}">
                            {{ $chat['unread'] > 0 ? 'BARU' : 'Lihat' }}
                        </span>
                    </div>

                    <div class="mt-2 flex justify-between items-center text-sm p-2 rounded-lg transition border border-transparent {{ $chat['unread'] > 0 ? 'bg-white shadow-sm' : 'bg-gray-50 group-hover:bg-white group-hover:border-cyan-100' }}">
                        <div class="truncate flex-1 pr-2">
                            <span class="font-medium {{ $chat['unread'] > 0 ? 'text-cyan-600' : 'text-gray-500' }}">{{ $chat['unread'] > 0 ? 'Pesan:' : 'Terakhir:' }}</span>
                            <span class="{{ $chat['unread'] > 0 ? 'font-semibold text-gray-800' : 'text-gray-500' }}">{{ Str::limit($chat['last_message'], 30) }}</span>
                        </div>
                        @if ($chat['unread'] > 0)
                            <span class="shrink-0 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm ml-2">
                                {{ $chat['unread'] }}
                            </span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="p-8 text-center flex flex-col items-center justify-center">
                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <p class="text-gray-500 text-sm font-medium">Belum ada pesan baru.</p>
                </div>
            @endforelse
        </div>
        
        <div class="px-4 py-2 border-t border-gray-100 bg-gray-50 text-center">
            <a href="{{ route('pesanan.saya') }}" class="text-xs text-cyan-600 font-bold hover:underline">Buka Halaman Pesanan Saya</a>
        </div>
    </div>
</li>

{{-- Script integrasi Echo & Reload Data ke Livewire --}}
@script
<script>
    Alpine.data('livewireUserNotif', () => ({
        notifMenuOpen: false,
        currentUserId: {{ auth()->id() ?? 'null' }},
        joinedChannels: [],

        init() {
            this.bindEcho();

            // Cegah duplikasi saat Livewire re-render
            Livewire.hook('morph.updated', ({ component }) => {
                if (component.name === 'user.notif-badge') {
                    this.bindEcho();
                }
            });
        },

        bindEcho() {
            if (typeof window.Echo === 'undefined') {
                setTimeout(() => this.bindEcho(), 200);
                return;
            }

            let ids = $wire.activeChatIds;

            ids.forEach(id => {
                if (!this.joinedChannels.includes(id)) {
                    window.Echo.private('chat.' + id)
                        .listen('MessageSent', (e) => {
                            // Tangkap payload terbaru (Pesan)
                            let payload = (e.pesan && typeof e.pesan === 'object') ? e.pesan : ((e.message && typeof e.message === 'object') ? e.message : e);
                            
                            // 🔹 PERBAIKAN: Gunakan pengirim_id dan paksa ke String agar validasinya akurat!
                            let senderId = String(payload.pengirim_id);
                            let myId = String(this.currentUserId);
                            
                            if (senderId !== myId) {
                                // Putar suara notif
                                let audio = new Audio('/notifikasi.mp3');
                                audio.play().catch(err => console.log('Autoplay ditolak: ', err));

                                // Panggil PHP function `loadData()` di backend untuk merefresh UI tanpa reload halaman!
                                $wire.loadData();
                            }
                        });
                    this.joinedChannels.push(id);
                }
            });
        }
    }));
</script>
@endscript