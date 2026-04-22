<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Identitas Saya') }}
        </h2>
    </x-slot>

    <div x-data="{ ktpPreview: '{{ $userIdentification?->foto_ktp ? Storage::url($userIdentification->foto_ktp) : '' }}', simPreview: '{{ $userIdentification?->foto_sim ? Storage::url($userIdentification->foto_sim) : '' }}' }"
         class="max-w-7xl mx-auto p-6 bg-white rounded-lg shadow-xl mt-10 border border-cyan-600/50">
        
        <h1 class="text-3xl font-bold text-left text-gray-800 mb-6 border-b pb-3">Verifikasi Identitas Penyewa</h1>

        {{-- 🔔 Notifikasi Status Global (Disesuaikan ke status_verifikasi) --}}
        @if($userIdentification && $userIdentification->status_verifikasi !== 'belum_upload')
            @if($userIdentification->status_verifikasi === 'disetujui')
                <div class="mb-6 bg-emerald-50 border border-emerald-300 text-emerald-700 px-4 py-3 rounded-xl shadow-md flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        ✅ Identitas Anda telah disetujui. Anda siap untuk melakukan pemesanan mobil.
                        <p class="text-xs text-emerald-600 mt-1">Status ini berlaku sejak: {{ \Carbon\Carbon::parse($userIdentification->updated_at)->format('d M Y') }}</p>
                    </div>
                </div>
            @elseif($userIdentification->status_verifikasi === 'ditolak')
                <div class="mb-6 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-xl shadow-md flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        ❌ Identitas Anda ditolak. Silakan periksa kembali file Anda (pastikan jelas dan tidak buram) dan unggah ulang untuk diverifikasi.
                        @if ($userIdentification->alasan_penolakan)
                            <p class="text-sm font-semibold mt-1">Alasan: {{ $userIdentification->alasan_penolakan }}</p>
                        @endif
                    </div>
                </div>
            @elseif($userIdentification->status_verifikasi === 'menunggu')
                <div class="mb-6 bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded-xl shadow-md flex items-center">
                    <svg class="w-6 h-6 mr-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"></path></svg>
                    <div>
                        ⏳ Identitas Anda sedang menunggu verifikasi. Verifikasi biasanya memakan waktu 1x24 jam.
                    </div>
                </div>
            @endif
        @else
             <div class="mb-6 bg-blue-50 border border-blue-300 text-blue-800 px-4 py-3 rounded-xl shadow-md">
                🔔 Harap unggah KTP dan SIM Anda untuk memulai proses verifikasi identitas.
            </div>
        @endif

        {{-- 🔴 NOTIFIKASI ERROR VALIDASI (DITAMBAHKAN DI SINI) --}}
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-xl shadow-md">
                <div class="flex items-center mb-2">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-bold">Mohon periksa kembali form Anda:</span>
                </div>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('upload.identity') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="overflow-x-auto">
                <table class="min-w-full w-full text-sm">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr class="text-left">
                            <th class="px-5 py-3 font-semibold w-1/5">Jenis Dokumen</th>
                            <th class="px-5 py-3 font-semibold w-1/4">File Terunggah / Preview</th>
                            <th class="px-5 py-3 font-semibold w-1/5">Tanggal Terakhir Update</th>
                            <th class="px-5 py-3 font-semibold w-1/5">Status</th>
                            <th class="px-5 py-3 font-semibold w-1/5">Unggah Ulang</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        @php
                            // Logic untuk status badge (Disesuaikan dengan enum status_verifikasi)
                            $currentStatus = $userIdentification?->status_verifikasi ?? 'belum_upload';
                            $statusLower = strtolower($currentStatus);

                            $statusProps = [
                                'belum_upload' => ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'ring' => 'ring-gray-100', 'dot' => 'bg-gray-400', 'label' => 'Belum Upload'],
                                'menunggu' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-700', 'ring' => 'ring-yellow-100', 'dot' => 'bg-yellow-500', 'label' => 'Menunggu'],
                                'disetujui' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'ring' => 'ring-emerald-100', 'dot' => 'bg-emerald-500', 'label' => 'Disetujui'],
                                'ditolak' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'ring' => 'ring-red-100', 'dot' => 'bg-red-500', 'label' => 'Ditolak'],
                            ];
                            $props = $statusProps[$statusLower] ?? $statusProps['belum_upload'];
                        @endphp
                        
                        {{-- KTP --}}
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-700 font-medium">Kartu Tanda Penduduk (KTP)</td>

                            <td class="px-5 py-3">
                                <template x-if="ktpPreview">
                                    <img :src="ktpPreview" alt="KTP Preview"
                                         class="w-32 h-auto rounded-lg border shadow-md object-cover transition duration-300 hover:scale-105 cursor-pointer">
                                </template>
                                <template x-if="!ktpPreview">
                                    <span class="text-gray-500 italic">Tidak ada file</span>
                                </template>
                            </td>

                            <td class="px-5 py-3 text-gray-700">
                                @if($userIdentification?->foto_ktp)
                                    {{ \Carbon\Carbon::parse($userIdentification->updated_at)->format('d M Y') }}
                                @else
                                    <span class="text-gray-500">Belum diupload</span>
                                @endif
                            </td>

                            <td class="px-5 py-3">
                                {{-- Status Badge Langsung --}}
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $props['bg'] }} {{ $props['text'] }} ring-1 {{ $props['ring'] }}">
                                    <span class="h-2 w-2 rounded-full {{ $props['dot'] }}"></span>
                                    {{ $props['label'] }}
                                </span>
                            </td>

                            <td class="px-5 py-3">
                                <label for="ktp" class="bg-cyan-600 text-white px-4 py-2 rounded-lg cursor-pointer hover:bg-cyan-700 transition shadow-md">
                                    Pilih KTP Baru
                                </label>
                                <input type="file" name="ktp" id="ktp" class="hidden" 
                                    accept="image/jpeg,image/png"
                                    @change="if (event.target.files.length) { ktpPreview = URL.createObjectURL(event.target.files[0]) } else { ktpPreview = '{{ $userIdentification?->foto_ktp ? Storage::url($userIdentification->foto_ktp) : '' }}' }"
                                    {{ $userIdentification?->foto_ktp ? '' : 'required' }}>
                            </td>
                        </tr>

                        {{-- SIM --}}
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-700 font-medium">Surat Izin Mengemudi (SIM)</td>

                            <td class="px-5 py-3">
                                <template x-if="simPreview">
                                    <img :src="simPreview" alt="SIM Preview"
                                         class="w-32 h-auto rounded-lg border shadow-md object-cover transition duration-300 hover:scale-105 cursor-pointer">
                                </template>
                                <template x-if="!simPreview">
                                    <span class="text-gray-500 italic">Tidak ada file</span>
                                </template>
                            </td>

                            <td class="px-5 py-3 text-gray-700">
                                @if($userIdentification?->foto_sim)
                                    {{ \Carbon\Carbon::parse($userIdentification->updated_at)->format('d M Y') }}
                                @else
                                    <span class="text-gray-500">Belum diupload</span>
                                @endif
                            </td>

                            <td class="px-5 py-3">
                                {{-- Status Badge Langsung --}}
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $props['bg'] }} {{ $props['text'] }} ring-1 {{ $props['ring'] }}">
                                    <span class="h-2 w-2 rounded-full {{ $props['dot'] }}"></span>
                                    {{ $props['label'] }}
                                </span>
                            </td>

                            <td class="px-5 py-3">
                                <label for="sim" class="bg-cyan-600 text-white px-4 py-2 rounded-lg cursor-pointer hover:bg-cyan-700 transition shadow-md">
                                    Pilih SIM Baru
                                </label>
                                <input type="file" name="sim" id="sim" class="hidden" 
                                    accept="image/jpeg,image/png"
                                    @change="if (event.target.files.length) { simPreview = URL.createObjectURL(event.target.files[0]) } else { simPreview = '{{ $userIdentification?->foto_sim ? Storage::url($userIdentification->foto_sim) : '' }}' }"
                                    {{ $userIdentification?->foto_sim ? '' : 'required' }}>
                            </td>
                        </tr>
                        
                        {{-- Baris terakhir untuk tombol Upload --}}
                        <tr>
                            <td colspan="5" class="px-5 py-4 text-center">
                                <small class="text-gray-500">Hanya format JPG atau PNG yang diterima. Maksimal ukuran 5MB.</small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-8 flex justify-center">
                <button type="submit"
                    class="bg-cyan-600 text-white px-8 py-3 text-lg rounded-xl font-semibold hover:bg-cyan-700 transition duration-300 ease-in-out transform hover:scale-105 shadow-lg">
                    SUBMIT & UPLOAD VERIFIKASI
                </button>
            </div>
        </form>
    </div>
</x-app-layout>