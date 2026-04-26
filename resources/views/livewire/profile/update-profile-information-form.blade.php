<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $alamat = '';
    public string $no_telepon = '';
    
    // Tetap kita pertahankan di backend untuk mengunci lokasi akurat
    public ?string $latitude = null;
    public ?string $longitude = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->alamat = Auth::user()->alamat ?? '';
        $this->no_telepon = Auth::user()->no_telepon ?? '';
        $this->latitude = Auth::user()->latitude ?? null;
        $this->longitude = Auth::user()->longitude ?? null;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'alamat' => ['nullable', 'string', 'max:255'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'latitude' => ['nullable', 'string', 'max:50'],
            'longitude' => ['nullable', 'string', 'max:50'],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    {{-- Import Leaflet CSS & JS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <header>
        <h2 class="text-lg font-bold text-slate-900">
            {{ __('Data Diri Lengkap') }}
        </h2>

        <p class="mt-1 text-sm text-slate-600">
            {{ __("Perbarui nama lengkap, nomor telepon, alamat domisili, dan alamat email Anda.") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
        <div>
            <x-input-label for="name" :value="__('Nama Lengkap')" class="font-bold text-slate-700" />
            <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full rounded-xl border-slate-300 focus:border-cyan-500 focus:ring-cyan-500" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="no_telepon" :value="__('Nomor Telepon')" class="font-bold text-slate-700" />
            <x-text-input wire:model="no_telepon" id="no_telepon" name="no_telepon" type="text" class="mt-1 block w-full rounded-xl border-slate-300 focus:border-cyan-500 focus:ring-cyan-500" placeholder="Contoh: 08123456789" />
            <x-input-error class="mt-2" :messages="$errors->get('no_telepon')" />
        </div>

        {{-- AREA PETA & ALAMAT --}}
        <div x-data="addressMap()" x-init="initMap()" class="bg-slate-50 p-4 sm:p-6 rounded-2xl border border-slate-200">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-2 gap-2">
                <x-input-label for="alamat" :value="__('Titik Lokasi & Alamat Lengkap')" class="font-bold text-slate-700" />
                
                <span class="text-[10px] font-bold uppercase tracking-widest text-cyan-600 bg-cyan-100 px-2 py-1 rounded-md">
                    Peta Interaktif
                </span>
            </div>

            <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                📍 <strong>Tips Cerdas:</strong> Ketik alamat Anda di kotak bawah. Jika titik peta kurang tepat (misal nama daerah kembar), Anda dapat menambahkan nama kota (contoh: <em>Demangan, Yogyakarta</em>) atau <strong>langsung geser pin di peta</strong> ke lokasi yang benar.
            </p>

            {{-- Container Peta --}}
            <div wire:ignore>
                <div id="map" class="w-full h-64 md:h-72 rounded-xl border border-slate-300 shadow-inner z-0 relative mb-4"></div>
            </div>
            
            {{-- Input Teks Alamat --}}
            <div>
                <div class="flex justify-between items-end mb-1">
                    <x-input-label for="alamat" :value="__('Alamat Tertulis')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest" />
                    
                    {{-- Tombol Buka Maps hanya muncul jika kordinat sudah terkunci --}}
                    @if($latitude && $longitude)
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $latitude }},{{ $longitude }}" target="_blank" class="inline-flex items-center gap-1.5 text-[10px] font-bold text-cyan-600 hover:text-cyan-800 transition-colors bg-cyan-50 px-2 py-1 rounded-md border border-cyan-100">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Lihat di Maps
                        </a>
                    @endif
                </div>
                {{-- PERBAIKAN: Menambahkan debounce.1000ms agar livewire tidak bentrok dengan AlpineJS saat mengetik --}}
                <textarea wire:model.live.debounce.1000ms="alamat" id="alamat" name="alamat" rows="3" class="block w-full rounded-xl border-slate-200 focus:border-cyan-500 focus:ring-cyan-500 shadow-sm text-sm" placeholder="Ketik nama jalan, kelurahan, atau kota..."></textarea>
                <x-input-error class="mt-2" :messages="$errors->get('alamat')" />
            </div>

            {{-- HIDDEN INPUT: Menyimpan titik akurat diam-diam tanpa merusak UX --}}
            <input type="hidden" wire:model="latitude">
            <input type="hidden" wire:model="longitude">
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" class="font-bold text-slate-700" />
            <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full rounded-xl border-slate-300 focus:border-cyan-500 focus:ring-cyan-500" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-slate-800">
                        {{ __('Alamat email Anda belum diverifikasi.') }}

                        <button wire:click.prevent="sendVerification" class="underline text-sm text-slate-600 hover:text-slate-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                            {{ __('Klik di sini untuk mengirim ulang email verifikasi.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('Tautan verifikasi baru telah dikirim ke alamat email Anda.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button class="bg-cyan-600 hover:bg-cyan-700 rounded-xl px-6 py-3">{{ __('Simpan Perubahan') }}</x-primary-button>

            <x-action-message class="me-3 text-emerald-600 font-bold flex items-center gap-1" on="profile-updated">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                {{ __('Data Tersimpan.') }}
            </x-action-message>
        </div>
    </form>

    {{-- Script AlpineJS untuk menangani Peta --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('addressMap', () => ({
                map: null,
                marker: null,
                typingTimer: null,
                isUpdatingFromMap: false,

                initMap() {
                    // Gunakan data DB jika ada, jika kosong setel ke titik tengah Indonesia
                    let initialLat = this.$wire.latitude ? parseFloat(this.$wire.latitude) : -0.789275;
                    let initialLng = this.$wire.longitude ? parseFloat(this.$wire.longitude) : 113.921327;
                    let initialZoom = (this.$wire.latitude && this.$wire.longitude) ? 16 : 5;

                    this.map = L.map('map').setView([initialLat, initialLng], initialZoom);

                    // Tile Layer OSM
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(this.map);

                    // Inisiasi Marker
                    this.marker = L.marker([initialLat, initialLng], {draggable: true}).addTo(this.map);

                    // ==========================================
                    // EVENT 1: User Geser / Klik Peta
                    // ==========================================
                    this.marker.on('dragend', (e) => {
                        const position = this.marker.getLatLng();
                        this.updateCoordsAndAddress(position.lat, position.lng);
                    });

                    this.map.on('click', (e) => {
                        this.marker.setLatLng(e.latlng);
                        this.updateCoordsAndAddress(e.latlng.lat, e.latlng.lng);
                    });

                    // Jika ada text alamat tapi koordinat masih kosong saat load
                    if (this.$wire.alamat && !this.$wire.latitude) {
                        this.geocode(this.$wire.alamat);
                    }

                    // ==========================================
                    // EVENT 2: User Ketik Teks Alamat
                    // ==========================================
                    this.$watch('$wire.alamat', (value) => {
                        // Jika trigger watcher ini berasal dari pin peta yang digeser, abaikan
                        if (this.isUpdatingFromMap) {
                            this.isUpdatingFromMap = false;
                            return;
                        }

                        clearTimeout(this.typingTimer);
                        this.typingTimer = setTimeout(() => {
                            if (value && value.length > 5) {
                                this.geocode(value);
                            }
                        }, 1200); 
                    });
                },

                // Logic 1: Update Teks & Koordinat Rahasia dari Peta (Digeser)
                updateCoordsAndAddress(lat, lng) {
                    this.isUpdatingFromMap = true;
                    // Simpan koordinat akurat diam-diam
                    this.$wire.latitude = lat.toFixed(6);
                    this.$wire.longitude = lng.toFixed(6);
                    // Ambil detail alamat untuk ditulis di textarea
                    this.reverseGeocode(lat, lng);
                },

                // Fetch API 1: Titik ke Teks (Reverse Geocode)
                reverseGeocode(lat, lng) {
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                        .then(res => res.json())
                        .then(data => {
                            if(data && data.display_name) {
                                this.isUpdatingFromMap = true;
                                this.$wire.alamat = data.display_name;
                            }
                        })
                        .catch(err => console.error("Geocoding Error: ", err));
                },

                // Fetch API 2: Teks ke Titik (Geocode)
                geocode(address) {
                    // PERBAIKAN: Menambahkan countrycodes=id agar API fokus mencari di wilayah Indonesia saja
                    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&countrycodes=id&limit=1`)
                        .then(res => res.json())
                        .then(data => {
                            if(data && data.length > 0) {
                                let lat = parseFloat(data[0].lat);
                                let lng = parseFloat(data[0].lon);
                                
                                // PERBAIKAN: Menghapus "this.isUpdatingFromMap = true" di sini
                                // karena geocode() tidak mengubah isi text area, sehingga watcher AlpineJS
                                // tidak boleh diblokir agar tetap mendeteksi ketikan lanjutan pengguna.
                                this.$wire.latitude = lat.toFixed(6);
                                this.$wire.longitude = lng.toFixed(6);

                                // Pindahkan Peta
                                const newLatLng = new L.LatLng(lat, lng);
                                this.marker.setLatLng(newLatLng);
                                this.map.setView(newLatLng, 16);
                            }
                        })
                        .catch(err => console.error("Geocoding Error: ", err));
                }
            }));
        });
    </script>
</section>