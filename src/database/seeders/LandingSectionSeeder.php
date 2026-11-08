<?php

namespace Database\Seeders;

use App\Models\LandingSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Demo landing-page content. Everything here is fictional: the brand, copy,
 * partners and images (generated SVG placeholders) are sample data only.
 */
class LandingSectionSeeder extends Seeder
{
    public function run(): void
    {
        $this->hero();
        $this->about();
        $this->dna();
        $this->craft();
        $this->collections();
        $this->process();
        $this->partners();
        $this->testimonials();
        $this->contact();
    }

    private function section(string $key, array $data): LandingSection
    {
        return LandingSection::updateOrCreate(['key' => $key], $data);
    }

    private function item(LandingSection $section, int $urutan, array $data): void
    {
        $section->items()->updateOrCreate(
            ['landing_section_id' => $section->id, 'urutan' => $urutan],
            array_merge($data, ['urutan' => $urutan])
        );
    }

    /** Writes a gradient SVG placeholder to the public disk and returns its relative path. */
    private function placeholder(string $name, string $label, int $hue): string
    {
        $path = "landing/demo-{$name}.svg";
        $h2 = ($hue + 40) % 360;
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 1000" role="img" aria-label="{$label}">
          <defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="hsl({$hue},38%,42%)"/><stop offset="1" stop-color="hsl({$h2},45%,22%)"/>
          </linearGradient></defs>
          <rect width="800" height="1000" fill="url(#g)"/>
          <text x="400" y="510" text-anchor="middle" font-family="Georgia,serif" font-size="44" fill="rgba(255,255,255,.85)">{$label}</text>
        </svg>
        SVG;
        Storage::disk('public')->put($path, $svg);

        return $path;
    }

    private function hero(): void
    {
        $s = $this->section('hero', [
            'display_type' => 'carousel',
            'is_active'    => true,
            'urutan'       => 1,
            'eyebrow_en'   => 'Modern Ethnic & Modest Fashion',
            'eyebrow_id'   => 'Modern Ethnic & Modest Fashion',
            'title_en'     => 'Wear Heritage. Live Purpose.',
            'title_id'     => 'Kenakan Warisan. Hidupkan Makna.',
            'subtitle_en'  => "Textile heritage, reimagined for today's modest wardrobe.",
            'subtitle_id'  => 'Warisan tekstil, dihadirkan kembali untuk gaya berbusana modest masa kini.',
            'cta_label_en' => 'Explore Collection',
            'cta_label_id' => 'Jelajahi Koleksi',
            'cta_url'      => '/produk',
        ]);

        $slides = [
            ['Rooted in Culture, Designed for Tomorrow', 'Berakar pada Budaya, Dirancang untuk Masa Depan'],
            ['Contemporary Silhouettes', 'Siluet Kontemporer'],
            ['Tradition in Every Detail', 'Tradisi dalam Setiap Detail'],
        ];
        foreach ($slides as $i => [$en, $id]) {
            $this->item($s, $i + 1, [
                'image_path' => $this->placeholder('hero-' . ($i + 1), 'Hero ' . ($i + 1), 340 + $i * 25),
                'title_en'   => $en,
                'title_id'   => $id,
            ]);
        }
    }

    private function about(): void
    {
        $s = $this->section('about', [
            'display_type' => 'static',
            'is_active'    => true,
            'urutan'       => 2,
            'eyebrow_en'   => 'Our Story',
            'eyebrow_id'   => 'Kisah Kami',
            'title_en'     => 'Local Roots, Wider Reach',
            'title_id'     => 'Akar Lokal, Jangkauan Lebih Luas',
            'body_en'      => 'A sample brand story for demonstration purposes. Atelier is a fictional garment studio that grew from a small workshop into a production house serving retail and wholesale customers.',
            'body_id'      => 'Contoh kisah merek untuk keperluan demonstrasi. Atelier adalah studio busana fiktif yang tumbuh dari bengkel kecil menjadi rumah produksi untuk pelanggan ritel dan grosir.',
        ]);

        $timeline = [
            ['Year 1', 'Tahun 1', 'Small Workshop', 'Bengkel Kecil', 'Started with a handful of tailors and a few product lines.', 'Dimulai dari beberapa penjahit dan sedikit lini produk.'],
            ['Year 4', 'Tahun 4', 'Ready-to-Wear', 'Ready-to-Wear', 'Expanded into ready-to-wear with a dedicated production team.', 'Berkembang ke ready-to-wear dengan tim produksi khusus.'],
            ['Today', 'Hari Ini', 'Looking Outward', 'Menatap ke Luar', 'Digital catalog, attendance and inventory tooling for the whole team.', 'Katalog digital, absensi, dan inventori untuk seluruh tim.'],
        ];
        foreach ($timeline as $i => [$lEn, $lId, $tEn, $tId, $bEn, $bId]) {
            $data = [
                'label_en' => $lEn, 'label_id' => $lId,
                'title_en' => $tEn, 'title_id' => $tId,
                'body_en'  => $bEn, 'body_id'  => $bId,
            ];
            if ($i === 0) {
                $data['image_path'] = $this->placeholder('about', 'Our Story', 20);
            }
            $this->item($s, $i + 1, $data);
        }
    }

