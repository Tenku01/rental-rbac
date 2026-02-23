<div class="p-8 space-y-10 bg-[#f9fafb] min-h-screen font-inter">
<style>
@import url('https://www.google.com/search?q=https://fonts.googleapis.com/css2%3Ffamily%3DInter:wght%40300%3B400%3B500%3B600%3B700%3B800%3B900%26display%3Dswap');
.font-inter { font-family: 'Inter', sans-serif; }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 20px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #0891b2; }
</style>

{{-- 1. HEADER --}}
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 border-b border-gray-200 pb-8">
    <div class="flex items-center gap-5">
        <div class="h-14 w-14 bg-cyan-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-cyan-100 ring-4 ring-cyan-50">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight leading-none uppercase ">Verifikasi Identitas</h1>
            <p class="text-sm text-gray-500 font-medium mt-2">Validasi dokumen KTP dan SIM Pengguna.</p>
        </div>
    </div>

    {{-- Tombol Tambah Manual --}}
    @can('create-verification')
    <button wire:click="create" class="bg-cyan-600 hover:bg-cyan-700 text-white font-black py-4 px-8 rounded-2xl shadow-xl shadow-cyan-100 flex items-center transition-all transform hover:scale-105 uppercase tracking-[0.2em] text-[11px] leading-none">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
        Upload Manual
    </button>
    @endcan
</div>

{{-- 2. CONTROL BAR (TABS & SEARCH) --}}
<div class="bg-white p-2 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col lg:flex-row items-center justify-between gap-4">
    <!-- Status Tabs -->
    <div class="flex p-1 space-x-1 bg-gray-50 rounded-3xl w-full lg:w-auto overflow-x-auto">
        @foreach(['menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak'] as $key => $label)
        <button wire:click="$set('filterStatus', '{{ $key }}')"
            class="px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-sm whitespace-nowrap
            {{ $filterStatus === $key 
                ? 'bg-white text-cyan-600 shadow-md ring-1 ring-black/5' 
                : 'text-gray-400 hover:text-gray-600 hover:bg-gray-100' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    <!-- Search -->
    <div class="relative w-full lg:w-96 pr-2">
        <span class="absolute inset-y-0 left-0 flex items-center pl-5 pointer-events-none text-gray-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </span>
        <input wire:model.live.debounce.300ms="search" type="text" 
            class="w-full h-12 pl-12 pr-6 rounded-2xl border-gray-100 bg-gray-50 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-bold text-xs transition-all placeholder:text-gray-300" 
            placeholder="Cari Pelanggan...">
    </div>
</div>

{{-- Flash Messages --}}
<x-action-message on="message" class="bg-emerald-50 text-emerald-600 border border-emerald-100 p-4 rounded-2xl font-bold text-sm shadow-sm mb-4" />
<x-action-message on="error" class="bg-rose-50 text-rose-600 border border-rose-100 p-4 rounded-2xl font-bold text-sm shadow-sm mb-4" />

{{-- 3. DATA TABLE --}}
<div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden mt-6">
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left">
            <thead class="bg-gray-50/80 border-b border-gray-100">
                <tr>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em]">Pelanggan</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em]">Dokumen KTP</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em]">Dokumen SIM</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] text-center">Status</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($identitas as $item)
                <tr class="hover:bg-cyan-50/20 transition-colors group">
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-500 font-black text-xs shadow-inner">
                                {{ substr($item->user->name ?? '?', 0, 2) }}
                            </div>
                            <div>
                                <div class="font-bold text-gray-900 text-sm">{{ $item->user->name ?? 'User Terhapus' }}</div>
                                <div class="text-[10px] font-bold text-gray-400 mt-0.5">{{ $item->user->email ?? '-' }}</div>
                                <div class="text-[10px] text-gray-400 mt-1">Diajukan: {{ $item->created_at->format('d M Y') }}</div>
                            </div>
                        </div>
                    </td>
                    
                    {{-- KTP PREVIEW --}}
                    <td class="px-8 py-6">
                        @if($item->ktp)
                            <a href="{{ asset('storage/' . $item->ktp) }}" target="_blank" class="group/img relative block w-24 h-16 rounded-lg overflow-hidden border border-gray-200 shadow-sm hover:shadow-md transition-all bg-gray-100">
                                <img src="{{ asset('storage/' . $item->ktp) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover/img:scale-110">
                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover/img:opacity-100 transition-opacity">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </div>
                            </a>
                        @else
                            <span class="text-xs text-gray-300 font-bold italic">Tidak Ada</span>
                        @endif
                    </td>

                    {{-- SIM PREVIEW --}}
                    <td class="px-8 py-6">
                        @if($item->sim)
                            <a href="{{ asset('storage/' . $item->sim) }}" target="_blank" class="group/img relative block w-24 h-16 rounded-lg overflow-hidden border border-gray-200 shadow-sm hover:shadow-md transition-all bg-gray-100">
                                <img src="{{ asset('storage/' . $item->sim) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover/img:scale-110">
                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover/img:opacity-100 transition-opacity">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </div>
                            </a>
                        @else
                            <span class="text-xs text-gray-300 font-bold italic">Tidak Ada</span>
                        @endif
                    </td>

                    {{-- STATUS BADGE --}}
                    <td class="px-8 py-6 text-center">
                        @php
                            $statusClass = match($item->status_approval) {
                                'disetujui' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                'ditolak' => 'bg-rose-50 text-rose-600 border-rose-100',
                                default => 'bg-amber-50 text-amber-600 border-amber-100',
                            };
                        @endphp
                        <span class="inline-flex px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest border {{ $statusClass }}">
                            {{ $item->status_approval }}
                        </span>
                    </td>

                    {{-- ACTIONS --}}
                    <td class="px-8 py-6 text-right">
                        <div class="flex justify-end gap-2 items-center">
                            @if($item->status_approval === 'menunggu')
                                {{-- Tombol Approve & Reject (Quick Action) --}}
                                @can('update-verification')
                                <button wire:confirm="Setujui dokumen identitas ini?" wire:click="approve({{ $item->id }})" 
                                    class="p-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-xl transition-all border border-emerald-100 shadow-sm" title="Setujui Cepat">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                                
                                <button wire:confirm="Tolak dokumen identitas ini?" wire:click="reject({{ $item->id }})" 
                                    class="p-2 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-xl transition-all border border-rose-100 shadow-sm" title="Tolak Cepat">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                                @endcan
                            @endif

                            {{-- Dropdown / More Actions --}}
                            <div class="relative ml-2" x-data="{ open: false }">
                                <button @click="open = !open" class="text-gray-400 hover:text-cyan-600 transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                                </button>
                                <div x-show="open" @click.away="open = false" 
                                    class="absolute right-0 mt-2 w-36 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-20" 
                                    style="display: none;">
                                    
                                    @can('update-verification')
                                    <button wire:click="edit({{ $item->id }})" class="w-full text-left px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-50 hover:text-cyan-600 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        Edit Data
                                    </button>
                                    @endcan

                                    @can('delete-verification')
                                    <button wire:confirm="Hapus data verifikasi ini selamanya?" wire:click="delete({{ $item->id }})" class="w-full text-left px-4 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Hapus
                                    </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-8 py-24 text-center text-gray-400 text-sm font-medium">
                        <div class="flex flex-col items-center justify-center">
                            <div class="h-20 w-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <p>Tidak ada pengajuan verifikasi dengan status "{{ ucfirst($filterStatus) }}".</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Pagination --}}
    <div class="px-8 py-8 border-t border-gray-100 bg-gray-50/50">
        {{ $identitas->links('components.pagination-info') }}
    </div>
