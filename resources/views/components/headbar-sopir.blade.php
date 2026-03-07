@php
    // 1. Cari data Sopir
    $sopir = \App\Models\Sopir::where('user_id', Auth::id())->first();
    $sopirId = $sopir ? $sopir->id : 0;

    // 2. Ambil pesanan aktif
    $activeChats = $sopirId ? \App\Models\Peminjaman::with(['mobil', 'user'])
                        ->where('sopir_id', $sopirId)
                        ->whereIn('status', ['pembayaran dp', 'sudah dibayar lunas', 'berlangsung'])
                        ->get() : collect([]);

    $activeChatIds = $activeChats->pluck('id')->values()->toArray();

    // 3. Ambil data pesan dari DATABASE sesungguhnya & Lakukan Sorting
    $chatList = [];
    $latestMessagesData = [];
    $totalUnreadAll = 0;

    foreach($activeChats as $chat) {
        $lm = \App\Models\Message::where('peminjaman_id', $chat->id)->latest()->first();
        
        // Hitung pesan yang BELUM DIBACA dan BUKAN dikirim oleh kita sendiri
        $unreadCount = \App\Models\Message::where('peminjaman_id', $chat->id)
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', false)
            ->count();
            
        $chatList[] = (object) [
            'id' => $chat->id,
            'mobil' => $chat->mobil,
            'user' => $chat->user,
            'last_message' => $lm,
            'last_time' => $lm ? $lm->created_at : $chat->created_at,
            'unread' => $unreadCount
        ];

        $latestMessagesData[$chat->id] = $lm ? $lm->message : 'Belum ada obrolan...';
        $latestMessagesData['unread_'.$chat->id] = $unreadCount;
        $totalUnreadAll += $unreadCount;
    }

    // 4. URUTKAN DAFTAR CHAT (Unread di atas, lalu berdasarkan waktu terbaru)
    usort($chatList, function($a, $b) {
        if ($a->unread !== $b->unread) {
            return $b->unread <=> $a->unread;
        }
        return $b->last_time <=> $a->last_time;
    });
@endphp