    private function dna(): void
    {
        $s = $this->section('dna', [
            'display_type' => 'grid',
            'is_active'    => true,
            'urutan'       => 3,
            'eyebrow_en'   => '01 / Brand DNA',
            'eyebrow_id'   => '01 / DNA Merek',
            'title_en'     => 'Three Ideas Shape Our Design Language',
            'title_id'     => 'Tiga Gagasan yang Membentuk Bahasa Desain Kami',
        ]);

        $values = [
            ['Heritage', 'Warisan', 'Textile traditions, local materials and cultural meaning.', 'Tradisi tekstil, material lokal, dan makna budaya.'],
            ['Modern', 'Modern', 'Clean silhouettes and contemporary styling.', 'Siluet bersih dan gaya kontemporer.'],
            ['Responsible', 'Bertanggung Jawab', 'Local creative resources and thoughtful production.', 'Sumber daya kreatif lokal dan produksi yang penuh pertimbangan.'],
        ];
        foreach ($values as $i => [$tEn, $tId, $bEn, $bId]) {
            $n = sprintf('%02d', $i + 1);
            $this->item($s, $i + 1, [
                'image_path' => $this->placeholder("dna-{$n}", $tEn, 160 + $i * 40),
                'label_en' => $n, 'label_id' => $n,
                'title_en' => $tEn, 'title_id' => $tId,
                'body_en'  => $bEn, 'body_id'  => $bId,
            ]);
        }
    }

    private function craft(): void
    {
        $s = $this->section('craft', [
            'display_type' => 'static',
            'is_active'    => true,
            'urutan'       => 4,
            'eyebrow_en'   => '02 / The Textile',
            'eyebrow_id'   => '02 / Tekstil',
            'title_en'     => 'Traditional Fabric, Contemporary Language',
            'title_id'     => 'Kain Tradisional, Bahasa Kontemporer',
            'body_en'      => 'Sample copy: traditional fabrics combined with modern design and in-house finishing techniques.',
            'body_id'      => 'Contoh teks: kain tradisional dipadukan dengan desain modern dan teknik finishing in-house.',
        ]);

        $this->item($s, 1, [
            'image_path' => $this->placeholder('craft-1', 'Craft', 280),
            'title_en' => 'Craft', 'title_id' => 'Kriya',
            'body_en'  => 'In-house techniques applied to selected pieces.',
            'body_id'  => 'Teknik in-house diterapkan pada koleksi pilihan.',
        ]);
        $this->item($s, 2, [
            'image_path' => $this->placeholder('craft-2', 'Design', 300),
            'title_en' => 'Design', 'title_id' => 'Desain',
            'body_en'  => 'Modern silhouettes with cultural meaning.',
            'body_id'  => 'Siluet modern dengan makna budaya.',
        ]);
    }

    private function collections(): void
    {
        $s = $this->section('collections', [
            'display_type' => 'carousel',
            'is_active'    => true,
            'urutan'       => 5,
            'eyebrow_en'   => '03 / The Collections',
            'eyebrow_id'   => '03 / Koleksi',
            'title_en'     => 'Heritage, Styled for Today',
            'title_id'     => 'Warisan, Digayakan untuk Hari Ini',
            'subtitle_en'  => 'Modest. Stylish. Meaningful.',
            'subtitle_id'  => 'Modest. Stylish. Bermakna.',
            'cta_label_en' => 'View Full Catalog',
            'cta_label_id' => 'Lihat Katalog Lengkap',
            'cta_url'      => '/produk',
        ]);

        $products = [
            ['Purple', 'Ungu', 'Casual Ethnic', 285],
            ['Maroon', 'Maroon', 'Casual Ethnic', 350],
            ['Grey', 'Abu-abu', 'Casual Ethnic', 220],
            ['Navy', 'Navy', 'Casual Ethnic', 230],
            ['Sky Blue', 'Biru Langit', 'Modern Modest', 200],
            ['Lilac', 'Lilac', 'Statement Pieces', 270],
        ];
        foreach ($products as $i => [$en, $id, $cat, $hue]) {
            $this->item($s, $i + 1, [
                'image_path' => $this->placeholder('collection-' . ($i + 1), "{$cat} — {$en}", $hue),
                'label_en' => $cat, 'label_id' => $cat,
                'title_en' => $en, 'title_id' => $id,
            ]);
        }
    }

