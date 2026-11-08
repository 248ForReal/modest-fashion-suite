@extends('layouts.app')
@section('title', 'Absen Masuk - Atelier')
@section('back', route('dashboard'))
@section('page_title', 'Absen Masuk')

@section('content')
<div class="max-w-md mx-auto space-y-4">
    <livewire:attendance.clock-in />
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('clockInMap', ({ officeLat, officeLon, radius, workStart, allowOutside = false }) => ({
        lat: null, lng: null, locStatus: 'idle', distance: null, inRadius: null,
        officeLat, officeLon, radius, workStart, allowOutside,
        map: null, userMarker: null, submitting: false,

        toRad(v) { return v * Math.PI / 180; },
        calcDistance(lat1, lon1, lat2, lon2) {
            const R = 6371000, dLat = this.toRad(lat2-lat1), dLon = this.toRad(lon2-lon1);
            const a = Math.sin(dLat/2)**2 + Math.cos(this.toRad(lat1))*Math.cos(this.toRad(lat2))*Math.sin(dLon/2)**2;
            return Math.round(R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)));
        },

        initMap() {
            if (this.map || !window.L) return;
            this.map = L.map('map-in', { zoomControl: false, attributionControl: false }).setView([this.officeLat, this.officeLon], 17);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19, crossOrigin: true, subdomains: ['a','b','c']
            }).addTo(this.map);
            L.circle([this.officeLat, this.officeLon], {
                radius: this.radius, color: '#F4A95A', fillColor: '#F4A95A', fillOpacity: 0.15, weight: 2
            }).addTo(this.map);
            const officeIcon = L.divIcon({
                className: '',
                html: '<div style="width:32px;height:32px;background:#F4A95A;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;"><div style="transform:rotate(45deg);font-size:14px;">🏢</div></div>',
                iconSize: [32, 32], iconAnchor: [16, 32]
            });
            L.marker([this.officeLat, this.officeLon], { icon: officeIcon }).addTo(this.map);
            setTimeout(() => this.map.invalidateSize(), 200);
        },

        updateMap() {
            if (!this.map) return;
            if (this.userMarker) this.map.removeLayer(this.userMarker);
            const color = this.inRadius ? '#22c55e' : '#ef4444';
            const pinHtml = '<div style="width:36px;height:36px;background:' + color + ';border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;"><div style="transform:rotate(45deg);font-size:16px;">📍</div></div>';
            const userIcon = L.divIcon({ className: '', html: pinHtml, iconSize: [36, 36], iconAnchor: [18, 36] });
            this.userMarker = L.marker([this.lat, this.lng], { icon: userIcon }).addTo(this.map);
            if (this.inRadius) {
                this.map.fitBounds([[this.lat, this.lng],[this.officeLat, this.officeLon]], { padding: [50,50], maxZoom: 17 });
            } else {
                this.map.setView([this.lat, this.lng], 15);
            }
            setTimeout(() => this.map.invalidateSize(), 100);
        },

        loadLeaflet(cb) {
            if (window.L) { cb(); return; }
            const css = document.createElement('link'); css.rel='stylesheet'; css.href='/css/leaflet.css'; document.head.appendChild(css);
            const js = document.createElement('script'); js.src='/js/leaflet.js'; js.onload = cb; document.head.appendChild(js);
        },

        getLocation() {
            this.locStatus = 'loading';
            this.loadLeaflet(() => { this.$nextTick(() => this.initMap()); });
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.lat = pos.coords.latitude; this.lng = pos.coords.longitude;
                    this.distance = this.calcDistance(this.lat, this.lng, this.officeLat, this.officeLon);
                    this.inRadius = this.distance <= this.radius;
                    this.locStatus = 'ok';
                    this.$nextTick(() => this.updateMap());
                },
                () => { this.locStatus = 'error'; },
                { enableHighAccuracy: false, timeout: 8000, maximumAge: 30000 }
            );
        },

        submitClockIn($wire) {
            if (this.locStatus !== 'ok') {
                Swal.fire({ icon: 'warning', title: 'Lokasi belum terdeteksi', text: 'Tekan Refresh dan tunggu GPS terdeteksi.', confirmButtonColor: '#F4A95A' });
                return;
            }
            if (!this.inRadius && !this.allowOutside) {
                Swal.fire({ icon: 'error', title: 'Di luar area kantor', text: 'Jarak kamu ' + this.distance + ' m dari kantor. Kamu harus berada dalam radius ' + this.radius + ' m.', confirmButtonColor: '#F4A95A', confirmButtonText: 'Mengerti' });
                return;
            }
            this.submitting = true;
            $wire.latitude = this.lat;
            $wire.longitude = this.lng;
            $wire.clockIn();
        },

        get timeStatus() {
            const now = new Date(), parts = this.workStart.split(':').map(Number);
            const start = new Date(); start.setHours(parts[0], parts[1], 0, 0);
            const diff = Math.round((now-start)/60000);
            if (diff < -1) return { label: 'Early ' + Math.abs(diff) + ' menit', color: 'text-blue-600', bg: 'bg-blue-50 border-blue-200' };
            if (diff <= 5) return { label: 'Tepat Waktu', color: 'text-green-600', bg: 'bg-green-50 border-green-200' };
            return { label: 'Terlambat ' + diff + ' menit', color: 'text-red-600', bg: 'bg-red-50 border-red-200' };
        }
    }));
});

document.addEventListener('livewire:initialized', () => {
    setInterval(() => {
        const el = document.getElementById('live-clock-in');
        if (el) el.textContent = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }, 1000);

    Livewire.on('absen-success', ({ type, distance, onTime }) => {
        const label = type === 'clock_in' ? 'Masuk' : 'Pulang';
        const timeInfo = onTime ? '✓ Tepat waktu' : '⚠ Terlambat — dicatat tidak valid';
        Swal.fire({
            icon: onTime ? 'success' : 'warning',
            title: 'Absen ' + label + ' Berhasil!',
            html: 'Jarak dari kantor: <b>' + distance + ' m</b><br><span style="font-size:0.9em">' + timeInfo + '</span>',
            confirmButtonColor: '#F4A95A',
            confirmButtonText: 'OK',
            timer: 2500,
            timerProgressBar: true,
        }).then(() => { window.location.href = '/dashboard'; });
    });

    Livewire.on('absen-blocked', ({ distance, radius }) => {
        Swal.fire({
            icon: 'error',
            title: 'Tidak Dapat Absen',
            text: 'Kamu berada ' + distance + ' m dari kantor. Radius yang diizinkan hanya ' + radius + ' m.',
            confirmButtonColor: '#F4A95A',
            confirmButtonText: 'Mengerti',
        });
    });

    Livewire.on('absen-duplicate', ({ label }) => {
        Swal.fire({
            icon: 'info',
            title: 'Sudah Absen ' + label.charAt(0).toUpperCase() + label.slice(1),
            text: 'Kamu sudah melakukan absen ' + label + ' hari ini.',
            confirmButtonColor: '#F4A95A',
            confirmButtonText: 'OK',
        }).then(() => { window.location.href = '/dashboard'; });
    });

    Livewire.on('absen-outside', ({ type, distance }) => {
        const label = type === 'clock_in' ? 'Masuk' : 'Pulang';
        Swal.fire({
            icon: 'warning',
            title: 'Absen ' + label + ' Tercatat',
            text: 'Kamu di luar radius kantor (' + distance + ' meter). Absen tetap direkam namun ditandai tidak valid.',
            confirmButtonColor: '#F4A95A',
            confirmButtonText: 'OK',
        }).then(() => { window.location.href = '/dashboard'; });
    });
});
</script>
@endpush
