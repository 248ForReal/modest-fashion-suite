<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GarmentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kategori' => 'Kasual', 'sub_kategori' => 'Atasan', 'contoh_pakaian' => 'Kaos polos', 'keterangan' => 'Atasan berbahan katun/jersey, potongan lurus, tanpa banyak detail', 'nilai_jahit' => 25.00, 'nilai_desain' => 25.00, 'nilai_pola' => 28.28],
            ['kategori' => 'Kasual', 'sub_kategori' => 'Atasan', 'contoh_pakaian' => 'Kaos sablon', 'keterangan' => 'Kaos polos dengan visual sablon digital/manual, model regular fit', 'nilai_jahit' => 25.00, 'nilai_desain' => 25.00, 'nilai_pola' => 28.28],
            ['kategori' => 'Kasual', 'sub_kategori' => 'Atasan', 'contoh_pakaian' => 'Kemeja santai', 'keterangan' => 'Kemeja bahan katun/rayon, kerah santai, cutting longgar, lengan pendek/panjang', 'nilai_jahit' => 25.00, 'nilai_desain' => 25.00, 'nilai_pola' => 28.28],
            ['kategori' => 'Kasual', 'sub_kategori' => 'Atasan Modest', 'contoh_pakaian' => 'Tunik panjang', 'keterangan' => 'Tunik longgar, menutupi pinggul, terkadang detail bordir, umumnya berbahan katun, linen, atau rayon', 'nilai_jahit' => 35.00, 'nilai_desain' => 35.00, 'nilai_pola' => 39.05],
            ['kategori' => 'Kasual', 'sub_kategori' => 'Atasan Ethnic', 'contoh_pakaian' => 'Blus batik longgar', 'keterangan' => 'Blus berlengan panjang/3/4, motif batik printing/tulis, material katun, fit regular', 'nilai_jahit' => 30.00, 'nilai_desain' => 30.00, 'nilai_pola' => 34.64],
            ['kategori' => 'Kasual', 'sub_kategori' => 'Luar', 'contoh_pakaian' => 'Hoodie', 'keterangan' => 'Jaket bertudung, berbahan fleece/katun, saku depan, resleting/kancing', 'nilai_jahit' => 39.05, 'nilai_desain' => 39.05, 'nilai_pola' => 42.43],
            ['kategori' => 'Kasual', 'sub_kategori' => 'Luar', 'contoh_pakaian' => 'Cardigan', 'keterangan' => 'Pakaian luar terbuka tanpa kancing/resleting, berbahan rajut/kain', 'nilai_jahit' => 39.05, 'nilai_desain' => 39.05, 'nilai_pola' => 42.43],
            ['kategori' => 'Kasual', 'sub_kategori' => 'Luar Ethnic', 'contoh_pakaian' => 'Outer tenun/kimono etnik', 'keterangan' => 'Outer/layer tanpa kancing dengan motif/aksen tenun, batik, atau etnik daerah', 'nilai_jahit' => 45.00, 'nilai_desain' => 45.00, 'nilai_pola' => 50.00],
            ['kategori' => 'Kasual', 'sub_kategori' => 'Bawahan', 'contoh_pakaian' => 'Jeans', 'keterangan' => 'Celana denim, potongan lurus/skinny/regular, jahitan kuat, detail saku dan rivet', 'nilai_jahit' => 36.06, 'nilai_desain' => 36.06, 'nilai_pola' => 39.05],
            ['kategori' => 'Kasual', 'sub_kategori' => 'Bawahan', 'contoh_pakaian' => 'Celana pendek', 'keterangan' => 'Celana di atas lutut, bahan katun/kanvas/jeans, santai, regular fit', 'nilai_jahit' => 36.06, 'nilai_desain' => 36.06, 'nilai_pola' => 39.05],
            ['kategori' => 'Kasual', 'sub_kategori' => 'Bawahan Modest', 'contoh_pakaian' => 'Celana palazzo/longgar', 'keterangan' => 'Celana longgar, pinggang karet, bahan jatuh (cupal, rayon, crepe), biasanya polos', 'nilai_jahit' => 30.00, 'nilai_desain' => 30.00, 'nilai_pola' => 34.64],
            ['kategori' => 'Kasual', 'sub_kategori' => 'Bawahan Ethnic', 'contoh_pakaian' => 'Rok batik casual', 'keterangan' => 'Rok selutut atau panjang, motif batik, pinggang karet/rits, lipatan sederhana', 'nilai_jahit' => 35.00, 'nilai_desain' => 35.00, 'nilai_pola' => 39.05],
            ['kategori' => 'Formal', 'sub_kategori' => 'Atasan', 'contoh_pakaian' => 'Kemeja kerja', 'keterangan' => 'Kemeja formal bahan TC/katun, kerah resmi, manset, saku, jahitan rapi', 'nilai_jahit' => 69.46, 'nilai_desain' => 69.46, 'nilai_pola' => 92.20],
            ['kategori' => 'Formal', 'sub_kategori' => 'Atasan', 'contoh_pakaian' => 'Blazer', 'keterangan' => 'Pakaian luar jas/lapis furing, siluet tegas, kerah formal, kadang bantalan bahu', 'nilai_jahit' => 69.46, 'nilai_desain' => 69.46, 'nilai_pola' => 92.20],
            ['kategori' => 'Formal', 'sub_kategori' => 'Atasan', 'contoh_pakaian' => 'Jas', 'keterangan' => 'Jas formal lengkap, full furing, beberapa kancing besar, rekat pinggang rapi', 'nilai_jahit' => 69.46, 'nilai_desain' => 69.46, 'nilai_pola' => 92.20],
            ['kategori' => 'Formal', 'sub_kategori' => 'Atasan Modest', 'contoh_pakaian' => 'Gamis kerja/muslim', 'keterangan' => 'Gaun panjang, longgar, kerah basic/mandarin, material non-transparan, kadang mix motif etnik', 'nilai_jahit' => 75.00, 'nilai_desain' => 75.00, 'nilai_pola' => 86.02],
            ['kategori' => 'Formal', 'sub_kategori' => 'Atasan Ethnic', 'contoh_pakaian' => 'Dress batik formal', 'keterangan' => 'Dress corak batik, jahitan detail, model midi/panjang, material batik tulis/prada', 'nilai_jahit' => 65.00, 'nilai_desain' => 65.00, 'nilai_pola' => 75.00],
            ['kategori' => 'Formal', 'sub_kategori' => 'Luar', 'contoh_pakaian' => 'Jas formal', 'keterangan' => 'Setelan jas, bahan wool/poliester blend, konstruksi tegas, cutting body fit', 'nilai_jahit' => 83.85, 'nilai_desain' => 83.22, 'nilai_pola' => 109.66],
            ['kategori' => 'Formal', 'sub_kategori' => 'Bawahan', 'contoh_pakaian' => 'Rok pensil', 'keterangan' => 'Rok mini-midi panjang, potongan sempit, belahan belakang/samping, ziper/lapisan', 'nilai_jahit' => 58.31, 'nilai_desain' => 58.31, 'nilai_pola' => 78.10],
            ['kategori' => 'Olahraga', 'sub_kategori' => 'Atasan', 'contoh_pakaian' => 'Jersey', 'keterangan' => 'Atasan olahraga, dryfit/polyester, sirkulasi udara baik, kadang detail piping', 'nilai_jahit' => 43.01, 'nilai_desain' => 43.01, 'nilai_pola' => 46.10],
            ['kategori' => 'Olahraga', 'sub_kategori' => 'Bawahan', 'contoh_pakaian' => 'Training pants', 'keterangan' => 'Celana olahraga, pinggang karet, bahan quick dry/spandex, model longgar/fit', 'nilai_jahit' => 46.10, 'nilai_desain' => 43.01, 'nilai_pola' => 46.10],
            ['kategori' => 'Anak & Bayi', 'sub_kategori' => 'Atasan', 'contoh_pakaian' => 'Kaos anak', 'keterangan' => 'Kaos lengan pendek, cutting simple, bahan katun lembut, motif/print karakter', 'nilai_jahit' => 36.06, 'nilai_desain' => 36.06, 'nilai_pola' => 43.01],
            ['kategori' => 'Anak & Bayi', 'sub_kategori' => 'Atasan Ethnic', 'contoh_pakaian' => 'Blus etnik anak', 'keterangan' => 'Blus motif etnik sederhana, aplikasi bordir, bahan adem, regular/loose fit', 'nilai_jahit' => 35.00, 'nilai_desain' => 35.00, 'nilai_pola' => 39.05],
            ['kategori' => 'Anak & Bayi', 'sub_kategori' => 'Dress', 'contoh_pakaian' => 'Dress anak', 'keterangan' => 'Gaun casual/pesta anak, material cotton/poly, ruffle sederhana, kadang sablon/bordir', 'nilai_jahit' => 43.01, 'nilai_desain' => 43.01, 'nilai_pola' => 50.00],
            ['kategori' => 'Anak & Bayi', 'sub_kategori' => 'Dress Ethnic', 'contoh_pakaian' => 'Dress etnik anak', 'keterangan' => 'Gaun anak motif batik/lurik/tenun, corak simpel, bahan lembut dan sejuk', 'nilai_jahit' => 43.01, 'nilai_desain' => 43.01, 'nilai_pola' => 50.00],
            ['kategori' => 'Seragam', 'sub_kategori' => 'Sekolah', 'contoh_pakaian' => 'Seragam pramuka', 'keterangan' => 'Setelan kemeja dan rok/celana, warna coklat krem, atribut badge seragam', 'nilai_jahit' => 50.00, 'nilai_desain' => 50.00, 'nilai_pola' => 57.01],
            ['kategori' => 'Seragam', 'sub_kategori' => 'Kerja/Profesi', 'contoh_pakaian' => 'Seragam kantor', 'keterangan' => 'Set standar corporate, celana/rok bahan, desain sederhana, bordir logo, cutting fit', 'nilai_jahit' => 54.08, 'nilai_desain' => 54.08, 'nilai_pola' => 64.03],
            ['kategori' => 'Khusus', 'sub_kategori' => 'Gaun & Busana Adat', 'contoh_pakaian' => 'Gaun pengantin', 'keterangan' => 'Dress formal panjang bersusun/bertumpuk, material berlapis, brokat/payet/pita, umumnya full lining', 'nilai_jahit' => 100.00, 'nilai_desain' => 100.00, 'nilai_pola' => 120.42],
            ['kategori' => 'Khusus', 'sub_kategori' => 'Gaun Modest', 'contoh_pakaian' => 'Abaya, kaftan', 'keterangan' => 'Gaun longgar, panjang, bahan satin/tile/crepe, detail bordir/payet minimalis, lengan longgar', 'nilai_jahit' => 85.00, 'nilai_desain' => 85.00, 'nilai_pola' => 100.00],
            ['kategori' => 'Khusus', 'sub_kategori' => 'Gaun Ethnic', 'contoh_pakaian' => 'Maxi dress tribal/tenun', 'keterangan' => 'Dress panjang/casual, motif etnik, kain tenun/batik tulis/lurik, kadang kombinasikan kain polos', 'nilai_jahit' => 80.00, 'nilai_desain' => 80.00, 'nilai_pola' => 89.44],
            ['kategori' => 'Khusus', 'sub_kategori' => 'Pakaian Dalam', 'contoh_pakaian' => 'Bra', 'keterangan' => 'Kutang, berbagai model cup, padding/furing, material spandex/cotton, jahitan elastic banyak', 'nilai_jahit' => 40.31, 'nilai_desain' => 40.31, 'nilai_pola' => 50.00],
            ['kategori' => 'Khusus', 'sub_kategori' => 'Tidur/Hamil', 'contoh_pakaian' => 'Daster, piyama', 'keterangan' => 'Gaun tidur longgar, kadang busui friendly, bahan rayon/katun adem, motif floral', 'nilai_jahit' => 46.10, 'nilai_desain' => 40.31, 'nilai_pola' => 50.00],
            ['kategori' => 'Mukena', 'sub_kategori' => 'Mukena Standar', 'contoh_pakaian' => 'Mukena polos dua setelan', 'keterangan' => 'Atasan panjang + bawahan, bahan rayon/cotton, tanpa motif/bordir, pinggang karet, tas serbaguna', 'nilai_jahit' => 40.00, 'nilai_desain' => 35.00, 'nilai_pola' => 40.00],
            ['kategori' => 'Mukena', 'sub_kategori' => 'Mukena Bordir', 'contoh_pakaian' => 'Mukena bordir klasik, bordir mesin', 'keterangan' => 'Atasan panjang, bawahan/non setelan, aplikasi bordir floral/kerancang, bahan rayon/sutra, sering motif renda', 'nilai_jahit' => 50.00, 'nilai_desain' => 50.00, 'nilai_pola' => 52.00],
            ['kategori' => 'Mukena', 'sub_kategori' => 'Mukena Travel', 'contoh_pakaian' => 'Mukena ringkas bahan ringan', 'keterangan' => 'Model ringkas bisa dilipat, bahan parasut/rayon ringan, tanpa detail berlebihan, tas mini', 'nilai_jahit' => 30.00, 'nilai_desain' => 30.00, 'nilai_pola' => 32.00],
            ['kategori' => 'Mukena', 'sub_kategori' => 'Mukena Anak', 'contoh_pakaian' => 'Mukena anak motif lucu', 'keterangan' => 'Ukuran kecil, motif kartun/karakter anak, bahan rayon/katun, detail renda, serat adem', 'nilai_jahit' => 34.00, 'nilai_desain' => 32.00, 'nilai_pola' => 37.00],
            ['kategori' => 'Mukena', 'sub_kategori' => 'Mukena Modest Ethnic', 'contoh_pakaian' => 'Mukena tenun/batik/songket/jumputan', 'keterangan' => 'Material utama kain tradisional (tenun, songket, batik), motif khas, kadang aplikasi renda/bordir lokal', 'nilai_jahit' => 55.00, 'nilai_desain' => 60.00, 'nilai_pola' => 60.00],
            ['kategori' => 'Aksesoris', 'sub_kategori' => 'Kepala', 'contoh_pakaian' => 'Topi', 'keterangan' => 'Topi kain/rajut printing, proteksi/simbol fashion, pola sederhana', 'nilai_jahit' => 29.15, 'nilai_desain' => 29.15, 'nilai_pola' => 22.36],
            ['kategori' => 'Aksesoris', 'sub_kategori' => 'Kepala Modest', 'contoh_pakaian' => 'Hijab, scarf', 'keterangan' => 'Penutup kepala/kerudung, bahan voal/chiffon/katun/sutra, potongan rectangular/segitiga, bordir/tassel opsional', 'nilai_jahit' => 25.00, 'nilai_desain' => 25.00, 'nilai_pola' => 25.00],
        ];

        foreach ($data as $row) {
            DB::table('garment_types')->insertOrIgnore(array_merge($row, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