    private function process(): void
    {
        $s = $this->section('process', [
            'display_type' => 'static',
            'is_active'    => true,
            'urutan'       => 6,
            'eyebrow_en'   => 'From Concept to Finished Garment',
            'eyebrow_id'   => 'Dari Konsep hingga Busana Jadi',
            'title_en'     => 'How to Work with Atelier',
            'title_id'     => 'Cara Berkolaborasi dengan Atelier',
            'subtitle_en'  => 'For selected buyer and collaboration projects.',
            'subtitle_id'  => 'Untuk proyek pembeli dan kolaborasi terpilih.',
            'body_en'      => 'MOQ, pricing, lead time and customization are confirmed per project.',
            'body_id'      => 'MOQ, harga, lead time, dan kustomisasi dikonfirmasi sesuai proyek.',
        ]);

        $steps = [
            ['Design', 'Desain', 'Consultation and concept. Share your ideas or references.', 'Konsultasi dan konsep. Bagikan ide atau referensi Anda.'],
            ['Material', 'Material', 'Textile and color selection.', 'Pemilihan tekstil dan warna.'],
            ['Sample', 'Sampel', 'Prototype development to match your expectations.', 'Pengembangan prototipe sesuai harapan Anda.'],
            ['Production', 'Produksi', 'Confirmed quantity and workflow, with quality control at every step.', 'Kuantitas dan alur kerja terkonfirmasi, dengan kontrol kualitas di tiap tahap.'],
            ['Delivery', 'Pengiriman', 'Secure packaging and shipping.', 'Kemasan aman dan pengiriman.'],
        ];
        foreach ($steps as $i => [$tEn, $tId, $bEn, $bId]) {
            $n = sprintf('%02d', $i + 1);
            $this->item($s, $i + 1, [
                'image_path' => $this->placeholder("process-{$n}", $tEn, 30 + $i * 30),
                'label_en' => $n, 'label_id' => $n,
                'title_en' => $tEn, 'title_id' => $tId,
                'body_en'  => $bEn, 'body_id'  => $bId,
            ]);
        }
    }

    private function partners(): void
    {
        $s = $this->section('partners', [
            'display_type' => 'static',
            'is_active'    => true,
            'urutan'       => 7,
            'eyebrow_en'   => 'Together for a Stronger Local Fashion Ecosystem',
            'eyebrow_id'   => 'Bersama Membangun Ekosistem Fashion Lokal yang Lebih Kuat',
            'title_en'     => 'Partners & Selected Activities',
            'title_id'     => 'Mitra & Kegiatan Terpilih',
        ]);

        $this->item($s, 1, ['image_path' => $this->placeholder('partners', 'Partners', 200)]);

        $partners = [
            ['Sample Corporate Partner', 'Community development program', 'Program pengembangan komunitas'],
            ['Sample Vocational School', 'Education / skills collaboration', 'Kolaborasi pendidikan / keterampilan'],
            ['Sample Craft Council', 'Local creative ecosystem', 'Ekosistem kreatif lokal'],
        ];
        foreach ($partners as $i => [$name, $bodyEn, $bodyId]) {
            $this->item($s, $i + 2, [
                'title_en' => $name, 'title_id' => $name,
                'body_en'  => $bodyEn, 'body_id' => $bodyId,
            ]);
        }
    }

    private function testimonials(): void
    {
        $s = $this->section('testimonials', [
            'display_type' => 'carousel',
            'is_active'    => true,
            'urutan'       => 8,
            'eyebrow_en'   => 'Customer Experiences',
            'eyebrow_id'   => 'Pengalaman Pelanggan',
            'title_en'     => "Worn on Life's Meaningful Moments",
            'title_id'     => 'Dikenakan di Momen-Momen Bermakna',
        ]);

        for ($i = 1; $i <= 3; $i++) {
            $this->item($s, $i, [
                'image_path' => $this->placeholder('testimonial-' . $i, 'Testimonial ' . $i, 250 + $i * 20),
            ]);
        }
    }

    private function contact(): void
    {
        $this->section('contact', [
            'display_type' => 'static',
            'is_active'    => true,
            'urutan'       => 9,
            'title_en'     => "Let's Create Something Distinctive",
            'title_id'     => 'Mari Ciptakan Sesuatu yang Khas',
            'subtitle_en'  => 'Modern Ethnic & Modest Fashion',
            'subtitle_id'  => 'Modern Ethnic & Modest Fashion',
            'cta_label_en' => 'Explore Collection',
            'cta_label_id' => 'Jelajahi Koleksi',
            'cta_url'      => '/produk',
        ]);
    }
}
