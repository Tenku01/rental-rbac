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
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </div>
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight leading-none uppercase ">Manajemen Personel</h1>
            <p class="text-sm text-gray-500 font-medium mt-2">Daftar pengguna terdaftar dan alokasi peranan operasional.</p>
        </div>
    </div>
    
    @can('create-users')
    <button wire:click="create" class="bg-cyan-600 hover:bg-cyan-700 text-white font-black py-4 px-10 rounded-2xl shadow-xl shadow-cyan-100 flex items-center transition-all transform hover:scale-105 uppercase tracking-[0.2em] text-[11px]  leading-none">
        <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
        Daftarkan User
    </button>
    @endcan
</div>

{{-- 2. CONTROL BAR (SEARCH & FILTER) --}}
<div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 flex flex-col lg:flex-row gap-5 items-center">
    <div class="relative flex-1 w-full group">
        <span class="absolute inset-y-0 left-0 flex items-center pl-5 group-focus-within:text-cyan-600 transition-colors">
            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </span>
        <input wire:model.live.debounce.300ms="search" type="text" 
            class="w-full h-14 pl-14 pr-6 rounded-2xl border-gray-100 bg-gray-50/50 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-bold text-sm transition-all  placeholder:not-" 
            placeholder="Cari berdasarkan nama lengkap atau alamat email...">
    </div>
    
    <div class="flex gap-4 w-full lg:w-auto">
        <select wire:model.live="filterRole" class="flex-1 lg:w-64 h-14 rounded-2xl border-gray-100 bg-gray-50/50 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-black text-[11px] uppercase tracking-widest  cursor-pointer transition-all px-6">
            <option value="">Semua Peranan</option>
            @foreach($roles as $role)
                <option value="{{ $role->name }}">{{ strtoupper($role->name) }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- 3. DATA TABLE --}}
