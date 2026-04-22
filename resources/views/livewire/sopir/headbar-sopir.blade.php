<header x-data="livewireHeadbar" class="bg-white shadow-lg z-30 relative">

    <div class="flex items-center justify-between h-16 px-6 relative z-40">

        {{-- Tombol Toggle Sidebar (Diasumsikan ada sidebarOpen di parent) --}}
        <button @click="sidebarOpen = true" x-show="!sidebarOpen"
            class="text-gray-500 lg:hidden focus:outline-none hover:text-cyan-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                </path>
            </svg>
        </button>

        <div class="text-lg font-bold text-gray-700 hidden lg:block">
            Dashboard Sopir
        </div>

        <div class="flex-1 lg:hidden"></div>

        <div class="flex items-center gap-4 ml-auto">

            {{-- 🔹 Ikon Notifikasi Chat & Dropdown --}}
            <div class="relative" @click.outside="notifMenuOpen = false">
                <button @click="notifMenuOpen = !notifMenuOpen"
                    class="relative p-2 text-gray-500 hover:text-cyan-600 transition focus:outline-none bg-gray-50 rounded-full hover:bg-gray-100"
                    title="Pesan">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                        </path>
                    </svg>

                    {{-- Indikator Bel Unread (Langsung dari Livewire) --}}
                    @if ($totalUnreadAll > 0)
                        <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span
                                class="relative flex rounded-full h-5 w-5 bg-red-500 border-2 border-white items-center justify-center text-[10px] font-bold text-white shadow-sm">
                                {{ $totalUnreadAll }}
                            </span>
                        </span>
                    @endif
                </button>

                {{-- Dropdown Pesan Aktif --}}
                <div x-show="notifMenuOpen" style="display: none;" x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="transform opacity-0 scale-95 translate-y-2"
                    x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="transform opacity-0 scale-95 translate-y-2"
                    class="absolute right-0 mt-3 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl z-50 border border-gray-100 overflow-hidden">

                    <div
                        class="bg-gray-50/80 px-4 py-3 border-b border-gray-100 flex justify-between items-center backdrop-blur-sm">
                        <h3 class="font-bold text-gray-800 text-sm">Obrolan Aktif</h3>
                        <span
                            class="text-[10px] font-bold uppercase tracking-wider bg-cyan-100 text-cyan-700 px-2 py-1 rounded-full">Live</span>
                    </div>

                    <div class="max-h-80 overflow-y-auto overscroll-contain">
                        @forelse($chatList as $chat)
                            <a href="{{ route('sopir.chat', $chat['id']) }}"
                                class="block p-4 border-b transition cursor-pointer group {{ $chat['unread'] > 0 ? 'bg-cyan-50/40 border-cyan-100 hover:bg-cyan-50' : 'bg-white border-gray-50 hover:bg-gray-50' }}">

                                <div class="flex justify-between items-start mb-1.5">
                                    <div class="flex-1">
                                        <p
                                            class="font-bold text-sm transition truncate pr-2 {{ $chat['unread'] > 0 ? 'text-cyan-800' : 'text-gray-800 group-hover:text-cyan-600' }}">
                                            #{{ $chat['id'] }} - {{ $chat['mobil_merek'] }}
                                        </p>
                                        <p
                                            class="text-xs font-medium flex items-center gap-1 mt-0.5 {{ $chat['unread'] > 0 ? 'text-cyan-700' : 'text-gray-500' }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                </path>
                                            </svg>
                                            {{ $chat['user_name'] }}
                                        </p>
                                    </div>
                                    <span
                                        class="shrink-0 text-[10px] font-bold px-2 py-1 rounded-md {{ $chat['unread'] > 0 ? 'bg-cyan-600 text-white' : 'text-gray-500 bg-gray-100' }}">
                                        {{ $chat['unread'] > 0 ? 'BARU' : 'Lihat' }}
                                    </span>
                                </div>

                                <div
                                    class="mt-2 flex justify-between items-center text-sm p-2 rounded-lg transition border border-transparent {{ $chat['unread'] > 0 ? 'bg-white shadow-sm' : 'bg-gray-50 group-hover:bg-white group-hover:border-cyan-100' }}">
                                    <div class="truncate flex-1 pr-2">
                                        <span
                                            class="font-medium {{ $chat['unread'] > 0 ? 'text-cyan-600' : 'text-gray-500' }}">{{ $chat['unread'] > 0 ? 'Belum dibaca:' : 'Terakhir:' }}</span>
                                        <span
                                            class="{{ $chat['unread'] > 0 ? 'font-semibold text-gray-800' : 'text-gray-500' }}">{{ $chat['last_message'] }}</span>
                                    </div>

                                    @if ($chat['unread'] > 0)
                                        <span
                                            class="shrink-0 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm ml-2">
                                            {{ $chat['unread'] }}
                                        </span>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="p-8 text-center flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                    </path>
                                </svg>
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
                    <div
                        class="w-10 h-10 rounded-full bg-cyan-600 flex items-center justify-center text-white text-base font-bold shadow-md border-2 border-white">
                        {{ mb_substr(Auth::user()->name ?? 'P', 0, 1) }}
                    </div>
                </button>

                <div x-show="userMenuOpen" style="display: none;"
                    class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl py-2 z-50 border border-gray-100 origin-top-right">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition font-medium">Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

{{-- Script integrasi Livewire 3 & Alpine JS --}}
@script
    <script>
        Alpine.data('livewireHeadbar', () => ({
            userMenuOpen: false,
            notifMenuOpen: false,
            currentUserId: {{ auth()->id() }},
            joinedChannels: [],

            init() {
                // Pertama kali dimuat, daftarkan echo
                this.bindEcho();

                // Cegah duplikasi event saat Livewire me-refresh data (Mekanisme Livewire 3)
                Livewire.hook('morph.updated', ({
                    component
                }) => {
                    if (component.name === 'sopir.headbar-sopir') {
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
                                // 🔹 Diperbarui: Fallback penangkapan payload dari event
                                let payload = (e.pesan && typeof e.pesan === 'object') ? e.pesan : ((e.message && typeof e.message === 'object') ? e.message : e);
                                
                                // 🔹 Diperbarui: sender_id -> pengirim_id
                                if (payload.pengirim_id != this.currentUserId) {

                                    // === FITUR SUARA NOTIFIKASI AKTIF ===
                                    // Mengambil file dari public/notifikasi.mp3
                                    let audio = new Audio('/notifikasi.mp3');
                                    audio.play().catch(err => console.log(
                                        'Autoplay ditolak browser: ', err));

                                    // Trigger Livewire untuk me-reload ulang dari database secara instan!
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