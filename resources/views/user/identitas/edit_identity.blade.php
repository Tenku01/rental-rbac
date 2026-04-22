<!-- resources/views/user/identitas/edit_identity.blade.php -->

<x-navbar />

<div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md mt-24 border border-cyan-600">
    <h1 class="text-2xl font-semibold text-left text-gray-700 mb-2">Edit Identitas Saya</h1>
    <p class="text-sm text-gray-500 mb-6">Unggah file baru jika dokumen sebelumnya buram, kadaluarsa, atau ditolak oleh admin.</p>

    <form action="{{ route('update.identity', $userIdentification->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <!-- Jika route di web.php menggunakan Route::put() atau Route::patch(), uncomment baris di bawah ini -->
        {{-- @method('PUT') --}}

        <div class="overflow-x-auto">
            <table class="min-w-full mt-6 table-auto border border-cyan-600 rounded-lg">
                <thead class="bg-cyan-600 text-white">
                    <tr>
                        <th class="py-3 px-6 text-left text-sm font-semibold border-r border-cyan-700 w-1/4">Jenis Dokumen</th>
                        <th class="py-3 px-6 text-center text-sm font-semibold border-r border-cyan-700 w-1/4">File Saat Ini</th>
                        <th class="py-3 px-6 text-left text-sm font-semibold w-1/2">Upload File Baru (Opsional)</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Baris KTP -->
                    <tr class="hover:bg-gray-50 border-b border-cyan-600">
                        <td class="py-3 px-6 text-sm text-gray-700 border-r border-cyan-600 font-medium align-middle">
                            KTP
                        </td>
                        <td class="py-3 px-6 border-r border-cyan-600 text-center align-middle">
                            @if($userIdentification->foto_ktp)
                                <img src="{{ Storage::url($userIdentification->foto_ktp) }}" alt="Preview KTP" class="w-24 h-auto mx-auto rounded shadow border border-gray-200">
                            @else
                                <span class="text-xs text-gray-400 italic">Belum ada file</span>
                            @endif
                        </td>
                        <td class="py-3 px-6 align-middle">
                            <input type="file" name="ktp" id="ktp" accept="image/jpeg,image/png" class="block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-cyan-50 file:text-cyan-700
                                hover:file:bg-cyan-100 cursor-pointer">
                            <p class="text-xs text-gray-400 mt-1">Format: JPG/PNG. Kosongkan jika tidak ingin mengubah KTP.</p>
                            <x-input-error :messages="$errors->get('ktp')" class="mt-2" />
                        </td>
                    </tr>

                    <!-- Baris SIM -->
                    <tr class="hover:bg-gray-50 border-b border-cyan-600">
                        <td class="py-3 px-6 text-sm text-gray-700 border-r border-cyan-600 font-medium align-middle">
                            SIM
                        </td>
                        <td class="py-3 px-6 border-r border-cyan-600 text-center align-middle">
                            @if($userIdentification->foto_sim)
                                <img src="{{ Storage::url($userIdentification->foto_sim) }}" alt="Preview SIM" class="w-24 h-auto mx-auto rounded shadow border border-gray-200">
                            @else
                                <span class="text-xs text-gray-400 italic">Belum ada file</span>
                            @endif
                        </td>
                        <td class="py-3 px-6 align-middle">
                            <input type="file" name="sim" id="sim" accept="image/jpeg,image/png" class="block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-cyan-50 file:text-cyan-700
                                hover:file:bg-cyan-100 cursor-pointer">
                            <p class="text-xs text-gray-400 mt-1">Format: JPG/PNG. Kosongkan jika tidak ingin mengubah SIM.</p>
                            <x-input-error :messages="$errors->get('sim')" class="mt-2" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-8 flex justify-between items-center">
            <!-- Tombol Kembali -->
            <a href="{{ route('dashboard') }}" class="text-cyan-700 font-medium hover:underline flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Dashboard
            </a>

            <!-- Tombol Submit -->
            <button type="submit" class="bg-cyan-600 text-white px-8 py-2 rounded-lg font-semibold hover:bg-cyan-700 transition duration-300 ease-in-out transform hover:scale-105 shadow-md">
                Update Identitas
            </button>
        </div>
    </form>
</div>