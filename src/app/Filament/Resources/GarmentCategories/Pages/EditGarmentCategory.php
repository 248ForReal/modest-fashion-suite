<?php

namespace App\Filament\Resources\GarmentCategories\Pages;

use App\Filament\Resources\GarmentCategories\GarmentCategoryResource;
use App\Models\ProductSize;
use App\Models\SizeGuide;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditGarmentCategory extends EditRecord
{
    protected static string $resource = GarmentCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('panduan_ukuran')
                ->label('Panduan Ukuran')
                ->icon('heroicon-o-rectangle-stack')
                ->color('info')
                ->modalHeading(fn () => 'Panduan Ukuran — ' . $this->record->nama)
                ->modalDescription('Atur dimensi per ukuran untuk kategori ini. Data ini ditampilkan di halaman produk publik sebagai tabel ukuran.')
                ->modalWidth('3xl')
                ->fillForm(function (): array {
                    $categoryId = $this->record->id;
                    $sizes  = ProductSize::aktif()->get();
                    $guides = SizeGuide::where('garment_category_id', $categoryId)
                        ->get()->keyBy('product_size_id');
                    $fields = array_keys(SizeGuide::dimensiLabels());

                    return [
                        'rows' => $sizes->map(function ($s) use ($guides, $fields) {
                            $dimensi = $guides->get($s->id)?->dimensi ?? [];
                            $row = [
                                'product_size_id' => $s->id,
                                'size_nama'       => $s->nama,
                            ];
                            foreach ($fields as $f) {
                                $row[$f] = $dimensi[$f] ?? null;
                            }
                            return $row;
                        })->toArray(),
                    ];
                })
                ->form(function (): array {
                    $labels      = SizeGuide::dimensiLabels();
                    $inputFields = [];
                    foreach ($labels as $key => $label) {
                        $inputFields[] = \Filament\Forms\Components\TextInput::make($key)
                            ->label($label)
                            ->numeric()
                            ->minValue(0)
                            ->suffix('cm')
                            ->placeholder('—');
                    }

                    return [
                        \Filament\Forms\Components\Repeater::make('rows')
                            ->label(false)
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('size_nama')
                                    ->label('Ukuran')
                                    ->disabled()
                                    ->dehydrated(false),

                                \Filament\Forms\Components\Hidden::make('product_size_id'),
                                \Filament\Forms\Components\Hidden::make('size_nama'),

                                ...$inputFields,
                            ])
                            ->columns(3)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->itemLabel(fn (array $state): ?string => $state['size_nama'] ?? null),
                    ];
                })
                ->action(function (array $data): void {
                    $categoryId = $this->record->id;
                    $fields     = array_keys(SizeGuide::dimensiLabels());

                    foreach ($data['rows'] as $row) {
                        $sizeId = $row['product_size_id'] ?? null;
                        if (!$sizeId) continue;

                        $dimensi = [];
                        foreach ($fields as $f) {
                            $val = $row[$f] ?? null;
                            if ($val !== null && $val !== '') {
                                $dimensi[$f] = $val;
                            }
                        }

                        SizeGuide::updateOrCreate(
                            ['garment_category_id' => $categoryId, 'product_size_id' => $sizeId],
                            ['dimensi' => empty($dimensi) ? null : $dimensi]
                        );
                    }

                    Notification::make()
                        ->title('Panduan ukuran berhasil disimpan')
                        ->success()
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Kategori berhasil diupdate';
    }
}
