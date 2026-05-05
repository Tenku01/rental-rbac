<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8" wire:poll.2s="refreshData">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 h-[650px] flex overflow-hidden">
        
        {{-- Sidebar Kiri --}}
        <div class="w-1/3 border-r border-gray-200 bg-gray-50 flex flex-col">
            <div class="p-5 border-b border-gray-200 bg-white">
                <h3 class="font-bold text-gray-800 text-lg">Livechat Tamu</h3>
                <p class="text-xs text-gray-500 mt-1">Status: Polling Aktif (3s)</p>
            </div>
            
            <div class="overflow-y-auto flex-1 p-3 space-y-2 custom-scrollbar">
                @forelse($chatSessions as $session)
                    <button wire:click="selectChat('{{ $session }}')" 
                            wire:key="session-{{ $session }}"
                            class="w-full text-left p-3 rounded-xl transition-all duration-200 {{ $activeSessionId === $session ? 'bg-cyan-100 border-cyan-300 shadow-sm' : 'bg-white hover:bg-gray-100 border-transparent' }} border">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-cyan-600 text-white flex items-center justify-center font-bold text-xs shrink-0">GS</div>
                            <div class="overflow-hidden">
                                <h4 class="font-bold text-sm text-gray-900 truncate">Pengunjung</h4>
                                <p class="text-xs text-cyan-600 font-medium mt-0.5">ID: {{ strtoupper(substr($session, -6)) }}</p>
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="text-center mt-10">
                        <p class="text-gray-400 text-sm">Belum ada obrolan.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Area Kanan --}}
        <div class="w-2/3 flex flex-col bg-white">
            @if($activeSessionId)
                <div class="p-4 border-b border-gray-200 bg-white flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center font-bold text-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800">ID: {{ strtoupper(substr($activeSessionId, -6)) }}</h3>
                        </div>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-5 bg-slate-50 space-y-4 custom-scrollbar" id="admin-chat-body">
                    @foreach($messages as $index => $msg)
                        <div wire:key="msg-{{ $index }}-{{ $msg['id'] }}" class="flex {{ $msg['pengirim_id'] === null ? 'justify-start' : 'justify-end' }}">
                            <div class="p-3.5 rounded-2xl shadow-sm max-w-[80%] text-sm {{ $msg['pengirim_id'] === null ? 'bg-white border border-gray-200 text-gray-800 rounded-tl-none' : 'bg-cyan-600 text-white rounded-tr-none' }}">
                                {{ $msg['isi_pesan'] }}
                                <div class="text-[10px] mt-1 text-right opacity-70">{{ $msg['waktu'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="p-4 bg-white border-t border-gray-200">
                    <form wire:submit.prevent="sendMessage" class="flex gap-2 relative">
                        <input type="text" wire:model="newMessage" placeholder="Balas pengunjung..." required
                               class="flex-1 bg-gray-50 border border-gray-300 rounded-xl pl-4 pr-12 py-3 text-sm outline-none">
                        <button type="submit" class="absolute right-2 top-2 bottom-2 bg-cyan-600 text-white px-4 rounded-lg">
                            Kirim
                        </button>
                    </form>
                </div>
            @else
                <div class="flex-1 flex flex-col items-center justify-center text-gray-400 bg-slate-50">
                    <p class="text-sm">Pilih obrolan untuk mulai membalas.</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            const chatBody = document.getElementById('admin-chat-body');
            const scrollToBottom = () => {
                const el = document.getElementById('admin-chat-body');
                if (el) el.scrollTop = el.scrollHeight;
            };
            Livewire.on('scroll-to-bottom', () => { setTimeout(scrollToBottom, 50); });
            scrollToBottom();
        });
    </script>
</div>