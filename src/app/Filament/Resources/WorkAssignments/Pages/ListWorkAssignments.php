<?php

namespace App\Filament\Resources\WorkAssignments\Pages;

use App\Filament\Resources\WorkAssignments\WorkAssignmentResource;
use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListWorkAssignments extends ListRecords
{
    protected static string $resource = WorkAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $submittedCount = $this->getResource()::getEloquentQuery()
            ->where('status', 'submitted')
            ->count();

        $reviewLabel = $submittedCount > 0
            ? new \Illuminate\Support\HtmlString(
                'Perlu Direview <span style="display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;padding:0 5px;background:#ef4444;color:#fff;font-size:11px;font-weight:700;border-radius:9999px;margin-left:4px;line-height:1;">'
                . ($submittedCount > 99 ? '99+' : $submittedCount)
                . '</span>'
            )
            : 'Perlu Direview';

        return [
            'semua' => Tab::make('Semua'),

            'perlu_review' => Tab::make($reviewLabel)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'submitted')),

            'aktif' => Tab::make('Aktif')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['open', 'in_progress', 'revision', 'cancel_requested'])),

            'selesai' => Tab::make('Selesai')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['approved', 'rejected', 'cancelled'])),
        ];
    }
}
