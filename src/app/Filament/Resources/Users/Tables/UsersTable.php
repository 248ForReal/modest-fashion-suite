<?php

namespace App\Filament\Resources\Users\Tables;

use App\Notifications\UserApprovedNotification;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Password;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->label('Nama'),
                TextColumn::make('email')->searchable()->hiddenOn('sm'),
                TextColumn::make('phone')->searchable()->toggleable()->label('No. HP')->hiddenOn('sm'),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->separator(','),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'active'   => 'success',
                        'pending'  => 'warning',
                        'inactive' => 'danger',
                        default    => 'gray',
                    }),
                TextColumn::make('created_at')->dateTime('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true)->label('Dibuat')->hiddenOn('sm'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'active' => 'Aktif', 'inactive' => 'Nonaktif']),
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->label('Role'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Pengguna')
                    ->modalDescription(fn($record) => 'Setujui akun ' . $record->name . '? Email notifikasi akan dikirim.')
                    ->modalSubmitActionLabel('Ya, Approve')
                    ->action(function ($record) {
                        try {
                            $record->update(['status' => 'active']);
                            $token = Password::createToken($record);
                            $resetUrl = url(route('password.reset', [
                                'token' => $token,
                                'email' => $record->email,
                            ], false));
                            $record->notify(new UserApprovedNotification($resetUrl));
                            Notification::make()->title('Berhasil')->body($record->name . ' telah disetujui dan email notifikasi terkirim.')->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()->title('Gagal Kirim Email')->body('Akun disetujui tapi email gagal terkirim: ' . $e->getMessage())->warning()->send();
                        }
                    }),

                Action::make('deactivate')
                    ->label('Nonaktifkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'active')
                    ->requiresConfirmation()
                    ->modalHeading('Nonaktifkan Pengguna')
                    ->modalDescription(fn($record) => 'Nonaktifkan akun ' . $record->name . '? Pengguna tidak bisa login.')
                    ->modalSubmitActionLabel('Ya, Nonaktifkan')
                    ->action(function ($record) {
                        $record->update(['status' => 'inactive']);
                        Notification::make()->title('Berhasil')->body($record->name . ' telah dinonaktifkan.')->success()->send();
                    }),

                EditAction::make()
                    ->successNotificationTitle('Pengguna berhasil diupdate'),

                DeleteAction::make()
                    ->requiresConfirmation()
                    ->successNotificationTitle('Pengguna berhasil dihapus')
                    ->visible(fn() => auth()->user()?->hasRole('super_admin')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(false)
                        ->visible(fn() => auth()->user()?->hasRole('super_admin')),
                ]),
            ]);
    }
}
