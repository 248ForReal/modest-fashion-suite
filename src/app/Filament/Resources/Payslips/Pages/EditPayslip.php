<?php

namespace App\Filament\Resources\Payslips\Pages;

use App\Filament\Resources\Payslips\PayslipResource;
use App\Filament\Resources\Payslips\Tables\PayslipsTable;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditPayslip extends EditRecord
{
    protected static string $resource = PayslipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('finalkan')
                ->label('Simpan & Finalkan')
                ->icon(Heroicon::OutlinedLockClosed)
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('Perubahan disimpan, slip dikunci dan langsung bisa dilihat karyawan yang bersangkutan.')
                ->action(function (): void {
                    $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
                    PayslipsTable::finalize($this->getRecord());
                    Notification::make()->title('Slip difinalkan')->success()->send();
                    $this->redirect(PayslipResource::getUrl('index'));
                }),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return PayslipResource::getUrl('index');
    }
}
