<div class="p-8 space-y-10 bg-[#f9fafb] min-h-screen font-inter">
{{--
PENTING: Semua tag <style> dan konten HTML
HARUS berada di dalam satu div utama ini agar Livewire berjalan normal.
--}}
<style>
@import url('https://www.google.com/search?q=https://fonts.googleapis.com/css2%3Ffamily%3DInter:wght%40300%3B400%3B500%3B600%3B700%3B800%3B900%26display%3Dswap');
.font-inter { font-family: 'Inter', sans-serif; }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 20px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #0891b2; }
</style>

{{-- 1. HEADER --}}
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-gray-200 pb-8">
    <div class="flex items-center gap-5">
        <div class="h-14 w-14 bg-cyan-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-cyan-100 ring-4 ring-cyan-50">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
        </div>
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight leading-none uppercase italic">Otoritas Sistem</h1>
            <p class="text-sm text-gray-500 font-medium mt-2 font-inter">Konfigurasi hak akses operasional dan peranan personel.</p>
        </div>
    </div>
</div>

{{-- 2. TABEL ROLE --}}
<div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50/80 border-b border-gray-100">
                <tr>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] italic">No. Identitas</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] italic">Peranan (Role)</th>
                    <th class="px-8 py-5 text-center text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] italic">Otoritas Aktif</th>
                    <th class="px-8 py-5 text-right text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] italic">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($roles as $role)
                <tr wire:key="role-row-{{ $role->id }}" class="hover:bg-cyan-50/30 transition-colors group">
                    <td class="px-8 py-6 text-sm font-bold text-gray-400 tabular-nums italic">#{{ $role->id }}</td>
                    <td class="px-8 py-6">
                        <div class="flex flex-col">
                            <span class="text-base font-black text-gray-900 uppercase italic">{{ $role->name }}</span>
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Dibuat: {{ $role->created_at->format('d/m/Y') }}</span>
                        </div>
                    </td>
                    <td class="px-8 py-6 text-center">
                        <span class="px-4 py-1.5 bg-cyan-100/50 text-cyan-700 text-[10px] font-black rounded-full uppercase tracking-tighter border border-cyan-100">
                            {{ $role->permissions->count() }} Izin Terpasang
                        </span>
                    </td>
                    <td class="px-8 py-6 text-right">
                        @can('update-roles')
                        <button 
                            wire:click="edit({{ $role->id }})" 
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-cyan-600 bg-cyan-50 hover:bg-cyan-600 hover:text-white rounded-xl transition-all font-bold text-xs uppercase tracking-widest border border-cyan-100 shadow-sm"
                        >
                            <svg wire:loading.remove wire:target="edit({{ $role->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            <span wire:loading wire:target="edit({{ $role->id }})" class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></span>
                            Konfigurasi
                        </button>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- 3. MODAL CONFIG --}}
