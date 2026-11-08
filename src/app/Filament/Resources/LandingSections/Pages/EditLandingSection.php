<?php

namespace App\Filament\Resources\LandingSections\Pages;

use App\Filament\Resources\LandingSections\LandingSectionResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\HtmlString;

class EditLandingSection extends EditRecord
{
    protected static string $resource = LandingSectionResource::class;

    protected function getHeaderActions(): array
    {
        $previewToken = hash('sha256', config('app.key') . 'landing_preview');
        $sectionKey   = $this->getRecord()->key;
        $previewUrl   = url('/landing-section-preview?token=' . $previewToken . '&section=' . $sectionKey);
        $fullUrl      = url('/landing-preview?token=' . $previewToken);

        return [
            Action::make('preview')
                ->label('Lihat Section')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->modalHeading('Preview Section: ' . strtoupper($sectionKey))
                ->modalWidth('7xl')
                ->modalFooterActionsAlignment('right')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup')
                ->modalContent(function () use ($previewUrl, $fullUrl, $sectionKey) {
                    return new HtmlString(<<<HTML
<div
  x-data="{ device: 'mobile' }"
  style="display:flex;flex-direction:column;gap:0"
>
  <!-- Toolbar -->
  <div style="display:flex;align-items:center;gap:8px;padding:10px 16px;background:#111;border-radius:8px 8px 0 0;flex-wrap:wrap">
    <span style="color:#666;font-size:11px;letter-spacing:.08em;text-transform:uppercase;margin-right:4px;flex-shrink:0">Section: {$sectionKey}</span>

    <button type="button" @click="device='mobile'"
      :style="device==='mobile' ? 'background:#A85C43;color:#fff;border-color:#A85C43' : 'background:transparent;color:#999;border-color:#444'"
      style="display:flex;align-items:center;gap:6px;border:1px solid;border-radius:6px;padding:5px 12px;font-size:12px;font-weight:600;cursor:pointer;transition:all .15s;font-family:inherit">
      <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="7" y="2" width="10" height="20" rx="2" stroke-width="2"/><circle cx="12" cy="18" r="1" fill="currentColor"/></svg> HP
    </button>

    <button type="button" @click="device='tablet'"
      :style="device==='tablet' ? 'background:#A85C43;color:#fff;border-color:#A85C43' : 'background:transparent;color:#999;border-color:#444'"
      style="display:flex;align-items:center;gap:6px;border:1px solid;border-radius:6px;padding:5px 12px;font-size:12px;font-weight:600;cursor:pointer;transition:all .15s;font-family:inherit">
      <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2" stroke-width="2"/><circle cx="12" cy="18" r="1" fill="currentColor"/></svg> Tablet
    </button>

    <button type="button" @click="device='desktop'"
      :style="device==='desktop' ? 'background:#A85C43;color:#fff;border-color:#A85C43' : 'background:transparent;color:#999;border-color:#444'"
      style="display:flex;align-items:center;gap:6px;border:1px solid;border-radius:6px;padding:5px 12px;font-size:12px;font-weight:600;cursor:pointer;transition:all .15s;font-family:inherit">
      <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="1" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M8 21h8M12 17v4"/></svg> Web
    </button>

    <a href="{$fullUrl}" target="_blank"
      style="display:flex;align-items:center;gap:5px;margin-left:auto;color:#777;font-size:11px;text-decoration:none;padding:5px 10px;border:1px solid #444;border-radius:6px;white-space:nowrap;transition:color .15s"
      onmouseover="this.style.color='#ddd'" onmouseout="this.style.color='#777'">
      <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
      Landing Page Penuh
    </a>
  </div>

  <!-- Frame area -->
  <div style="background:#1a1a1a;border-radius:0 0 8px 8px;padding:20px;display:flex;justify-content:center;align-items:flex-start;min-height:500px;overflow:auto">

    <!-- HP frame 390px -->
    <div x-show="device==='mobile'" style="width:390px;max-height:780px;flex-shrink:0;border-radius:44px;overflow:hidden;box-shadow:0 0 0 8px #1c1c1e,0 0 0 11px #3a3a3c,0 24px 60px rgba(0,0,0,.6);position:relative">
      <div style="position:absolute;top:0;left:50%;transform:translateX(-50%);width:126px;height:34px;background:#1c1c1e;border-radius:0 0 22px 22px;z-index:10"></div>
      <iframe src="{$previewUrl}&vw=390" style="width:390px;height:780px;border:none;display:block;background:#F7F3EC" loading="lazy"></iframe>
    </div>

    <!-- Tablet frame 768px -->
    <div x-show="device==='tablet'" style="width:768px;max-height:700px;flex-shrink:0;border-radius:20px;overflow:hidden;box-shadow:0 0 0 8px #1c1c1e,0 0 0 11px #3a3a3c,0 24px 60px rgba(0,0,0,.6)">
      <iframe src="{$previewUrl}&vw=768" style="width:768px;height:700px;border:none;display:block;background:#F7F3EC" loading="lazy"></iframe>
    </div>

    <!-- Desktop frame full-width -->
    <div x-show="device==='desktop'" style="width:100%;max-width:1100px;border-radius:8px;overflow:hidden;box-shadow:0 0 0 1px #3a3a3c,0 16px 48px rgba(0,0,0,.5)">
      <div style="height:32px;background:#2c2c2e;display:flex;align-items:center;padding:0 14px;gap:7px">
        <span style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;flex-shrink:0"></span>
        <span style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;flex-shrink:0"></span>
        <span style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;flex-shrink:0"></span>
        <div style="flex:1;background:#1c1c1e;border-radius:5px;height:18px;margin:0 20px;display:flex;align-items:center;justify-content:center">
          <span style="color:#666;font-size:10px;font-family:monospace">example.com — section: {$sectionKey}</span>
        </div>
      </div>
      <div style="height:auto;min-height:400px;overflow:hidden">
        <iframe src="{$previewUrl}&vw=1200" style="width:100%;height:700px;border:none;display:block;background:#F7F3EC" loading="lazy"></iframe>
      </div>
    </div>

  </div>
</div>
HTML);
                }),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Section berhasil diupdate';
    }
}
