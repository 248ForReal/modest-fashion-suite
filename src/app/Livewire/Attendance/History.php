<?php

namespace App\Livewire\Attendance;

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class History extends Component
{
    use WithPagination;

    public string $filterType = '';
    public string $filterMonth = '';

    public function render()
    {
        $query = Attendance::where('user_id', Auth::id())->orderBy('noted_at', 'desc');

        if ($this->filterType) $query->where('type', $this->filterType);
        if ($this->filterMonth) $query->whereRaw('DATE_FORMAT(noted_at, "%Y-%m") = ?', [$this->filterMonth]);

        return view('livewire.attendance.history', ['attendances' => $query->paginate(15)]);
    }
}
