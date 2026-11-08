<?php

namespace App\Livewire\Attendance;

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LeaveRequest extends Component
{
    public string $type = 'leave';
    public string $notes = '';
    public string $date = '';
    public ?string $message = null;
    public string $messageType = 'success';

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
    }

    public function submit(): void
    {
        $this->validate([
            'type'  => 'required|in:leave,permission',
            'notes' => 'required|string|max:500',
            'date'  => 'required|date',
        ]);

        Attendance::create([
            'user_id'  => Auth::id(),
            'type'     => $this->type,
            'is_valid' => true,
            'noted_at' => $this->date . ' 00:00:00',
            'notes'    => $this->notes,
        ]);

        $this->message     = 'Pengajuan ' . ($this->type === 'leave' ? 'cuti' : 'izin') . ' berhasil dicatat.';
        $this->messageType = 'success';
        $this->notes       = '';
    }

    public function render()
    {
        return view('livewire.attendance.leave-request');
    }
}