{{-- Logika Inline Alpine: Dijamin 100% berjalan tanpa menunggu Script Bawah --}}
<header x-data="{
        userMenuOpen: false,
        notifMenuOpen: false,
        pesanBelumDibaca: {{ $totalUnreadAll }},
        activeChannels: {{ json_encode($activeChatIds) }},
        latestMessages: {{ json_encode($latestMessagesData) }},
        currentUserId: {{ auth()->id() }},
        
        init() {
            const setupEcho = () => {
                if (window.Echo) {
                    this.activeChannels.forEach(id => {
                        window.Echo.private('chat.' + id)
                            .listen('MessageSent', (e) => {
                                // Deteksi format payload (mencegah error dari tipe data Reverb)
                                let payload = (e.message && typeof e.message === 'object') ? e.message : e;
                                
                                if (payload.sender_id != this.currentUserId) {
                                    // 1. Tambah jumlah unread bel
                                    this.pesanBelumDibaca++;
                                    
                                    // 2. Ganti teks pesan terbaru & tambah unread di daftar
                                    this.latestMessages[id] = payload.message;
                                    this.latestMessages['unread_' + id]++;
                                }
                            });
                    });
                } else {
                    setTimeout(setupEcho, 200);
                }
            };
            setupEcho();
        }
    }" 
    class="bg-white shadow-lg z-30 relative">
    
    <div class="flex items-center justify-between h-16 px-6 relative z-40">
        
        {{-- Tombol Toggle Sidebar --}}
        <button @click="sidebarOpen = true" x-show="!sidebarOpen" class="text-gray-500 lg:hidden focus:outline-none hover:text-cyan-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
        
        <div class="text-lg font-bold text-gray-700 hidden lg:block">
            @yield('title', 'Dashboard Sopir')
        </div>

        <div class="flex-1 lg:hidden"></div>

        <div class="flex items-center gap-4 ml-auto">
            
            {{-- 🔹 Ikon Notifikasi Chat & Dropdown --}}
            <div class="relative" @click.outside="notifMenuOpen = false">
                <button @click="notifMenuOpen = !notifMenuOpen" 
                        class="relative p-2 text-gray-500 hover:text-cyan-600 transition focus:outline-none bg-gray-50 rounded-full hover:bg-gray-100" 
                        title="Pesan">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    
                    {{-- Indikator Bel Unread (Otomatis dari Alpine/Database) --}}
                    <span x-cloak x-show="pesanBelumDibaca > 0" class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative flex rounded-full h-5 w-5 bg-red-500 border-2 border-white items-center justify-center text-[10px] font-bold text-white shadow-sm">
                            <span x-text="pesanBelumDibaca"></span>
                        </span>
                    </span>
                </button>

                {{-- Dropdown Pesan Aktif --}}
                <div x-show="notifMenuOpen" 
                     style="display: none;"
                     x-transition:enter="transition ease-out duration-150" 
                     x-transition:enter-start="transform opacity-0 scale-95 translate-y-2" 
                     x-transition:enter-end="transform opacity-100 scale-100 translate-y-0" 
                     x-transition:leave="transition ease-in duration-100" 
                     x-transition:leave-start="transform opacity-100 scale-100 translate-y-0" 
                     x-transition:leave-end="transform opacity-0 scale-95 translate-y-2" 
                     class="absolute right-0 mt-3 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl z-50 border border-gray-100 overflow-hidden">
                    
                    <div class="bg-gray-50/80 px-4 py-3 border-b border-gray-100 flex justify-between items-center backdrop-blur-sm">
                        <h3 class="font-bold text-gray-800 text-sm">Obrolan Aktif</h3>
                        <span class="text-[10px] font-bold uppercase tracking-wider bg-cyan-100 text-cyan-700 px-2 py-1 rounded-full">Live</span>
                    </div>

                    <div class="max-h-80 overflow-y-auto overscroll-contain">
                        @forelse($chatList as $chat)
                            <a href="{{ route('sopir.chat', $chat->id) }}" 
                               class="block p-4 border-b transition cursor-pointer group"
                               :class="latestMessages['unread_{{ $chat->id }}'] > 0 ? 'bg-cyan-50/40 border-cyan-100 hover:bg-cyan-50' : 'bg-white border-gray-50 hover:bg-gray-50'">
                                
                                <div class="flex justify-between items-start mb-1.5">
                                    <div class="flex-1">
                                        <p class="font-bold text-sm transition truncate pr-2"
                                           :class="latestMessages['unread_{{ $chat->id }}'] > 0 ? 'text-cyan-800' : 'text-gray-800 group-hover:text-cyan-600'">
                                            #{{ $chat->id }} - {{ $chat->mobil->merek ?? 'Mobil' }}
                                        </p>
                                        <p class="text-xs font-medium flex items-center gap-1 mt-0.5"
                                           :class="latestMessages['unread_{{ $chat->id }}'] > 0 ? 'text-cyan-700' : 'text-gray-500'">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            {{ $chat->user->name ?? 'Pelanggan' }}
                                        </p>
                                    </div>
                                    <span class="shrink-0 text-[10px] font-bold px-2 py-1 rounded-md"
                                          :class="latestMessages['unread_{{ $chat->id }}'] > 0 ? 'bg-cyan-600 text-white' : 'text-gray-500 bg-gray-100'">
                                        <span x-text="latestMessages['unread_{{ $chat->id }}'] > 0 ? 'BARU' : 'Lihat'"></span>
                                    </span>
                                </div>
                                
                                {{-- Area Teks Realtime --}}
                                <div class="mt-2 flex justify-between items-center text-sm p-2 rounded-lg transition border border-transparent"
                                     :class="latestMessages['unread_{{ $chat->id }}'] > 0 ? 'bg-white shadow-sm' : 'bg-gray-50 group-hover:bg-white group-hover:border-cyan-100'">
                                    <div class="truncate flex-1 pr-2">
                                        <span class="font-medium" :class="latestMessages['unread_{{ $chat->id }}'] > 0 ? 'text-cyan-600' : 'text-gray-500'" x-text="latestMessages['unread_{{ $chat->id }}'] > 0 ? 'Baru:' : 'Terakhir:'"></span> 
                                        <span :class="latestMessages['unread_{{ $chat->id }}'] > 0 ? 'font-semibold text-gray-800' : 'text-gray-500'" x-text="latestMessages['{{ $chat->id }}']"></span>
                                    </div>
                                    
                                    {{-- Badge Unread per Chat --}}
                                    <span x-cloak x-show="latestMessages['unread_{{ $chat->id }}'] > 0" 
                                          x-text="latestMessages['unread_{{ $chat->id }}']" 
                                          class="shrink-0 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm ml-2">
                                    </span>
                                </div>
                            </a>
                        @empty
                            <div class="p-8 text-center flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                <p class="text-gray-500 text-sm font-medium">Tidak ada obrolan aktif</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- User Profile & Logout --}}
            <div class="relative" @click.outside="userMenuOpen = false">
                <button @click="userMenuOpen = !userMenuOpen" 
                        class="flex items-center space-x-2 focus:outline-none p-1 rounded-full hover:bg-gray-100 transition">
                    <span class="text-sm font-medium text-gray-600 hidden sm:inline-block">
                        {{ Auth::user()->name ?? 'Pengemudi' }}
                    </span>
                    <div class="w-10 h-10 rounded-full bg-cyan-600 flex items-center justify-center text-white text-base font-bold shadow-md border-2 border-white">
                        {{ mb_substr(Auth::user()->name ?? 'P', 0, 1) }}
                    </div>
                </button>

                <div x-show="userMenuOpen" 
                     style="display: none;"
                     x-transition:enter="transition ease-out duration-100" 
                     x-transition:enter-start="transform opacity-0 scale-95" 
                     x-transition:enter-end="transform opacity-100 scale-100" 
                     x-transition:leave="transition ease-in duration-75" 
                     x-transition:leave-start="transform opacity-100 scale-100" 
                     x-transition:leave-end="transform opacity-0 scale-95" 
                     class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl py-2 z-50 border border-gray-100 origin-top-right">
                     
                    <div class="border-t border-gray-100 my-1"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition font-medium">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>