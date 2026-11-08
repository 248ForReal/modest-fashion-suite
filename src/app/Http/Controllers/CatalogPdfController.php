<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class CatalogPdfController extends Controller
{
    private function current(): ?array
    {
        $path = Setting::get('catalog_pdf_path');
        if (!$path || !Storage::disk('local')->exists($path)) {
            return null;
        }

        return [
            'path'    => $path,
            'name'    => Setting::get('catalog_pdf_name', 'ATELIER_E_Catalog.pdf'),
            'title'   => Setting::get('catalog_pdf_title', 'E-Catalog'),
            'version' => (string) Setting::get('catalog_pdf_version', '1'),
            'size'    => Storage::disk('local')->size($path),
        ];
    }

    public function show()
    {
        $pdf = $this->current();

        return view('catalogpdf.show', [
            'pdf'         => $pdf,
            'fileUrl'     => $pdf ? route('catalogpdf.file', ['v' => $pdf['version']]) : null,
            'downloadUrl' => $pdf ? route('catalogpdf.download', ['v' => $pdf['version']]) : null,
        ]);
    }

    public function file()
    {
        $pdf = $this->current() ?? abort(404);

        return response()->file(Storage::disk('local')->path($pdf['path']), [
            'Content-Type'  => 'application/pdf',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    public function download()
    {
        $pdf = $this->current() ?? abort(404);

        return Storage::disk('local')->download($pdf['path'], $pdf['name'], [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
