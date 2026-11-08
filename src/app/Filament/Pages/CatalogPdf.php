<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

class CatalogPdf extends Page
{
    use WithFileUploads;


    protected string $view = 'filament.pages.catalog-pdf';
    protected static ?string $navigationLabel = 'Katalog PDF';
    protected static ?string $title = 'Katalog PDF & QR';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';
    protected static \UnitEnum|string|null $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 3;

    public $pdfFile = null;
    public string $catalogTitle = '';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    public function mount(): void
    {
        $this->catalogTitle = (string) Setting::get('catalog_pdf_title', 'E-Catalog TEI 2026');
    }

    public function getCurrent(): ?array
    {
        $path = Setting::get('catalog_pdf_path');
        if (!$path || !Storage::disk('local')->exists($path)) {
            return null;
        }

        return [
            'name'    => Setting::get('catalog_pdf_name', 'catalog.pdf'),
            'size'    => Storage::disk('local')->size($path),
            'updated' => (int) Setting::get('catalog_pdf_version', 0),
        ];
    }

    public function savePdf(): void
    {
        $this->validate([
            'pdfFile' => ['required', 'file', 'mimes:pdf', 'max:12288'],
        ], [
            'pdfFile.required' => 'Pilih file PDF terlebih dahulu.',
            'pdfFile.mimes'    => 'File harus berformat PDF.',
            'pdfFile.max'      => 'Ukuran PDF maksimal 12 MB.',
            'pdfFile.uploaded' => 'Upload gagal. Pastikan ukuran PDF di bawah 12 MB lalu coba lagi.',
        ]);

        $original = pathinfo($this->pdfFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = (preg_replace('/[^A-Za-z0-9._-]+/', '_', $original) ?: 'catalog') . '.pdf';

        $old = Setting::get('catalog_pdf_path');
        $path = $this->pdfFile->storeAs('catalog', Str::random(24) . '.pdf', ['disk' => 'local']);

        Setting::set('catalog_pdf_path', $path);
        Setting::set('catalog_pdf_name', $safeName);
        Setting::set('catalog_pdf_version', (string) time());

        if ($old && $old !== $path) {
            Storage::disk('local')->delete($old);
        }

        $this->reset('pdfFile');

        Notification::make()
            ->title('PDF katalog berhasil diperbarui')
            ->body('QR yang sudah tercetak tetap berlaku — isi halaman otomatis berganti.')
            ->success()
            ->send();
    }

    public function saveTitle(): void
    {
        $this->validate(['catalogTitle' => ['required', 'string', 'max:60']]);
        Setting::set('catalog_pdf_title', trim($this->catalogTitle));

        Notification::make()->title('Judul katalog disimpan')->success()->send();
    }
}
