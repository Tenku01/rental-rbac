<div class="flex flex-col h-[500px] border border-gray-300 rounded-lg overflow-hidden bg-white shadow-sm">
    
    <!-- Area Riwayat Chat -->
    <div class="flex-1 p-4 overflow-y-auto bg-gray-50 flex flex-col space-y-3">
        @foreach($riwayatChat as $chat)
            <!-- 🔹 Diperbarui: sender_id menjadi pengirim_id -->
            @if($chat['pengirim_id'] == auth()->id())
                <!-- Pesan dari Sopir (Kanan) -->
                <div class="self-end max-w-[75%] bg-cyan-600 text-white p-3 rounded-l-lg rounded-br-lg shadow-sm">
                    <!-- 🔹 Diperbarui: message menjadi isi_pesan -->
                    <p class="text-sm">{{ $chat['isi_pesan'] }}</p>
                </div>
            @else
                <!-- Pesan dari Pelanggan (Kiri) -->
                <div class="self-start max-w-[75%] bg-white border border-gray-200 text-gray-800 p-3 rounded-r-lg rounded-bl-lg shadow-sm">
                    <p class="text-xs text-cyan-600 font-semibold mb-1">Pelanggan</p>
                    <!-- 🔹 Diperbarui: message menjadi isi_pesan -->
                    <p class="text-sm">{{ $chat['isi_pesan'] }}</p>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Area Input Form -->
    <div class="p-3 bg-white border-t border-gray-300">
        <form wire:submit.prevent="kirimPesan" class="flex gap-2">
            <input type="text" wire:model="pesanBaru" placeholder="Ketik pesan untuk pelanggan..." 
                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition" required>
            <button type="submit" class="px-5 py-2 bg-cyan-600 text-white font-medium rounded-md hover:bg-cyan-700 transition focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-1">
                Kirim
            </button>
        </form>
    </div>
</div>