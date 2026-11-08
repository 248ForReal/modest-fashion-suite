<?php

namespace App\Filament\Resources\WorkAssignments\Tables;

use App\Filament\Pages\WorkSettings;
use App\Models\WorkAssignment;
use App\Models\WorkCancelRequest;
use App\Models\WorkReview;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\View;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class WorkAssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label('Model')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'pool' ? 'warning' : 'info')
                    ->formatStateUsing(fn (string $state): string => $state === 'pool' ? 'Pool' : 'Personal'),
                TextColumn::make('assignedTo.name')
                    ->label('Peserta')
                    ->placeholder('— Belum diklaim —')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('garmentType.contoh_pakaian')
                    ->label('Jenis Pakaian')
                    ->searchable()
                    ->hiddenOn('sm'),
                TextColumn::make('workType.nama')
                    ->label('Jenis Pekerjaan')
                    ->badge()
                    ->color(fn ($record) => match ($record->workType?->kategori_nilai) {
                        'jahit'  => 'info',
                        'desain' => 'warning',
                        'pola'   => 'success',
                        default  => 'gray',
                    }),
                TextColumn::make('target_jumlah')
                    ->label('Target')
                    ->numeric()
                    ->suffix(' item')
                    ->hiddenOn('sm'),
                TextColumn::make('deadline')
                    ->label('Deadline')
                    ->date('d M Y')
                    ->sortable()
                    ->hiddenOn('sm'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open'             => 'gray',
                        'in_progress'      => 'info',
                        'submitted'        => 'warning',
                        'revision'         => 'danger',
                        'approved'         => 'success',
                        'rejected'         => 'danger',
                        'cancel_requested' => 'warning',
                        'cancelled'        => 'gray',
                        default            => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open'             => 'Open',
                        'in_progress'      => 'Dikerjakan',
                        'submitted'        => 'Menunggu Review',
                        'revision'         => 'Revisi',
                        'approved'         => 'Disetujui',
                        'rejected'         => 'Ditolak',
                        'cancel_requested' => 'Req. Cancel',
                        'cancelled'        => 'Dibatalkan',
                        default            => $state,
                    }),
                TextColumn::make('product.nama')
                    ->label('Produk Terkait')
                    ->placeholder('—')
                    ->badge()
                    ->color('info')
                    ->limit(25)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('latestSubmission.jumlah_selesai')
                    ->label('Hasil')
                    ->suffix(' item')
                    ->placeholder('—'),
                TextColumn::make('latestSubmission.nilai_poin')
                    ->label('Poin')
                    ->numeric(decimalPlaces: 2)
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Model')
                    ->options(['personal' => 'Personal', 'pool' => 'Pool']),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'open'             => 'Open',
                        'in_progress'      => 'Dikerjakan',
                        'submitted'        => 'Menunggu Review',
                        'revision'         => 'Revisi',
                        'approved'         => 'Disetujui',
                        'rejected'         => 'Ditolak',
                        'cancel_requested' => 'Req. Cancel',
                        'cancelled'        => 'Dibatalkan',
                    ]),
                SelectFilter::make('work_type_id')
                    ->label('Jenis Pekerjaan')
                    ->relationship('workType', 'nama'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                // Lihat hasil submission (read-only, untuk tugas selesai/ditolak/dll)
                Action::make('lihat_hasil')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->visible(fn (WorkAssignment $record): bool => in_array($record->status, ['approved', 'rejected', 'revision', 'cancelled']) && $record->latestSubmission !== null)
                    ->modalHeading(fn (WorkAssignment $record): string => 'Detail Submission — ' . ($record->assignedTo?->name ?? '-'))
                    ->modalWidth('full')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->fillForm(function (WorkAssignment $record): array {
                        $record->load(['garmentType.garmentCategory', 'workType', 'assignedTo', 'latestSubmission']);
                        $submission = $record->latestSubmission;
                        $fotoUrl = $submission?->foto_path
                            ? Storage::disk('public')->url($submission->foto_path)
                            : null;
                        $nilaiDasar = match ($record->workType?->kategori_nilai) {
                            'jahit'  => $record->garmentType?->nilai_jahit,
                            'desain' => $record->garmentType?->nilai_desain,
                            'pola'   => $record->garmentType?->nilai_pola,
                            default  => null,
                        };
                        return [
                            '_foto_url'         => $fotoUrl,
                            '_peserta'          => $record->assignedTo?->name ?? '-',
                            '_pakaian'          => ($record->garmentType?->garmentCategory?->nama ?? '') . ' — ' . ($record->garmentType?->contoh_pakaian ?? ''),
                            '_pekerjaan'        => $record->workType?->nama ?? '-',
                            '_kategori_nilai'   => ucfirst($record->workType?->kategori_nilai ?? '-'),
                            '_bobot'            => $record->workType?->bobot_persen . '%',
                            '_nilai_dasar'      => $nilaiDasar,
                            '_target'           => $record->target_jumlah . ' item',
                            '_jumlah_selesai'   => ($submission?->jumlah_selesai ?? '-') . ' item',
                            '_catatan_peserta'  => $submission?->catatan ?? '-',
                            '_poin_otomatis'    => $submission?->nilai_poin ?? 0,
                            '_waktu_submit'     => $submission?->created_at?->format('d M Y, H:i') ?? '-',
                            '_instruksi'        => $record->instruksi ?? null,
                        ];
                    })
                    ->form([
                        \Filament\Schemas\Components\View::make('filament.forms.submission-preview'),
                    ]),

                // Lihat detail submission + review dalam satu modal besar
                Action::make('lihat_review')
                    ->label('Lihat & Review')
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->visible(fn (WorkAssignment $record): bool => $record->status === 'submitted')
                    ->modalHeading(fn (WorkAssignment $record): string => 'Review Submission — ' . ($record->assignedTo?->name ?? '-'))
                    ->modalWidth('full')
                    ->modalSubmitActionLabel('Simpan Review')
                    ->fillForm(function (WorkAssignment $record): array {
                        $record->load(['garmentType.garmentCategory', 'workType', 'assignedTo', 'latestSubmission.submittedBy']);
                        $submission = $record->latestSubmission;

                        $fotoUrl = $submission?->foto_path
                            ? Storage::disk('public')->url($submission->foto_path)
                            : null;

                        $nilaiDasar = match ($record->workType?->kategori_nilai) {
                            'jahit'  => $record->garmentType?->nilai_jahit,
                            'desain' => $record->garmentType?->nilai_desain,
                            'pola'   => $record->garmentType?->nilai_pola,
                            default  => null,
                        };

                        return [
                            '_foto_url'         => $fotoUrl,
                            '_peserta'          => $record->assignedTo?->name ?? '-',
                            '_pakaian'          => ($record->garmentType?->garmentCategory?->nama ?? '') . ' — ' . ($record->garmentType?->contoh_pakaian ?? ''),
                            '_pekerjaan'        => $record->workType?->nama ?? '-',
                            '_kategori_nilai'   => ucfirst($record->workType?->kategori_nilai ?? '-'),
                            '_bobot'            => $record->workType?->bobot_persen . '%',
                            '_nilai_dasar'      => $nilaiDasar,
                            '_target'           => $record->target_jumlah . ' item',
                            '_jumlah_selesai'   => ($submission?->jumlah_selesai ?? '-') . ' item',
                            '_catatan_peserta'  => $submission?->catatan ?? '-',
                            '_poin_otomatis'    => $submission?->nilai_poin ?? 0,
                            '_waktu_submit'     => $submission?->created_at?->format('d M Y, H:i') ?? '-',
                            '_instruksi'        => $record->instruksi ?? null,
                        ];
                    })
                    ->form(function (WorkAssignment $record): array {
                        return [
                            View::make('filament.forms.submission-preview'),
                            Radio::make('status')
                                ->label('Keputusan')
                                ->options([
                                    'approved' => 'Setujui',
                                    'revision' => 'Minta Revisi',
                                    'rejected' => 'Tolak',
                                ])
                                ->required()
                                ->inline(),
                            TextInput::make('skor_kualitas')
                                ->label('Skor Kualitas (1–5)')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(5)
                                ->placeholder('Opsional — nilai subjektif kualitas hasil'),
                            Textarea::make('catatan_review')
                                ->label('Catatan untuk Peserta')
                                ->rows(3)
                                ->placeholder('Opsional — catatan revisi, apresiasi, dll.'),
                        ];
                    })
                    ->action(function (WorkAssignment $record, array $data): void {
                        $submission = $record->latestSubmission;
                        if (! $submission) return;

                        WorkReview::create([
                            'work_submission_id' => $submission->id,
                            'reviewed_by'        => auth()->id(),
                            'status'             => $data['status'],
                            'skor_kualitas'      => $data['skor_kualitas'] ?? null,
                            'catatan_review'     => $data['catatan_review'] ?? null,
                        ]);

                        $record->update([
                            'status'       => $data['status'],
                            'completed_at' => in_array($data['status'], ['approved', 'rejected']) ? now() : null,
                        ]);

                        // Jika approved & ada produk terkait & fitur aktif → tambah unit ke stok
                        if ($data['status'] === 'approved' && $record->product_id && WorkSettings::isAutoSyncStokEnabled()) {
                            $produk       = Product::find($record->product_id);
                            $jumlahHasil  = $record->hasil_jumlah ?? $submission->jumlah_selesai ?? $record->target_jumlah;
                            if ($produk && $jumlahHasil > 0) {
                                $jumlahBaru = $produk->jumlah + $jumlahHasil;
                                $produk->syncUnits($jumlahBaru, $produk->warehouse_id);
                                Notification::make()
                                    ->title("Review disetujui — {$jumlahHasil} unit ditambahkan ke stok {$produk->nama}")
                                    ->success()
                                    ->send();
                                return;
                            }
                        }

                        Notification::make()->title('Review berhasil disimpan')->success()->send();
                    }),

                // Approve/Tolak cancel request dari user
                Action::make('reviewCancel')
                    ->label('Review Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (WorkAssignment $record): bool => $record->status === 'cancel_requested')
                    ->form(function (WorkAssignment $record): array {
                        $cancelRequest = $record->latestCancelRequest;
                        return [
                            Textarea::make('alasan_user')
                                ->label('Alasan Cancel dari User')
                                ->default($cancelRequest?->alasan ?? '-')
                                ->disabled()
                                ->rows(3),
                            Radio::make('keputusan')
                                ->label('Keputusan')
                                ->options([
                                    'approved' => 'Setujui Cancel',
                                    'rejected' => 'Tolak Cancel',
                                ])
                                ->required()
                                ->inline(),
                            Textarea::make('catatan_review')
                                ->label('Catatan (opsional)')
                                ->rows(2),
                        ];
                    })
                    ->action(function (WorkAssignment $record, array $data): void {
                        $cancelRequest = $record->latestCancelRequest;
                        if (! $cancelRequest) return;

                        $cancelRequest->update([
                            'status'         => $data['keputusan'],
                            'reviewed_by'    => auth()->id(),
                            'catatan_review' => $data['catatan_review'] ?? null,
                        ]);

                        if ($data['keputusan'] === 'approved') {
                            $record->update([
                                'status'       => 'cancelled',
                                'completed_at' => now(),
                            ]);
                            Notification::make()->title('Cancel disetujui — tugas dibatalkan')->success()->send();
                        } else {
                            // Tolak cancel → kembalikan ke status sebelumnya (in_progress)
                            $record->update(['status' => 'in_progress']);
                            Notification::make()->title('Cancel ditolak — tugas dilanjutkan')->warning()->send();
                        }
                    }),

                // Cancel langsung oleh supervisor/admin (tanpa request)
                Action::make('cancelDirect')
                    ->label('Cancel Tugas')
                    ->icon('heroicon-o-trash')
                    ->color('gray')
                    ->visible(fn (WorkAssignment $record): bool =>
                        in_array($record->status, ['open', 'in_progress']) &&
                        auth()->user()->can('work.cancel_approve'))
                    ->requiresConfirmation()
                    ->modalHeading('Cancel Tugas Ini?')
                    ->modalDescription('Tugas akan dibatalkan dan user dapat ambil tugas baru setelah cooldown.')
                    ->form([
                        Textarea::make('catatan_review')
                            ->label('Alasan Cancel (opsional)')
                            ->rows(2),
                    ])
                    ->action(function (WorkAssignment $record, array $data): void {
                        $record->update([
                            'status'       => 'cancelled',
                            'completed_at' => now(),
                        ]);

                        WorkCancelRequest::create([
                            'work_assignment_id' => $record->id,
                            'requested_by'       => $record->assigned_to ?? auth()->id(),
                            'alasan'             => 'Dibatalkan langsung oleh supervisor/admin.',
                            'status'             => 'approved',
                            'reviewed_by'        => auth()->id(),
                            'catatan_review'     => $data['catatan_review'] ?? null,
                        ]);

                        Notification::make()->title('Tugas berhasil dibatalkan')->success()->send();
                    }),

                EditAction::make()->label('Edit')
                    ->successNotificationTitle('Tugas pekerjaan berhasil diupdate'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