@if($showModal)
<div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        {{-- Background Backdrop --}}
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full border border-gray-100">
            <form wire:submit.prevent="update">
                <div class="bg-white p-10">
                    <div class="flex justify-between items-center mb-10 border-b border-gray-100 pb-6">
                        <div>
                            <h3 class="text-2xl font-black text-gray-900 uppercase italic tracking-tight">Otoritas Peranan</h3>
                            <p class="text-xs text-gray-400 font-bold mt-1 uppercase tracking-[0.2em] italic leading-none">ID Peranan: #{{ $roleId }}</p>
                        </div>
                        <button type="button" wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="space-y-10">
                        {{-- Field Nama --}}
                        <div class="group">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 italic group-focus-within:text-cyan-600 transition-colors">Identitas Peranan</label>
                            <input wire:model="role_name" type="text" 
                                class="w-full h-14 rounded-2xl border-gray-100 bg-gray-50/50 px-6 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-bold text-gray-800 transition-all @if($roleId === 1) opacity-50 cursor-not-allowed @endif"
                                placeholder="cth: staff_inspeksi" @if($roleId === 1) readonly @endif>
                            @error('role_name') <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 block tracking-widest">{{ $message }}</span> @enderror
                        </div>

                        {{-- Checklist Section --}}
                        <div class="space-y-6">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-2">
                                <label class="text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1 italic">Daftar Izin Akses (RBAC)</label>
                                <div class="relative w-full md:w-72 group">
                                    <input wire:model.live="searchPermission" type="text" placeholder="Cari modul atau aksi..." 
                                        class="w-full h-11 text-xs rounded-xl border-gray-100 bg-gray-50/80 pl-10 pr-4 focus:ring-4 focus:ring-cyan-500/10 focus:border-cyan-500 font-bold italic transition-all">
                                    <svg class="w-4 h-4 text-gray-300 absolute left-3.5 top-3.5 group-focus-within:text-cyan-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                            </div>

                            <div class="max-h-[400px] overflow-y-auto pr-3 space-y-8 custom-scrollbar">
                                @foreach($groupedPermissions as $group => $items)
                                <div class="bg-gray-50/50 p-6 rounded-3xl border border-gray-100/50 shadow-inner">
                                    {{-- MODUL HEADER DENGAN PILIH SEMUA --}}
                                    <div class="flex justify-between items-center mb-6">
                                        <h4 class="text-[11px] font-black text-cyan-700 uppercase tracking-[0.25em] flex items-center gap-3 italic">
                                            <span class="w-3 h-3 bg-cyan-600 rounded-full ring-4 ring-cyan-100"></span>
                                            Data: {{ strtoupper($group) }}
                                        </h4>
                                        
                                        <label class="flex items-center gap-2 cursor-pointer group/all">
                                            @php 
                                                $allGroupPerms = $items->pluck('name')->toArray(); 
                                                $allGroupPermsJson = json_encode($allGroupPerms);
                                            @endphp
                                            <input type="checkbox" 
                                                   x-on:change="
                                                        let perms = {{ $allGroupPermsJson }};
                                                        if($el.checked) {
                                                            // Gabungkan izin modul ini ke array utama tanpa duplikasi
                                                            $wire.selectedPermissions = [...new Set([...$wire.selectedPermissions, ...perms])];
                                                        } else {
                                                            // Hapus semua izin modul ini dari array utama
                                                            $wire.selectedPermissions = $wire.selectedPermissions.filter(p => !perms.includes(p));
                                                        }
                                                   "
                                                   x-bind:checked="{{ $allGroupPermsJson }}.every(p => $wire.selectedPermissions.includes(p))"
                                                   class="rounded border-gray-300 text-cyan-600 focus:ring-4 focus:ring-cyan-500/10 h-4 w-4 transition-all">
                                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest group-hover/all:text-cyan-600 transition-colors italic leading-none">Pilih Semua</span>
                                        </label>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach($items as $permission)
                                        <label wire:key="perm-{{ $permission->id }}" class="relative flex items-center gap-4 p-4 bg-white rounded-2xl border border-gray-50 cursor-pointer hover:border-cyan-200 hover:shadow-md hover:shadow-cyan-100/50 transition-all group/item">
                                            <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}"
                                                class="rounded-lg border-gray-300 text-cyan-600 shadow-sm focus:ring-4 focus:ring-cyan-500/10 h-6 w-6 transition-all">
                                            <div class="flex flex-col">
                                                <span class="text-[11px] font-black text-gray-700 group-hover/item:text-cyan-700 transition-colors uppercase italic leading-tight">{{ str_replace('-', ' ', $permission->name) }}</span>
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50/80 px-10 py-8 flex flex-row-reverse gap-4 border-t border-gray-100 rounded-b-[2.5rem]">
                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex justify-center rounded-2xl px-12 py-3.5 bg-cyan-600 text-xs font-black text-white hover:bg-cyan-700 shadow-xl shadow-cyan-100 transition-all uppercase tracking-[0.2em] italic disabled:opacity-50">
                        <span wire:loading.remove>Sinkronkan Izin</span>
                        <span wire:loading>Memproses...</span>
                    </button>
                    <button type="button" wire:click="closeModal"
                        class="inline-flex justify-center rounded-2xl px-8 py-3.5 bg-white text-xs font-black text-gray-400 hover:bg-gray-100 border border-gray-200 transition-all uppercase tracking-[0.2em] italic leading-none">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif


</div>