</div>

{{-- 4. MODAL FORM (CREATE / EDIT) --}}
@if($showModal)
<div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-gray-100">
            <form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}">
                <!-- Header Modal -->
                <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <div>
                        <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight">{{ $modalTitle }}</h3>
                        <p class="text-[10px] text-cyan-600 font-bold uppercase tracking-[0.25em] mt-1 leading-none">Dokumen Legalitas Pengguna</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-8 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    
                    <!-- Searchable User Dropdown (Alpine.js) -->
                    <div class="group" x-data="{
                        open: false,
                        search: '',
                        selectedUser: @entangle('user_id'),
                        users: {{ $users->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email])->values()->toJson() }},
                        get filteredUsers() {
                            if (this.search === '') {
                                return this.users;
                            }
                            return this.users.filter(user => {
                                return user.name.toLowerCase().includes(this.search.toLowerCase()) || 
                                       user.email.toLowerCase().includes(this.search.toLowerCase());
                            });
                        },
                        get selectedUserName() {
                            if (!this.selectedUser) return '';
                            let user = this.users.find(u => u.id == this.selectedUser);
                            return user ? user.name + ' (' + user.email + ')' : '';
                        }
                    }">
                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 group-focus-within:text-cyan-600 transition-colors">Pilih Pelanggan</label>
                        
                        <div class="relative">
                            <!-- Trigger Button -->
                            <button type="button" @click="open = !open" 
                                class="w-full h-12 rounded-2xl border border-gray-100 bg-gray-50/50 px-4 text-left font-bold text-gray-800 text-sm transition-all flex items-center justify-between focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10">
                                <span x-text="selectedUserName || '-- Pilih Pelanggan --'" :class="{'text-gray-400': !selectedUser}"></span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            <!-- Dropdown List -->
                            <div x-show="open" @click.away="open = false" 
                                class="absolute z-20 mt-2 w-full bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden" 
                                style="display: none;">
                                
                                <!-- Search Input -->
                                <div class="p-2 border-b border-gray-50">
                                    <input x-model="search" type="text" class="w-full rounded-xl border-gray-100 bg-gray-50 px-3 py-2 text-xs font-bold text-gray-700 placeholder-gray-400 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/10 transition-all" placeholder="Cari nama atau email..." autofocus>
                                </div>

                                <!-- User List -->
                                <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                    <template x-for="user in filteredUsers" :key="user.id">
                                        <div @click="selectedUser = user.id; open = false; search = ''" 
                                            class="px-4 py-3 cursor-pointer hover:bg-cyan-50 group/item transition-colors border-b border-gray-50 last:border-0">
                                            <div class="text-xs font-bold text-gray-700 group-hover/item:text-cyan-700" x-text="user.name"></div>
                                            <div class="text-[10px] font-medium text-gray-400 group-hover/item:text-cyan-500" x-text="user.email"></div>
                                        </div>
                                    </template>
                                    <div x-show="filteredUsers.length === 0" class="px-4 py-3 text-xs font-medium text-gray-400 text-center italic">
                                        Pelanggan tidak ditemukan.
                                    </div>
                                </div>
                            </div>
                        </div>
                        @error('user_id') <span class="text-rose-500 text-[10px] font-black uppercase mt-1 block tracking-widest">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <!-- Upload KTP -->
                        <div class="group">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Upload KTP</label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-200 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50 hover:border-cyan-300 transition-all bg-gray-50/30 overflow-hidden relative">
                                @if($ktp_file)
                                    <img src="{{ $ktp_file->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover opacity-90">
                                @elseif($existing_ktp)
                                    <img src="{{ asset('storage/' . $existing_ktp) }}" class="absolute inset-0 w-full h-full object-cover opacity-90">
                                @else
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-6 h-6 mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Pilih KTP</p>
                                    </div>
                                @endif
                                <input type="file" wire:model="ktp_file" class="hidden" accept="image/*">
                            </label>
                            <div wire:loading wire:target="ktp_file" class="text-[10px] text-cyan-500 font-bold mt-1 animate-pulse">Mengupload...</div>
                            @error('ktp_file') <span class="text-rose-500 text-[10px] font-black uppercase mt-1 block tracking-widest">{{ $message }}</span> @enderror
                        </div>

                        <!-- Upload SIM -->
                        <div class="group">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Upload SIM</label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-200 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50 hover:border-cyan-300 transition-all bg-gray-50/30 overflow-hidden relative">
                                @if($sim_file)
                                    <img src="{{ $sim_file->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover opacity-90">
                                @elseif($existing_sim)
                                    <img src="{{ asset('storage/' . $existing_sim) }}" class="absolute inset-0 w-full h-full object-cover opacity-90">
                                @else
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-6 h-6 mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .884-.5 2-2 2h4c-1.5 0-2-1.116-2-2z"></path></svg>
                                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Pilih SIM</p>
                                    </div>
                                @endif
                                <input type="file" wire:model="sim_file" class="hidden" accept="image/*">
                            </label>
                            <div wire:loading wire:target="sim_file" class="text-[10px] text-cyan-500 font-bold mt-1 animate-pulse">Mengupload...</div>
                            @error('sim_file') <span class="text-rose-500 text-[10px] font-black uppercase mt-1 block tracking-widest">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="group">
                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 group-focus-within:text-cyan-600">Status Verifikasi</label>
                        <select wire:model="status_approval" class="w-full h-12 rounded-2xl border-gray-100 bg-gray-50/50 px-4 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-bold text-gray-800 text-sm transition-all cursor-pointer">
                            <option value="menunggu">Menunggu</option>
                            <option value="disetujui">Disetujui</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                    </div>

                </div>

                <div class="bg-gray-50/80 px-8 py-6 flex flex-row-reverse gap-3 border-t border-gray-100 rounded-b-[2.5rem]">
                    <button type="submit" class="inline-flex justify-center rounded-xl px-8 py-3 bg-cyan-600 text-[10px] font-black text-white hover:bg-cyan-700 shadow-lg shadow-cyan-100 transition-all uppercase tracking-[0.2em] leading-none">
                        Simpan
                    </button>
                    <button type="button" wire:click="closeModal" class="inline-flex justify-center rounded-xl px-6 py-3 bg-white text-[10px] font-black text-gray-400 hover:bg-gray-100 border border-gray-200 transition-all uppercase tracking-[0.2em] leading-none">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<x-toast-notification />
</div>