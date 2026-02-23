<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
            
            {{-- Tampilan Mobile (Simpel) --}}
            <div class="flex justify-between flex-1 sm:hidden">
                @if ($paginator->onFirstPage())
                    <span class="relative inline-flex items-center px-4 py-3 text-xs font-black text-gray-300 uppercase tracking-widest bg-gray-50 border border-gray-100 rounded-2xl cursor-default leading-5">
                        &laquo; Prev
                    </span>
                @else
                    <button wire:click="previousPage" wire:loading.attr="disabled" class="relative inline-flex items-center px-4 py-3 text-xs font-black text-cyan-600 uppercase tracking-widest bg-white border border-gray-100 rounded-2xl leading-5 hover:bg-cyan-50 focus:outline-none transition-all shadow-sm active:scale-95">
                        &laquo; Prev
                    </button>
                @endif

                @if ($paginator->hasMorePages())
                    <button wire:click="nextPage" wire:loading.attr="disabled" class="relative inline-flex items-center px-4 py-3 ml-3 text-xs font-black text-cyan-600 uppercase tracking-widest bg-white border border-gray-100 rounded-2xl leading-5 hover:bg-cyan-50 focus:outline-none transition-all shadow-sm active:scale-95">
                        Next &raquo;
                    </button>
                @else
                    <span class="relative inline-flex items-center px-4 py-3 ml-3 text-xs font-black text-gray-300 uppercase tracking-widest bg-gray-50 border border-gray-100 rounded-2xl cursor-default leading-5">
                        Next &raquo;
                    </span>
                @endif
            </div>

            {{-- Tampilan Desktop (Lengkap) --}}
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                        Menampilkan
                        <span class="font-black text-cyan-600">{{ $paginator->firstItem() }}</span>
                        sampai
                        <span class="font-black text-cyan-600">{{ $paginator->lastItem() }}</span>
                        dari
                        <span class="font-black text-cyan-600">{{ $paginator->total() }}</span>
                        data
                    </p>
                </div>

                <div>
                    <span class="relative z-0 inline-flex shadow-sm rounded-xl gap-1.5">
                        {{-- Tombol Sebelumnya --}}
                        @if ($paginator->onFirstPage())
                            <span aria-disabled="true" aria-label="@lang('pagination.previous')">
                                <span class="relative inline-flex items-center justify-center w-10 h-10 text-gray-300 bg-gray-50 border border-gray-100 rounded-xl cursor-default" aria-hidden="true">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                                </span>
                            </span>
                        @else
                            <button wire:click="previousPage" rel="prev" class="relative inline-flex items-center justify-center w-10 h-10 text-cyan-600 bg-white border border-gray-100 rounded-xl hover:bg-cyan-50 hover:border-cyan-200 focus:z-10 focus:outline-none transition-all duration-200 active:scale-90" aria-label="@lang('pagination.previous')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                            </button>
                        @endif

                        {{-- Element Pagination (Nomor Halaman) --}}
                        @foreach ($elements as $element)
                            {{-- "Three Dots" Separator --}}
                            @if (is_string($element))
                                <span aria-disabled="true">
                                    <span class="relative inline-flex items-center justify-center w-10 h-10 text-xs font-black text-gray-300 bg-gray-50 border border-gray-100 rounded-xl cursor-default tracking-wider">{{ $element }}</span>
                                </span>
                            @endif

                            {{-- Array Of Links --}}
                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    @if ($page == $paginator->currentPage())
                                        <span aria-current="page">
                                            <span class="relative inline-flex items-center justify-center w-10 h-10 text-xs font-black text-white bg-cyan-600 border border-cyan-600 rounded-xl shadow-lg shadow-cyan-200 cursor-default transform scale-105">{{ $page }}</span>
                                        </span>
                                    @else
                                        <button wire:click="gotoPage({{ $page }})" class="relative inline-flex items-center justify-center w-10 h-10 text-xs font-black text-gray-500 bg-white border border-gray-100 rounded-xl hover:bg-cyan-50 hover:text-cyan-700 hover:border-cyan-200 focus:z-10 focus:outline-none transition-all duration-200 active:scale-90" aria-label="@lang('pagination.goto_page', ['page' => $page])">
                                            {{ $page }}
                                        </button>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach

                        {{-- Tombol Berikutnya --}}
                        @if ($paginator->hasMorePages())
                            <button wire:click="nextPage" rel="next" class="relative inline-flex items-center justify-center w-10 h-10 text-cyan-600 bg-white border border-gray-100 rounded-xl hover:bg-cyan-50 hover:border-cyan-200 focus:z-10 focus:outline-none transition-all duration-200 active:scale-90" aria-label="@lang('pagination.next')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        @else
                            <span aria-disabled="true" aria-label="@lang('pagination.next')">
                                <span class="relative inline-flex items-center justify-center w-10 h-10 text-gray-300 bg-gray-50 border border-gray-100 rounded-xl cursor-default" aria-hidden="true">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                                </span>
                            </span>
                        @endif
                    </span>
                </div>
            </div>
        </nav>
    @endif
</div>