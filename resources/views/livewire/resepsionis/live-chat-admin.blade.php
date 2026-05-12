<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8" wire:poll.3s="refreshData">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 h-[750px] flex flex-col overflow-hidden">
        
        {{-- Bagian Atas: Navigasi & Daftar Pengunjung (Sebelumnya Sidebar) --}}
        <div class="w-full border-b border-gray-200 bg-gray-50 flex flex-col shrink-0">
            <div class="p-4 border-b border-gray-200 bg-white flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-gray-800 text-lg">Livechat Tamu</h3>
                    <p class="text-[10px] text-gray-500">Status: Polling Aktif</p>
                </div>
                
                {{-- Tab Navigation --}}
                <div class="flex bg-gray-100 p-1 rounded-lg w-full md:w-auto">
                    <button wire:click="setTab('semua')" 
                        class="px-4 text-[10px] font-bold py-1.5 rounded-md transition-all {{ $activeTab === 'semua' ? 'bg-white shadow-sm text-cyan-600' : 'text-gray-500 hover:text-gray-700' }}">
                        SEMUA
                    </button>
                    <button wire:click="setTab('belum_dibaca')" 
                        class="px-4 text-[10px] font-bold py-1.5 rounded-md transition-all {{ $activeTab === 'belum_dibaca' ? 'bg-white shadow-sm text-cyan-600' : 'text-gray-500 hover:text-gray-700' }}">
                        BELUM DIBACA
                    </button>
                    <button wire:click="setTab('sudah_dibaca')" 
                        class="px-4 text-[10px] font-bold py-1.5 rounded-md transition-all {{ $activeTab === 'sudah_dibaca' ? 'bg-white shadow-sm text-cyan-600' : 'text-gray-500 hover:text-gray-700' }}">
                        SUDAH DIBACA
                    </button>
                </div>
            </div>
            
            {{-- Daftar Sesi Horizontal --}}
            <div class="overflow-x-auto flex p-3 space-x-3 custom-scrollbar-h bg-gray-50 items-center min-h-[100px]">
                @forelse($filteredSessions as $session)
                    <button wire:click="selectChat('{{ $session['session_id'] }}')" 
                            wire:key="session-{{ $session['session_id'] }}"
                            class="flex-none w-48 text-left p-3 rounded-xl transition-all duration-200 border flex items-center gap-3 {{ $activeSessionId === $session['session_id'] ? 'bg-cyan-100 border-cyan-300 shadow-sm' : 'bg-white hover:bg-gray-100 border-transparent' }}">
                        
                        {{-- Avatar --}}
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs shrink-0 relative {{ $session['unread_count'] > 0 ? 'bg-cyan-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                            GS
                            @if($session['unread_count'] > 0)
                                <span class="absolute -top-1 -right-1 flex h-4 w-4">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-4 w-4 bg-cyan-500 text-[9px] text-white items-center justify-center font-bold">
                                        {{ $session['unread_count'] }}
                                    </span>
                                </span>
                            @endif
                        </div>

                        <div class="overflow-hidden">
                            <h4 class="font-bold text-xs {{ $session['unread_count'] > 0 ? 'text-cyan-700' : 'text-gray-900' }} truncate">Pengunjung</h4>
                            <p class="text-[9px] text-gray-500 truncate">ID: {{ strtoupper(substr($session['session_id'], -6)) }}</p>
                        </div>

                        @if($session['unread_count'] > 0)
                            <div class="ml-auto w-2 h-2 bg-cyan-500 rounded-full shrink-0 shadow-sm"></div>
                        @endif
                    </button>
                @empty
                    <div class="w-full text-center py-4">
                        <p class="text-gray-400 text-[10px] italic">Tidak ada percakapan {{ str_replace('_', ' ', $activeTab) }}.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Area Bawah: Ruang Obrolan --}}
        <div class="flex-1 flex flex-col bg-white overflow-hidden">
            @if($activeSessionId)
                {{-- Info Header Room --}}
                <div class="px-6 py-3 border-b border-gray-100 bg-white flex justify-between items-center shadow-sm shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-sm">Room: {{ strtoupper(substr($activeSessionId, -6)) }}</h3>
                        </div>
                    </div>
                </div>

                {{-- Kontainer Pesan --}}
                <div class="flex-1 overflow-y-auto p-6 bg-slate-50 space-y-4 custom-scrollbar relative" id="admin-chat-body">
                    @php $lastDate = ''; @endphp
                    @foreach($messages as $index => $msg)
                        
                        {{-- Header Tanggal Mengambang --}}
                        @if($lastDate !== $msg['tanggal_grup'])
                            <div class="flex justify-center my-4 sticky top-2 z-10">
                                <span class="px-4 py-1.5 text-[10px] font-bold text-gray-500 bg-white/90 backdrop-blur-sm border border-gray-200 rounded-full shadow-sm">
                                    {{ $msg['tanggal_grup'] }}
                                </span>
                            </div>
                            @php $lastDate = $msg['tanggal_grup']; @endphp
                        @endif

                        <div wire:key="msg-{{ $index }}-{{ $msg['id'] }}" class="flex {{ $msg['pengirim_id'] === null ? 'justify-start' : 'justify-end' }}">
                            <div class="p-3.5 rounded-2xl shadow-sm max-w-[75%] text-sm {{ $msg['pengirim_id'] === null ? 'bg-white border border-gray-200 text-gray-800 rounded-tl-none' : 'bg-cyan-600 text-white rounded-tr-none' }}">
                                {{ $msg['isi_pesan'] }}
                                <div class="text-[10px] mt-1 flex items-center justify-end gap-1 {{ $msg['pengirim_id'] === null ? 'text-gray-400' : 'text-cyan-100' }}">
                                    {{ $msg['waktu'] }}
                                    @if($msg['pengirim_id'] !== null)
                                        <svg class="w-3 h-3 {{ $msg['sudah_dibaca'] ? 'text-white' : 'text-cyan-300' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293l-4.243 4.243a1 1 0 01-1.414 0l-2.121-2.121a1 1 0 011.414-1.414l1.414 1.414 3.536-3.536a1 1 0 011.414 1.414z"></path></svg>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Input Chat --}}
                <div class="p-4 bg-white border-t border-gray-200 shrink-0">
                    <form wire:submit.prevent="sendMessage" class="flex gap-2 relative">
                        <input type="text" wire:model="newMessage" placeholder="Ketik pesan balasan..." required
                               class="flex-1 bg-gray-50 border border-gray-300 rounded-xl pl-4 pr-12 py-3 text-sm focus:ring-2 focus:ring-cyan-500 outline-none transition-all">
                        <button type="submit" class="absolute right-2 top-2 bottom-2 bg-cyan-600 hover:bg-cyan-700 text-white px-5 rounded-lg font-medium transition-colors">
                            Kirim
                        </button>
                    </form>
                </div>
            @else
                <div class="flex-1 flex flex-col items-center justify-center text-gray-400 bg-slate-50">
                    <div class="p-6 bg-white rounded-full shadow-sm border border-gray-100 mb-4 text-cyan-200">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <p class="text-sm font-medium">Pilih percakapan dari daftar di atas untuk membalas.</p>
                </div>
            @endif
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        
        .custom-scrollbar-h::-webkit-scrollbar { height: 4px; }
        .custom-scrollbar-h::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>

    <script>
        document.addEventListener('livewire:initialized', () => {
            const scrollToBottom = () => {
                const el = document.getElementById('admin-chat-body');
                if (el) el.scrollTop = el.scrollHeight;
            };
            Livewire.on('scroll-to-bottom', () => { setTimeout(scrollToBottom, 50); });
            scrollToBottom();
        });
    </script>
</div>