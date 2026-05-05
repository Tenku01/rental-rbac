<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 h-[650px] flex overflow-hidden">
        
        {{-- Sidebar --}}
        <div class="w-1/3 border-r border-gray-200 bg-gray-50 flex flex-col">
            <div class="p-5 border-b border-gray-200 bg-white">
                <h3 class="font-bold text-gray-800 text-lg">Livechat Tamu</h3>
                <p class="text-xs text-gray-500 mt-1">Balas pesan pengunjung website</p>
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
                        <p class="text-gray-400 text-sm">Belum ada obrolan masuk.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Area Chat --}}
        <div class="w-2/3 flex flex-col bg-white">
            @if($activeSessionId)
                <div class="p-4 border-b border-gray-200 flex justify-between items-center shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center font-bold text-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800">Pengunjung - {{ strtoupper(substr($activeSessionId, -6)) }}</h3>
                            <span class="flex items-center gap-1 text-[10px] text-green-500 font-semibold uppercase">
                                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span> Online
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Kontainer Pesan --}}
                <div class="flex-1 overflow-y-auto p-5 bg-slate-50 space-y-4 custom-scrollbar" id="admin-chat-body" wire:key="chat-window-{{ $activeSessionId }}">
                    @foreach($messages as $index => $msg)
                        <div wire:key="msg-{{ $index }}-{{ $msg['id'] ?? rand() }}" class="flex {{ $msg['pengirim_id'] === null ? 'justify-start' : 'justify-end' }}">
                            <div class="p-3.5 rounded-2xl shadow-sm max-w-[80%] text-sm {{ $msg['pengirim_id'] === null ? 'bg-white border border-gray-200 text-gray-800 rounded-tl-none' : 'bg-cyan-600 text-white rounded-tr-none' }}">
                                {{ $msg['isi_pesan'] }}
                                <div class="text-[10px] mt-1 text-right {{ $msg['pengirim_id'] === null ? 'text-gray-400' : 'text-cyan-200' }}">
                                    {{ $msg['waktu'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="p-4 bg-white border-t border-gray-200">
                    <form wire:submit.prevent="sendMessage" class="flex gap-2 relative">
                        <input type="text" wire:model="newMessage" placeholder="Ketik balasan..." required
                               class="flex-1 bg-gray-50 border border-gray-300 rounded-xl pl-4 pr-12 py-3 focus:ring-cyan-500 text-sm outline-none">
                        <button type="submit" class="absolute right-2 top-2 bottom-2 bg-cyan-600 hover:bg-cyan-700 text-white px-4 rounded-lg">
                            <svg class="w-4 h-4 transform rotate-90" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path></svg>
                        </button>
                    </form>
                </div>
            @else
                <div class="flex-1 flex flex-col items-center justify-center text-gray-400 bg-slate-50">
                    <h3 class="text-lg font-bold text-gray-700">Aka Livechat Pusat</h3>
                    <p class="text-sm">Pilih obrolan untuk mulai membalas.</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            const chatBody = document.getElementById('admin-chat-body');
            const scrollToBottom = () => {
                if (chatBody) {
                    chatBody.scrollTop = chatBody.scrollHeight;
                }
            };

            // Dengarkan perintah scroll dari server
            Livewire.on('scroll-to-bottom', () => {
                setTimeout(scrollToBottom, 100);
            });

            // Jalankan sekali saat init
            scrollToBottom();
        });
    </script>
</div>