<div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50/80 border-b border-gray-100">
                <tr>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] ">Informasi Personal</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] ">Otoritas (Role)</th>
                    <th class="px-8 py-5 text-center text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] ">Akses Akun</th>
                    <th class="px-8 py-5 text-right text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] ">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $user)
                <tr wire:key="user-row-{{ $user->id }}" class="hover:bg-cyan-50/20 transition-colors group">
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-5">
                            <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-cyan-600 to-blue-700 flex items-center justify-center text-white font-black text-sm shadow-lg shadow-cyan-100 uppercase ring-4 ring-white ring-offset-1">
                                {{ substr($user->name, 0, 2) }}
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[15px] font-black text-gray-900 uppercase  leading-none">{{ $user->name }}</span>
                                <span class="text-[11px] text-gray-400 font-bold mt-2 tracking-tight">{{ $user->email }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex flex-wrap gap-2">
                            @forelse($user->getRoleNames() as $roleName)
                                <span class="px-3 py-1.5 bg-cyan-50 text-cyan-700 text-[10px] font-black rounded-lg uppercase tracking-widest border border-cyan-100 shadow-sm shadow-cyan-100/50 leading-none">
                                    {{ $roleName }}
                                </span>
                            @empty
                                <span class="text-[10px] font-black text-gray-300 uppercase ">Unassigned</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-8 py-6 text-center">
                        @can('update-users')
                            <button wire:click="toggleStatus({{ $user->id }})" 
                                class="px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-[0.1em]  transition-all shadow-sm border
                                {{ $user->status === 'aktif' ? 'bg-emerald-50 text-emerald-600 border-emerald-100 hover:bg-emerald-600 hover:text-white' : 'bg-rose-50 text-rose-600 border-rose-100 hover:bg-rose-600 hover:text-white' }}">
                                {{ strtoupper($user->status) }}
                            </button>
                        @else
                            <span class="px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-[0.1em]  {{ $user->status === 'aktif' ? 'bg-emerald-50 text-emerald-500' : 'bg-rose-50 text-rose-500' }}">
                                {{ strtoupper($user->status) }}
                            </span>
                        @endcan
                    </td>
                    <td class="px-8 py-6 text-right">
                        <div class="flex justify-end gap-3">
                            @php
                                $curr = auth()->user();
                                $canEdit = ($curr->hasRole('admin') || $curr->can('create-users-any-role') || ($curr->can('create-users-same-role') && $user->hasRole($curr->getRoleNames()->first())));
                            @endphp

                            @if($canEdit && $curr->can('update-users'))
                            <button wire:click="edit({{ $user->id }})" class="p-2.5 text-cyan-600 bg-cyan-50 hover:bg-cyan-600 hover:text-white rounded-xl transition-all border border-cyan-100 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            @endif
                            
                            @can('delete-users')
                            <button onclick="confirm('Hapus personel tim ini secara permanen?') || event.stopImmediatePropagation()" 
                                wire:click="delete({{ $user->id }})" class="p-2.5 text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white rounded-xl transition-all border border-rose-100 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-8 py-24 text-center text-gray-400  text-sm font-medium">
                        Basis data personel tidak ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-8 py-8 border-t border-gray-100 bg-gray-50/50">
        {{ $users->links() }}
    </div>
</div>

{{-- 4. MODAL FORM --}}
@if($showModal)
<div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-gray-100">
            <form wire:submit.prevent="save">
                <div class="bg-white p-10">
                    <div class="mb-10 border-b border-gray-100 pb-6 flex justify-between items-center">
                        <div>
                            <h3 class="text-2xl font-black text-gray-900 uppercase  tracking-tight">{{ $modalTitle }}</h3>
                            <p class="text-[11px] text-cyan-600 font-bold uppercase tracking-[0.25em] mt-2  leading-none">Otoritas Akses Sistem AKA RENTAL</p>
                        </div>
                        <button type="button" wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="md:col-span-2 group">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">Nama Lengkap Sesuai Identitas</label>
                            <input wire:model="name" type="text" class="w-full h-14 rounded-2xl border-gray-100 bg-gray-50/50 px-6 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-bold text-gray-800 transition-all ">
                            @error('name') <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 block tracking-widest">{{ $message }}</span> @enderror
                        </div>

                        <div class="group">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1  group-focus-within:text-cyan-600 transition-colors">Email Korporat / Akun</label>
                            <input wire:model="email" type="email" class="w-full h-14 rounded-2xl border-gray-100 bg-gray-50/50 px-6 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-bold text-gray-800 transition-all ">
                            @error('email') <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 block tracking-widest">{{ $message }}</span> @enderror
                        </div>

                        <div class="group">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1  group-focus-within:text-cyan-600 transition-colors">Tingkat Otoritas (Role)</label>
                            <select wire:model="selectedRole" class="w-full h-14 rounded-2xl border-gray-100 bg-gray-50/50 px-6 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-black text-[11px] uppercase tracking-widest  transition-all cursor-pointer">
                                <option value="">Pilih Otoritas</option>
                                @foreach($roles as $role)
                                    @php
                                        $curr = auth()->user();
                                        $show = false;
                                        if($curr->hasRole('admin') || $curr->can('create-users-any-role')) $show = true;
                                        elseif($curr->can('create-users-same-role') && $curr->hasRole($role->name)) $show = true;
                                    @endphp
                                    @if($show)
                                        <option value="{{ $role->name }}">{{ strtoupper($role->name) }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @error('selectedRole') <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 block tracking-widest">{{ $message }}</span> @enderror
                        </div>

                        <div class="group">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1  group-focus-within:text-cyan-600 transition-colors">Kata Sandi @if($editingUserId) <span class="lowercase font-medium opacity-60 ">(Opsional)</span> @endif</label>
                            <input wire:model="password" type="password" class="w-full h-14 rounded-2xl border-gray-100 bg-gray-50/50 px-6 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-bold transition-all">
                            @error('password') <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 block tracking-widest">{{ $message }}</span> @enderror
                        </div>

                        <div class="group">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1  group-focus-within:text-cyan-600 transition-colors">Konfirmasi Sandi</label>
                            <input wire:model="password_confirmation" type="password" class="w-full h-14 rounded-2xl border-gray-100 bg-gray-50/50 px-6 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-bold transition-all">
                        </div>

                        <div class="md:col-span-2 space-y-4">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 text-center ">Status Keaktifan Personel</label>
                            <div class="flex gap-6">
                                <label class="flex-1 flex items-center justify-center h-16 rounded-2xl border-2 cursor-pointer transition-all shadow-sm {{ $status === 'aktif' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-700 ring-4 ring-emerald-500/10' : 'border-gray-100 hover:border-emerald-200' }}">
                                    <input type="radio" wire:model="status" value="aktif" class="hidden">
                                    <span class="text-xs font-black uppercase tracking-[0.2em] ">Akses Aktif</span>
                                </label>
                                <label class="flex-1 flex items-center justify-center h-16 rounded-2xl border-2 cursor-pointer transition-all shadow-sm {{ $status === 'nonaktif' ? 'border-rose-500 bg-rose-50/50 text-rose-700 ring-4 ring-rose-500/10' : 'border-gray-100 hover:border-rose-200' }}">
                                    <input type="radio" wire:model="status" value="nonaktif" class="hidden">
                                    <span class="text-xs font-black uppercase tracking-[0.2em] ">Nonaktif</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50/80 px-10 py-8 flex flex-row-reverse gap-4 border-t border-gray-100 rounded-b-[2.5rem]">
                    <button type="submit" class="inline-flex justify-center rounded-2xl px-12 py-4 bg-cyan-600 text-xs font-black text-white hover:bg-cyan-700 shadow-xl shadow-cyan-100 transition-all uppercase tracking-[0.2em]  leading-none">
                        Simpan Data Personel
                    </button>
                    <button type="button" wire:click="closeModal" class="inline-flex justify-center rounded-2xl px-8 py-4 bg-white text-xs font-black text-gray-400 hover:bg-gray-100 border border-gray-200 transition-all uppercase tracking-[0.2em]  leading-none">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif


</div>