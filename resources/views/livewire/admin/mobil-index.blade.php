<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            
            <!-- Header & Action -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <h2 class="text-2xl font-bold text-gray-800">Manajemen Armada</h2>
                <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition duration-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Tambah Mobil
                </button>
            </div>

            <!-- Search & Filter -->
            <div class="mb-6 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input wire:model.live="search" type="text" placeholder="Cari Plat Nomor, Merek, atau Tipe..." class="pl-10 w-full md:w-1/3 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
            </div>

            <!-- Flash Message -->
            @if (session()->has('message'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4 rounded shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('message') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            @if (session()->has('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Tabel Data -->
            <div class="overflow-x-auto rounded-lg shadow ring-1 ring-black ring-opacity-5">
                <table class="min-w-full bg-white divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mobil & Plat</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Spesifikasi</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Harga/Hari</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($mobils as $mobil)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($mobil->foto)
                                    <div class="flex-shrink-0 h-16 w-24">
                                        <img class="h-16 w-24 object-cover rounded-md shadow-sm border border-gray-200" src="{{ asset('storage/' . $mobil->foto) }}" alt="{{ $mobil->merek }}">
                                    </div>
                                @else
                                    <div class="h-16 w-24 bg-gray-100 rounded-md flex items-center justify-center text-gray-400 text-xs border border-gray-200">
                                        No Image
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $mobil->merek }} {{ $mobil->tipe }}</div>
                                <div class="text-xs font-mono bg-gray-100 px-2 py-0.5 rounded inline-block mt-1 text-gray-600 border border-gray-200">
                                    {{ $mobil->id }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-50 text-blue-800 border border-blue-100">
                                    {{ ucfirst($mobil->transmisi) }}
                                </span>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-50 text-indigo-800 border border-indigo-100 ml-1">
                                    {{ $mobil->kursi }} Kursi
                                </span>
                                <div class="text-xs text-gray-500 mt-1 capitalize">{{ $mobil->warna }}</div>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">Rp {{ number_format($mobil->harga, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @php
                                    $statusClasses = match($mobil->status) {
                                        'tersedia' => 'bg-green-100 text-green-800 border-green-200',
                                        'disewa' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'pemeliharaan' => 'bg-red-100 text-red-800 border-red-200',
                                        'dibersihkan' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                        default => 'bg-gray-100 text-gray-800 border-gray-200'
                                    };
                                @endphp
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full border {{ $statusClasses }} uppercase tracking-wide">
                                    {{ $mobil->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-medium">
                                <div class="flex justify-center space-x-3">
                                    <button wire:click="edit('{{ $mobil->id }}')" class="text-blue-600 hover:text-blue-900 transition transform hover:scale-110" title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button wire:confirm="Yakin ingin menghapus mobil {{ $mobil->merek }} ini?" wire:click="delete('{{ $mobil->id }}')" class="text-red-600 hover:text-red-900 transition transform hover:scale-110" title="Hapus">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                    <p>Belum ada data armada.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-6">
                {{ $mobils->links() }}
            </div>
        </div>
    </div>

    <!-- MODERN MODAL FORM -->
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            
            <!-- Background Backdrop with Blur -->
            <div class="fixed inset-0 bg-gray-900 bg-opacity-60 transition-opacity backdrop-blur-sm" 
                 aria-hidden="true" 
                 wire:click="$set('showModal', false)">
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <!-- Modal Content -->
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-gray-100">
                
                <!-- Modal Header -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-gray-800" id="modal-title">
                        {{ $isEditMode ? 'Edit Data Armada' : 'Tambah Armada Baru' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600 transition focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}">
                    <div class="bg-white px-6 py-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            
                            <!-- 1. PLAT NOMOR (Primary Key) - Full Width -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    Plat Nomor (ID Kendaraan) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 font-bold sm:text-sm">🇮🇩</span>
                                    </div>
                                    <input wire:model="plat_nomor" type="text" placeholder="B 1234 XYZ" 
                                           class="pl-10 uppercase w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 font-mono text-lg tracking-wide placeholder-gray-300"
                                           {{-- Jika sedang edit, Anda bisa membuatnya readonly jika mau, tapi di sini dibiarkan editable --}}
                                    >
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Gunakan Huruf Kapital dan Spasi. Contoh: <span class="font-mono bg-gray-100 px-1 rounded">B 1234 XYZ</span></p>
                                @error('plat_nomor') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- 2. Merek -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Merek Mobil</label>
                                <input wire:model="merek" type="text" placeholder="Toyota, Honda, dll" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                                @error('merek') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- 3. Tipe -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe / Model</label>
                                <input wire:model="tipe" type="text" placeholder="Avanza, Civic, dll" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                                @error('tipe') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- 4. Warna -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Warna</label>
                                <input wire:model="warna" type="text" placeholder="Hitam, Putih, dll" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                                @error('warna') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- 5. Harga -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Harga Sewa / Hari (Rp)</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                      <span class="text-gray-500 sm:text-sm">Rp</span>
                                    </div>
                                    <input wire:model="harga" type="number" placeholder="0" class="pl-10 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 text-right font-mono">
                                </div>
                                @error('harga') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- 6. Transmisi -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Transmisi</label>
                                <select wire:model="transmisi" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 bg-white">
                                    <option value="">-- Pilih Transmisi --</option>
                                    <option value="manual">Manual</option>
                                    <option value="otomatis">Otomatis</option>
                                </select>
                                @error('transmisi') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- 7. Kursi -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Kursi</label>
                                <select wire:model="kursi" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 bg-white">
                                    <option value="">-- Pilih --</option>
                                    <option value="5">5 Kursi (City Car/Sedan)</option>
                                    <option value="7">7 Kursi (MPV)</option>
                                    <option value="9">9 Kursi (SUV)</option>
                                    <option value="14">14 Kursi (Travel)</option>
                                    <option value="19">19 Kursi (Minibus)</option>
                                </select>
                                @error('kursi') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- 8. Status (Full Width) -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status Ketersediaan</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    <label class="cursor-pointer">
                                        <input type="radio" wire:model="status" value="tersedia" class="peer sr-only">
                                        <div class="text-center rounded-md border border-gray-200 py-2 px-3 hover:bg-gray-50 peer-checked:bg-green-100 peer-checked:text-green-700 peer-checked:border-green-500 transition">
                                            Tersedia
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" wire:model="status" value="disewa" class="peer sr-only">
                                        <div class="text-center rounded-md border border-gray-200 py-2 px-3 hover:bg-gray-50 peer-checked:bg-blue-100 peer-checked:text-blue-700 peer-checked:border-blue-500 transition">
                                            Disewa
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" wire:model="status" value="pemeliharaan" class="peer sr-only">
                                        <div class="text-center rounded-md border border-gray-200 py-2 px-3 hover:bg-gray-50 peer-checked:bg-red-100 peer-checked:text-red-700 peer-checked:border-red-500 transition">
                                            Service
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" wire:model="status" value="dibersihkan" class="peer sr-only">
                                        <div class="text-center rounded-md border border-gray-200 py-2 px-3 hover:bg-gray-50 peer-checked:bg-yellow-100 peer-checked:text-yellow-700 peer-checked:border-yellow-500 transition">
                                            Cuci
                                        </div>
                                    </label>
                                </div>
                                @error('status') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- 9. Foto Upload (Full Width) -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Foto Mobil</label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-blue-400 transition bg-gray-50">
                                    <div class="space-y-1 text-center">
                                        <!-- Preview Area -->
                                        @if ($foto)
                                            <img src="{{ $foto->temporaryUrl() }}" class="mx-auto h-48 object-cover rounded-lg shadow-md mb-3">
                                            <p class="text-xs text-green-600 font-semibold">Foto Baru Dipilih</p>
                                        @elseif ($foto_lama)
                                            <img src="{{ asset('storage/' . $foto_lama) }}" class="mx-auto h-48 object-cover rounded-lg shadow-md mb-3">
                                            <p class="text-xs text-gray-500">Foto Saat Ini</p>
                                        @else
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        @endif

                                        <div class="flex text-sm text-gray-600 justify-center">
                                            <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                <span>Upload file</span>
                                                <input id="file-upload" wire:model="foto" type="file" class="sr-only">
                                            </label>
                                            <p class="pl-1">atau drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                                    </div>
                                </div>
                                <div wire:loading wire:target="foto" class="text-sm text-blue-500 mt-2 font-semibold text-center w-full animate-pulse">Sedang mengupload foto...</div>
                                @error('foto') <span class="text-red-500 text-xs mt-1 block text-center">{{ $message }}</span> @enderror
                            </div>

                        </div>
                    </div>
                    
                    <!-- Footer Actions -->
                    <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3 rounded-b-xl border-t border-gray-100">
                        <button type="submit" wire:loading.attr="disabled" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-bold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="{{ $isEditMode ? 'update' : 'store' }}">{{ $isEditMode ? 'Simpan Perubahan' : 'Simpan Data' }}</span>
                            <span wire:loading wire:target="{{ $isEditMode ? 'update' : 'store' }}">Menyimpan...</span>
                        </button>
                        <button wire:click="$set('showModal', false)" type="button" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>