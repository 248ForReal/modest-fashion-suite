{{-- Color Picker: klik foto → ambil warna, beri nama --}}
@php
    $livewire       = $getLivewire();
    $record         = method_exists($livewire, 'getRecord') ? $livewire->getRecord() : null;
    $existingPhotos = $record?->photos?->map(fn($p) => asset('storage/' . $p->photo_path))->values()->toArray() ?? [];
    $initialColors  = $livewire->colorData['json'] ?? '[]';
@endphp

{{-- wire:ignore mencegah Livewire re-render div ini saat update komponen lain --}}
<div wire:ignore x-data="cpPicker()" x-init="boot(@js($existingPhotos), @js($initialColors))" style="font-family:inherit">

<style>
.cp-wrap{display:flex;gap:24px;flex-wrap:wrap;align-items:flex-start}
.cp-left{flex:0 0 340px;max-width:100%}
.cp-right{flex:1;min-width:240px}

.cp-box{position:relative;border-radius:10px;overflow:hidden;border:2px solid #e5e7eb;background:#f3f4f6;cursor:crosshair}
.cp-box canvas{display:block;width:100%;height:auto}
.cp-hair{position:absolute;pointer-events:none;display:none;width:20px;height:20px;transform:translate(-50%,-50%)}
.cp-hair::before,.cp-hair::after{content:'';position:absolute;background:rgba(255,255,255,.9);box-shadow:0 0 2px rgba(0,0,0,.5)}
.cp-hair::before{height:1px;width:100%;top:50%;left:0}
.cp-hair::after{width:1px;height:100%;left:50%;top:0}

.cp-hover{display:flex;align-items:center;gap:8px;margin-top:8px;min-height:28px}
.cp-dot{width:22px;height:22px;border-radius:50%;border:2px solid #d1d5db;flex-shrink:0}
.cp-hexlabel{font-size:13px;color:#374151;font-weight:600;font-family:monospace}
.cp-tip{font-size:12px;color:#9ca3af}

.cp-thumbs{display:flex;gap:6px;flex-wrap:wrap;margin-top:10px}
.cp-thumb{width:52px;height:52px;border-radius:7px;cursor:pointer;border:3px solid transparent;object-fit:cover;opacity:.6;transition:border-color .15s,opacity .15s}
.cp-thumb:hover{opacity:1;border-color:#6366f1}
.cp-thumb.on{opacity:1;border-color:#6366f1;box-shadow:0 0 0 2px rgba(99,102,241,.25)}
.cp-thumb.fresh{border-color:#10b981;opacity:.8}
.cp-thumb.fresh.on{box-shadow:0 0 0 2px rgba(16,185,129,.25)}

.cp-empty-box{padding:24px;text-align:center;color:#9ca3af;font-size:14px;background:#f9fafb;border-radius:10px;border:2px dashed #e5e7eb;min-height:80px;display:flex;align-items:center;justify-content:center}

.cp-title{font-size:13px;font-weight:600;color:#374151;margin-bottom:10px}
.cp-empty-pal{font-size:13px;color:#9ca3af;padding:12px 0}
.cp-row{display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid #f3f4f6}
.cp-swatch{width:30px;height:30px;border-radius:7px;flex-shrink:0;border:2px solid rgba(0,0,0,.08);box-shadow:0 1px 4px rgba(0,0,0,.12)}
.cp-code{font-size:11px;color:#6b7280;width:64px;flex-shrink:0;font-family:monospace}
.cp-name{flex:1;border:1px solid #e5e7eb;border-radius:6px;padding:5px 8px;font-size:13px;outline:none;background:#fff;color:#111827}
.cp-name:focus{border-color:#6366f1;box-shadow:0 0 0 2px rgba(99,102,241,.12)}
.cp-del{background:none;border:none;cursor:pointer;color:#d1d5db;font-size:18px;padding:0 2px;line-height:1;flex-shrink:0}
.cp-del:hover{color:#ef4444}
.cp-max{font-size:12px;color:#f59e0b;margin-top:6px}
.cp-hint{font-size:12px;color:#9ca3af;margin-top:10px;line-height:1.5}

@media(max-width:640px){.cp-wrap{flex-direction:column}.cp-left{flex:0 0 auto;width:100%}}
</style>

<div class="cp-wrap">

  {{-- Kiri: canvas + thumbs --}}
  <div class="cp-left">

    <div x-show="photos.length > 0">
      <div class="cp-box" @mousemove="hover($event)" @mouseleave="unhover()" @click="pick($event)">
        <canvas x-ref="cv"></canvas>
        <div class="cp-hair" x-ref="hair" :style="'left:'+hx+'px;top:'+hy+'px'"></div>
      </div>

      <div class="cp-hover">
        <div class="cp-dot" :style="'background:'+(hhex||'#e5e7eb')"></div>
        <span class="cp-hexlabel" x-show="hhex" x-text="hhex"></span>
        <span class="cp-tip"      x-show="!hhex">Arahkan ke foto untuk ambil warna</span>
      </div>

      <div class="cp-thumbs" x-show="photos.length > 1">
        <template x-for="(p,i) in photos" :key="p.k">
          <img :src="p.url"
               :class="'cp-thumb'+(p.fresh?' fresh':'')+(activeK===p.k?' on':'')"
               @click.stop="switchPhoto(p.k)"
               :title="'Foto '+(i+1)+(p.fresh?' (baru)':'')">
        </template>
      </div>
    </div>

    <div class="cp-empty-box" x-show="photos.length === 0">
      Upload foto produk untuk mengaktifkan color picker
    </div>
  </div>

  {{-- Kanan: palet --}}
  <div class="cp-right">
    <div class="cp-title">Palet Warna (<span x-text="cols.length"></span>/8)</div>

    <div class="cp-empty-pal" x-show="cols.length === 0">
      Belum ada warna — klik foto untuk menambah
    </div>

    <div>
      <template x-for="(c,i) in cols" :key="c.id">
        <div class="cp-row">
          <div class="cp-swatch" :style="'background:'+c.hex"></div>
          <span class="cp-code" x-text="c.hex"></span>
          <input class="cp-name" type="text" placeholder="Nama warna..." maxlength="50"
                 :value="c.name"
                 @change="rename(c.id, $event.target.value)">
          <button class="cp-del" type="button" @click="remove(c.id)">&times;</button>
        </div>
      </template>
    </div>

    <div class="cp-max" x-show="cols.length >= 8">
      &#9888; Sudah 8 warna (maks) — hapus salah satu untuk tambah baru
    </div>

    <p class="cp-hint">
      Klik foto untuk ambil warna. Maks 8 warna.<br>
      <span x-show="photos.length > 1">Klik thumbnail di bawah untuk ganti foto.</span>
    </p>
  </div>

</div>
</div>

<script>
function cpPicker() {
  return {
    photos:      [],   // [{k, url, fresh}]
    activeK:     '',
    cols:        [],   // [{id, hex, name}]
    hhex:        '',
    hx: 0, hy: 0,
    _ctx:        null,
    _colUid:     0,
    _photoUid:   0,
    _serverCount: 0,   // jumlah foto dari server — FilePond item pertama N = existing, skip
    _seen:       null,
    _timer:      null,
    _observer:   null,
    _scanTimer:  null,

    boot(serverPhotos, serverColorsJson) {
      this._seen = new Set();

      // Parse warna dari server
      try {
        const raw = JSON.parse(serverColorsJson || '[]');
        this.cols = (Array.isArray(raw) ? raw : []).map(c => ({
          id:   ++this._colUid,
          hex:  c.hex  || '',
          name: c.nama || '',
        })).filter(c => c.hex);
      } catch(e) { this.cols = []; }

      // Foto dari server
      this._serverCount = serverPhotos.length;
      serverPhotos.forEach((url, i) => {
        const k = 'sv' + i;
        this._seen.add(k);
        this.photos.push({ k, url, fresh: false });
      });

      if (this.photos.length > 0) {
        this.activeK = this.photos[0].k;
        this.$nextTick(() => this._load(0));
      }

      // Watch FilePond via MutationObserver + interval fallback
      this._startWatchingFilePond();
    },

    _startWatchingFilePond() {
      const self = this;

      const scan = () => {
        // Cara 1: FilePond _pond instance
        const fpEl = document.querySelector('.filepond--root');
        if (fpEl && fpEl._pond) {
          fpEl._pond.getFiles()
            .filter(f => f.file && (f.status === 2 || f.status === 5)) // 2=IDLE, 5=PROCESSING_COMPLETE
            .forEach(f => {
              const k = 'fp' + f.id;
              if (self._seen.has(k)) return;
              self._seen.add(k);
              const url = URL.createObjectURL(f.file);
              self._addPhoto(k, url, true);
            });
        }

        // Cara 2: scan FilePond items — SKIP item pertama sejumlah foto server
        // karena FilePond load ulang foto existing ke dalam itemnya juga
        const fpItems = Array.from(document.querySelectorAll('.filepond--item'));
        const newItems = fpItems.slice(self._serverCount); // hanya item yang benar-benar baru
        newItems.forEach(item => {
          // Gunakan id attribute FilePond sebagai stable key
          let itemKey = item.getAttribute('data-cp-key');
          if (!itemKey) {
            itemKey = 'fpi' + (++self._photoUid);
            item.setAttribute('data-cp-key', itemKey);
          }
          if (self._seen.has(itemKey)) return;

          // Coba canvas preview
          const cv = item.querySelector('canvas');
          if (cv && cv.width > 0 && cv.height > 0) {
            try {
              const dataUrl = cv.toDataURL('image/jpeg', 0.85);
              if (dataUrl && dataUrl.length > 500) {
                self._seen.add(itemKey);
                self._addPhoto(itemKey, dataUrl, true);
                return;
              }
            } catch(e) { /* tainted, lanjut ke fallback */ }
          }

          // Fallback: blob URL dari background-image
          const preview = item.querySelector('.filepond--image-preview');
          if (!preview) return;
          const m = getComputedStyle(preview).backgroundImage.match(/url\(["']?(blob:[^"')]+)["']?\)/);
          if (!m) return;
          self._seen.add(itemKey);
          self._addPhoto(itemKey, m[1], true);
        });
      };

      // Jalankan pertama kali dan tiap 1.5s
      this._timer = setInterval(scan, 1500);

      // MutationObserver untuk deteksi lebih cepat saat file ditambah
      const fpContainer = document.querySelector('.fi-fo-file-upload, [wire\\:key*="photos"], .filepond--root')
                       || document.body;
      this._observer = new MutationObserver(() => {
        clearTimeout(this._scanTimer);
        this._scanTimer = setTimeout(scan, 300);
      });
      this._observer.observe(fpContainer, { childList: true, subtree: true });
    },

    _addPhoto(k, url, fresh) {
      this.photos = [...this.photos, { k, url, fresh }];
      // Auto-load ke canvas hanya jika ini foto pertama sama sekali
      if (this.photos.length === 1) {
        this.activeK = k;
        this.$nextTick(() => this._load(0));
      }
    },

    switchPhoto(k) {
      const idx = this.photos.findIndex(p => p.k === k);
      if (idx < 0) return;
      this.activeK = k;
      this._load(idx);
    },

    _load(idx) {
      const p = this.photos[idx];
      if (!p) return;
      const cv = this.$refs.cv;
      if (!cv) return;

      const self = this;
      const draw = (img) => {
        const mW = 336, mH = 300;
        let w = img.naturalWidth || mW;
        let h = img.naturalHeight || mH;
        if (w < 1) w = mW;
        if (h < 1) h = mH;
        const r = Math.min(mW/w, mH/h, 1);
        cv.width  = Math.round(w * r);
        cv.height = Math.round(h * r);
        const ctx = cv.getContext('2d', { willReadFrequently: true });
        try {
          ctx.drawImage(img, 0, 0, cv.width, cv.height);
          self._ctx = ctx;
          self.activeK = p.k;
        } catch(e) { self._ctx = null; }
      };

      // blob/data URL: langsung
      if (p.url.startsWith('blob:') || p.url.startsWith('data:')) {
        const img = new Image();
        img.onload  = () => draw(img);
        img.onerror = () => { self._ctx = null; };
        img.src = p.url;
        return;
      }

      // URL server: fetch → blob → ObjectURL (bypass CORS taint di canvas)
      fetch(p.url)
        .then(res => {
          if (!res.ok) throw new Error(res.status);
          return res.blob();
        })
        .then(blob => {
          const ou = URL.createObjectURL(blob);
          const img = new Image();
          img.onload = () => { draw(img); URL.revokeObjectURL(ou); };
          img.onerror = () => { URL.revokeObjectURL(ou); self._ctx = null; };
          img.src = ou;
        })
        .catch(() => {
          // Fallback langsung (canvas mungkin tainted)
          const img = new Image();
          img.crossOrigin = 'anonymous';
          img.onload  = () => draw(img);
          img.onerror = () => { self._ctx = null; };
          img.src = p.url;
        });
    },

    _coords(e) {
      const cv = this.$refs.cv;
      const rc = cv.getBoundingClientRect();
      return {
        px: Math.max(0, Math.min(cv.width  - 1, Math.round((e.clientX - rc.left) * cv.width  / rc.width))),
        py: Math.max(0, Math.min(cv.height - 1, Math.round((e.clientY - rc.top)  * cv.height / rc.height))),
        dx: e.clientX - rc.left,
        dy: e.clientY - rc.top,
      };
    },

    _pixel(px, py) {
      if (!this._ctx) return null;
      try {
        const d = this._ctx.getImageData(px, py, 1, 1).data;
        if (d[3] < 20) return null; // transparan
        return '#' + [d[0],d[1],d[2]].map(v => v.toString(16).padStart(2,'0')).join('').toUpperCase();
      } catch(e) { return null; }
    },

    hover(e) {
      if (!this._ctx) return;
      const c = this._coords(e);
      this.hx = c.dx; this.hy = c.dy;
      this.$refs.hair.style.display = 'block';
      const h = this._pixel(c.px, c.py);
      if (h) this.hhex = h;
    },

    unhover() {
      this.$refs.hair.style.display = 'none';
      this.hhex = '';
    },

    pick(e) {
      if (this.cols.length >= 8 || !this._ctx) return;
      const c   = this._coords(e);
      const hex = this._pixel(c.px, c.py);
      if (!hex) return;
      if (this.cols.some(c => c.hex === hex)) return; // duplikat hex skip
      this.cols = [...this.cols, { id: ++this._colUid, hex, name: this._name(hex) }];
      this._push();
    },

    rename(id, val) {
      // Mutasi langsung pada object — Alpine 3 reactive via Proxy
      const c = this.cols.find(c => c.id === id);
      if (c) { c.name = val; this._push(); }
    },

    remove(id) {
      this.cols = this.cols.filter(c => c.id !== id);
      this._push();
    },

    _push() {
      const json = JSON.stringify(this.cols.map(c => ({ hex: c.hex, nama: c.name })));
      try { this.$wire.call('updateColors', json); } catch(e) {}
    },

    _name(hex) {
      const r=parseInt(hex.slice(1,3),16), g=parseInt(hex.slice(3,5),16), b=parseInt(hex.slice(5,7),16);
      const rn=r/255, gn=g/255, bn=b/255;
      const mx=Math.max(rn,gn,bn), mn=Math.min(rn,gn,bn), l=(mx+mn)/2;
      let h=0,s=0;
      if(mx!==mn){const d=mx-mn;s=l>.5?d/(2-mx-mn):d/(mx+mn);switch(mx){case rn:h=((gn-bn)/d+(gn<bn?6:0))/6;break;case gn:h=((bn-rn)/d+2)/6;break;case bn:h=((rn-gn)/d+4)/6;break;}}
      const hd=Math.round(h*360),sa=Math.round(s*100),li=Math.round(l*100);
      if(li<12)return'Black';if(li>88&&sa<15)return'White';
      if(sa<12)return li<30?'Dark Gray':li<55?'Gray':'Light Gray';
      const p=li<30?'Dark ':li>70?'Light ':'';
      if(hd<15||hd>=345)return p+'Red';if(hd<30)return sa>60?p+'Orange Red':p+'Brown';
      if(hd<45)return li<35?'Brown':p+'Orange';if(hd<65)return p+'Yellow';
      if(hd<80)return p+'Yellow Green';if(hd<150)return p+'Green';if(hd<165)return p+'Teal';
      if(hd<195)return p+'Cyan';if(hd<225)return p+'Sky Blue';if(hd<255)return p+'Blue';
      if(hd<270)return p+'Indigo';if(hd<290)return p+'Violet';if(hd<320)return p+'Purple';
      if(hd<345)return p+'Pink';return p+'Red';
    },
  };
}
</script>
