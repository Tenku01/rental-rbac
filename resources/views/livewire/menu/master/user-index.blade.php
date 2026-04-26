<div class="p-8 space-y-10 bg-[#f9fafb] min-h-screen font-inter">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        .font-inter { font-family: 'Inter', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 20px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #0891b2; }

        /* Animasi Toast */
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        .toast-enter { animation: slideInRight 0.3s ease-out forwards; }
        .toast-leave { animation: fadeOut 0.3s ease-in forwards; }
        .animate-fade-in-down { animation: fadeInDown 0.4s ease-out forwards; }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <!-- TOAST NOTIFICATION (Alpine.js) -->
    <div x-data="{ toasts: [] }" 
         @show-toast.window="
            let id = Date.now();
            toasts.push({ id: id, type: $event.detail.type, message: $event.detail.message });
            setTimeout(() => { toasts = toasts.filter(t => t.id !== id) }, 4000);
         "
         class="fixed top-5 right-5 z-[9999] flex flex-col gap-3 max-w-sm w-full pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="toast-enter pointer-events-auto flex items-center w-full p-4 rounded-xl shadow-lg border"
                 :class="{
                     'bg-white border-emerald-100': toast.type === 'success',
                     'bg-white border-rose-100': toast.type === 'error',
                     'bg-white border-amber-100': toast.type === 'warning'
                 }">
                <div class="inline-flex items-center justify-center flex-shrink-0 w-10 h-10 rounded-lg"
                     :class="{
                         'bg-emerald-50 text-emerald-500': toast.type === 'success',
                         'bg-rose-50 text-rose-500': toast.type === 'error',
                         'bg-amber-50 text-amber-500': toast.type === 'warning'
                     }">
                    <svg x-show="toast.type === 'success'" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                    <svg x-show="toast.type === 'error'" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                    <svg x-show="toast.type === 'warning'" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                </div>
                <div class="ml-3 text-sm font-bold text-gray-800 tracking-tight" x-text="toast.message"></div>
            </div>
        </template>
    </div>

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 border-b border-gray-200 pb-8">
        <div class="flex items-center gap-5">
            <div class="h-14 w-14 bg-cyan-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-cyan-100 ring-4 ring-cyan-50">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight leading-none uppercase ">Manajemen Pengguna</h1>
                <p class="text-sm text-gray-500 font-medium mt-2">Daftar pengguna terdaftar dan alokasi peranan operasional.</p>
            </div>
        </div>
        
        @can('create-users')
        <button wire:click="create" class="bg-cyan-600 hover:bg-cyan-700 text-white font-black py-4 px-10 rounded-2xl shadow-xl shadow-cyan-100 flex items-center transition-all transform hover:scale-105 uppercase tracking-[0.2em] text-[11px] leading-none">
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
                class="w-full h-14 pl-14 pr-6 rounded-2xl border border-gray-100 bg-gray-50/50 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-bold text-sm transition-all focus:outline-none placeholder:text-gray-400 placeholder:font-medium" 
                placeholder="Cari berdasarkan nama lengkap atau alamat email...">
        </div>
        
        <div class="flex gap-4 w-full lg:w-auto">
            <select wire:model.live="filterRole" class="flex-1 lg:w-64 h-14 rounded-2xl border border-gray-100 bg-gray-50/50 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-black text-[11px] uppercase tracking-widest cursor-pointer transition-all px-6 focus:outline-none">
                <option value="">Semua Peranan</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ strtoupper($role->name) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- 3. DATA TABLE --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left">
                <thead class="bg-gray-50/80 border-b border-gray-100">
                    <tr>
                        <th class="px-8 py-5 text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em] ">Informasi Personal</th>
                        <th class="px-8 py-5 text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em] ">Otoritas (Role)</th>
                        <th class="px-8 py-5 text-center text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em] ">Akses Akun</th>
                        <th class="px-8 py-5 text-right text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em] ">Aksi</th>
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
                                    <span class="text-[15px] font-black text-gray-900 uppercase leading-none">{{ $user->name }}</span>
                                    <span class="text-[13px] text-gray-400 font-bold mt-2 tracking-tight">{{ $user->email }}</span>
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
                            {{-- LOGIC: Jika user adalah admin, tampilkan badge disable --}}
                            @if($user->hasRole('admin'))
                                <span class="inline-flex px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-[0.1em] border bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed items-center justify-center gap-2">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    LOCKED
                                </span>
                            @else
                                @can('update-users')
                                    <button wire:click="toggleStatus({{ $user->id }})" 
                                        class="inline-flex px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-[0.1em] transition-all shadow-sm border
                                        {{ $user->status === 'aktif' ? 'bg-emerald-50 text-emerald-600 border-emerald-100 hover:bg-emerald-600 hover:text-white' : 'bg-rose-50 text-rose-600 border-rose-100 hover:bg-rose-600 hover:text-white' }}">
                                        {{ strtoupper($user->status) }}
                                    </button>
                                @else
                                    <span class="inline-flex px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-[0.1em] {{ $user->status === 'aktif' ? 'bg-emerald-50 text-emerald-500 border border-emerald-100' : 'bg-rose-50 text-rose-500 border border-rose-100' }}">
                                        {{ strtoupper($user->status) }}
                                    </span>
                                @endcan
                            @endif
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex justify-end gap-3">
                                @php
                                    $curr = auth()->user();
                                    $canEdit = ($curr->hasRole('admin') || $curr->can('create-users-any-role') || ($curr->can('create-users-same-role') && $user->hasRole($curr->getRoleNames()->first())));
                                @endphp

                                @if($canEdit && $curr->can('update-users'))
                                <button wire:click="edit({{ $user->id }})" class="p-2.5 text-cyan-600 bg-cyan-50 hover:bg-cyan-600 hover:text-white rounded-xl transition-all border border-cyan-100 shadow-sm" title="Edit Data">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                @endif
                                
                                @can('delete-users')
                                    @if($user->hasRole('admin'))
                                        <button disabled class="p-2.5 text-gray-300 bg-gray-50 border border-gray-100 rounded-xl cursor-not-allowed" title="Admin Utama Tidak Dapat Dihapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        </button>
                                    @else
                                        <button onclick="confirm('Hapus Pengguna tim ini secara permanen?') || event.stopImmediatePropagation()" 
                                            wire:click="delete({{ $user->id }})" class="p-2.5 text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white rounded-xl transition-all border border-rose-100 shadow-sm" title="Hapus User">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-8 py-24 text-center text-gray-400 text-sm font-medium italic">
                            Basis data Pengguna tidak ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-8 py-8 border-t border-gray-100 bg-gray-50/50">
            {{ $users->links('components.pagination-info') }}
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
                    <!-- HEADER MODAL FIXED -->
                    <div class="px-10 py-6 border-b border-gray-100 flex justify-between items-center bg-white sticky top-0 z-10">
                        <div>
                            <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tight">{{ $modalTitle }}</h3>
                            <p class="text-[11px] text-cyan-600 font-bold uppercase tracking-[0.25em] mt-2 leading-none">Otoritas Akses Sistem</p>
                        </div>
                        <button type="button" wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- CONTENT SCROLLABLE -->
                    <div class="bg-white p-10 max-h-[70vh] overflow-y-auto custom-scrollbar">
                        <div class="space-y-8">
                            <!-- BAGIAN UTAMA -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="md:col-span-2 group">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                        Nama Lengkap Sesuai Identitas <span class="text-rose-500">*</span>
                                    </label>
                                    <input wire:model.live.debounce.300ms="name" type="text" 
                                        class="w-full h-14 rounded-2xl px-6 font-bold transition-all focus:outline-none placeholder:text-gray-300
                                        @error('name') border-rose-500 bg-rose-50 text-rose-900 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-gray-50/50 text-gray-800 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror"
                                        placeholder="Contoh: Budi Santoso">
                                    @error('name') 
                                        <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $message }}
                                        </span> 
                                    @enderror
                                </div>

                                <div class="group">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                        Email Korporat / Akun <span class="text-rose-500">*</span>
                                    </label>
                                    <input wire:model.live.debounce.300ms="email" type="email" 
                                        class="w-full h-14 rounded-2xl px-6 font-bold transition-all focus:outline-none placeholder:text-gray-300
                                        @error('email') border-rose-500 bg-rose-50 text-rose-900 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-gray-50/50 text-gray-800 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror"
                                        placeholder="email@domain.com">
                                    @error('email') 
                                        <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $message }}
                                        </span> 
                                    @enderror
                                </div>

                                <div class="group">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                        Tingkat Otoritas (Role) <span class="text-rose-500">*</span>
                                    </label>
                                    <select wire:model.live="selectedRole" 
                                        class="w-full h-14 rounded-2xl px-6 font-black text-[11px] uppercase tracking-widest transition-all cursor-pointer focus:outline-none
                                        @error('selectedRole') border-rose-500 bg-rose-50 text-rose-900 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-gray-50/50 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror">
                                        <option value="">Pilih Otoritas</option>
                                        @foreach($roles as $role)
                                            @php
                                                $disabled = false;
                                                if ($role->name === 'admin' && $existingAdminId && $existingAdminId != $editingUserId) {
                                                    $disabled = true;
                                                }
                                            @endphp
                                            <option value="{{ $role->name }}" @if($disabled) disabled @endif>
                                                {{ strtoupper($role->name) }} @if($disabled) (SUDAH ADA) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('selectedRole') 
                                        <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $message }}
                                        </span> 
                                    @enderror
                                </div>

                                <div class="group">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                        Kata Sandi @if($editingUserId) <span class="lowercase font-medium opacity-60">(Opsional)</span> @else <span class="text-rose-500">*</span> @endif
                                    </label>
                                    <input wire:model.live.debounce.300ms="password" type="password" 
                                        class="w-full h-14 rounded-2xl px-6 font-bold transition-all focus:outline-none
                                        @error('password') border-rose-500 bg-rose-50 text-rose-900 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-gray-50/50 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror">
                                    @error('password') 
                                        <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $message }}
                                        </span> 
                                    @enderror
                                </div>

                                <div class="group">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                        Konfirmasi Sandi @if(!$editingUserId) <span class="text-rose-500">*</span> @endif
                                    </label>
                                    <input wire:model.live.debounce.300ms="password_confirmation" type="password" 
                                        class="w-full h-14 rounded-2xl px-6 font-bold transition-all focus:outline-none border border-gray-100 bg-gray-50/50 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10">
                                </div>

                                <div class="md:col-span-2 space-y-4">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 text-center">Status Keaktifan Pengguna <span class="text-rose-500">*</span></label>
                                    <div class="flex gap-6">
                                        <label class="flex-1 flex items-center justify-center h-16 rounded-2xl border-2 cursor-pointer transition-all shadow-sm {{ $status === 'aktif' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-700 ring-4 ring-emerald-500/10' : 'border-gray-100 hover:border-emerald-200' }}">
                                            <input type="radio" wire:model.live="status" value="aktif" class="hidden">
                                            <span class="text-xs font-black uppercase tracking-[0.2em]">Akses Aktif</span>
                                        </label>
                                        <label class="flex-1 flex items-center justify-center h-16 rounded-2xl border-2 cursor-pointer transition-all shadow-sm {{ $status === 'nonaktif' ? 'border-rose-500 bg-rose-50/50 text-rose-700 ring-4 ring-rose-500/10' : 'border-gray-100 hover:border-rose-200' }}">
                                            <input type="radio" wire:model.live="status" value="nonaktif" class="hidden">
                                            <span class="text-xs font-black uppercase tracking-[0.2em]">Nonaktif</span>
                                        </label>
                                    </div>
                                    @error('status') 
                                        <span class="text-rose-500 text-[10px] font-black uppercase mt-4 block tracking-widest text-center">{{ $message }}</span> 
                                    @enderror
                                </div>
                            </div>

                            <!-- BAGIAN KHUSUS PELANGGAN -->
                            @if($selectedRole === 'pelanggan')
                            <div class="pt-8 border-t border-dashed border-gray-200 space-y-6 animate-fade-in-down">
                                <h4 class="text-sm font-extrabold text-cyan-700 uppercase tracking-widest border-l-4 border-cyan-500 pl-4">Detail Profil Pelanggan & Dokumen</h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="group">
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                            Nomor Telepon / WA <span class="text-rose-500">*</span>
                                        </label>
                                        <input wire:model.live.debounce.300ms="no_telepon" type="number" 
                                            class="w-full h-14 rounded-2xl px-6 font-bold transition-all focus:outline-none placeholder:text-gray-300
                                            @error('no_telepon') border-rose-500 bg-rose-50 text-rose-900 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-gray-50/50 text-gray-800 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror" 
                                            placeholder="08xxxxxxxxxx">
                                        @error('no_telepon') 
                                            <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                {{ $message }}
                                            </span> 
                                        @enderror
                                    </div>
                                    <div class="group">
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                            Alamat Lengkap <span class="text-rose-500">*</span>
                                        </label>
                                        <input wire:model.live.debounce.300ms="alamat" type="text" 
                                            class="w-full h-14 rounded-2xl px-6 font-bold transition-all focus:outline-none placeholder:text-gray-300
                                            @error('alamat') border-rose-500 bg-rose-50 text-rose-900 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-gray-50/50 text-gray-800 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror" 
                                            placeholder="Jalan, RT/RW, Kota...">
                                        @error('alamat') 
                                            <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                {{ $message }}
                                            </span> 
                                        @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <!-- KTP Upload -->
                                    <div class="group">
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1">
                                            Foto KTP (Identitas) @if(!$editingUserId) <span class="text-rose-500">*</span> @endif
                                        </label>
                                        <label class="relative flex flex-col items-center justify-center w-full h-40 border-2 border-dashed rounded-3xl cursor-pointer transition-all bg-gray-50/30 overflow-hidden
                                            @error('ktp_file') border-rose-400 hover:border-rose-500 bg-rose-50/30 @else border-gray-200 hover:bg-gray-50 hover:border-cyan-300 group-hover:border-cyan-400 @enderror">
                                            
                                            {{-- LOGIKA PREVIEW GAMBAR --}}
                                            @if($ktp_file)
                                                <img src="{{ $ktp_file->temporaryUrl() }}" class="h-full w-full object-cover opacity-90">
                                            @elseif($editingUserId && ($existingUser = \App\Models\User::find($editingUserId)) && $existingUser->foto_ktp)
                                                <img src="{{ Storage::url($existingUser->foto_ktp) }}" class="h-full w-full object-cover opacity-90">
                                                <div class="absolute inset-0 flex items-center justify-center bg-gray-900/50 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <span class="text-white text-[10px] font-bold uppercase tracking-widest px-3 py-1 border border-white rounded-full">Ganti KTP</span>
                                                </div>
                                            @else
                                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                    <svg class="w-8 h-8 mb-3 transition-colors @error('ktp_file') text-rose-400 @else text-gray-300 group-hover:text-cyan-500 @enderror" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                    <p class="text-[10px] font-bold uppercase tracking-widest @error('ktp_file') text-rose-500 @else text-gray-400 group-hover:text-cyan-600 @enderror">Klik untuk Upload KTP</p>
                                                </div>
                                            @endif

                                            <input type="file" wire:model.live="ktp_file" class="hidden" accept="image/*">
                                        </label>
                                        <div wire:loading wire:target="ktp_file" class="text-[10px] text-cyan-500 font-bold mt-2 ml-1 animate-pulse">Sedang mengunggah file KTP...</div>
                                        @error('ktp_file') 
                                            <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                {{ $message }}
                                            </span> 
                                        @enderror
                                    </div>

                                    <!-- SIM Upload -->
                                    <div class="group">
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1">
                                            Foto SIM A (Pengemudi) @if(!$editingUserId) <span class="text-rose-500">*</span> @endif
                                        </label>
                                        <label class="relative flex flex-col items-center justify-center w-full h-40 border-2 border-dashed rounded-3xl cursor-pointer transition-all bg-gray-50/30 overflow-hidden
                                            @error('sim_file') border-rose-400 hover:border-rose-500 bg-rose-50/30 @else border-gray-200 hover:bg-gray-50 hover:border-cyan-300 group-hover:border-cyan-400 @enderror">
                                            
                                            {{-- LOGIKA PREVIEW GAMBAR --}}
                                            @if($sim_file)
                                                <img src="{{ $sim_file->temporaryUrl() }}" class="h-full w-full object-cover opacity-90">
                                            @elseif($editingUserId && ($existingUser = \App\Models\User::find($editingUserId)) && $existingUser->foto_sim)
                                                <img src="{{ Storage::url($existingUser->foto_sim) }}" class="h-full w-full object-cover opacity-90">
                                                <div class="absolute inset-0 flex items-center justify-center bg-gray-900/50 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <span class="text-white text-[10px] font-bold uppercase tracking-widest px-3 py-1 border border-white rounded-full">Ganti SIM</span>
                                                </div>
                                            @else
                                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                    <svg class="w-8 h-8 mb-3 transition-colors @error('sim_file') text-rose-400 @else text-gray-300 group-hover:text-cyan-500 @enderror" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .884-.5 2-2 2h4c-1.5 0-2-1.116-2-2z"></path></svg>
                                                    <p class="text-[10px] font-bold uppercase tracking-widest @error('sim_file') text-rose-500 @else text-gray-400 group-hover:text-cyan-600 @enderror">Klik untuk Upload SIM</p>
                                                </div>
                                            @endif

                                            <input type="file" wire:model.live="sim_file" class="hidden" accept="image/*">
                                        </label>
                                        <div wire:loading wire:target="sim_file" class="text-[10px] text-cyan-500 font-bold mt-2 ml-1 animate-pulse">Sedang mengunggah file SIM...</div>
                                        @error('sim_file') 
                                            <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                {{ $message }}
                                            </span> 
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- BAGIAN KHUSUS SOPIR -->
                            @if($selectedRole === 'sopir')
                            <div class="pt-8 border-t border-dashed border-gray-200 space-y-6 animate-fade-in-down">
                                <h4 class="text-sm font-extrabold text-amber-600 uppercase tracking-widest border-l-4 border-amber-500 pl-4">Kelengkapan Data Sopir</h4>
                                
                                <div class="group">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-amber-600 transition-colors">
                                        Nomor SIM A Umum / B1 <span class="text-rose-500">*</span>
                                    </label>
                                    <input wire:model.live.debounce.300ms="no_sim_sopir" type="text" 
                                        class="w-full h-14 rounded-2xl px-6 font-bold transition-all focus:outline-none placeholder:text-gray-300
                                        @error('no_sim_sopir') border-rose-500 bg-rose-50 text-rose-900 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-gray-50/50 text-gray-800 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 @enderror" 
                                        placeholder="Masukkan Nomor SIM Driver">
                                    @error('no_sim_sopir') 
                                        <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $message }}
                                        </span> 
                                    @enderror
                                </div>
                            </div>
                            @endif

                        </div>
                    </div>

                    <div class="bg-gray-50/80 px-10 py-8 flex flex-row-reverse gap-4 border-t border-gray-100 rounded-b-[2.5rem]">
                        <button type="submit" wire:loading.attr="disabled"
                            class="relative inline-flex justify-center items-center rounded-2xl px-12 py-4 bg-cyan-600 text-xs font-black text-white hover:bg-cyan-700 shadow-xl shadow-cyan-100 transition-all uppercase tracking-[0.2em] leading-none disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="save">Simpan Data Pengguna</span>
                            <span wire:loading wire:target="save" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Menyimpan...
                            </span>
                        </button>
                        <button type="button" wire:click="closeModal" 
                            class="inline-flex justify-center rounded-2xl px-8 py-4 bg-white text-xs font-black text-gray-400 hover:bg-gray-100 border border-gray-200 transition-all uppercase tracking-[0.2em] leading-none">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>