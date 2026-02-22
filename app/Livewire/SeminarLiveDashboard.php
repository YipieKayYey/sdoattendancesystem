<?php

namespace App\Livewire;

use App\Models\Seminar;
use Livewire\Component;

class SeminarLiveDashboard extends Component
{
    public ?Seminar $seminar = null;

    public int $registeredCount = 0;

    public int $checkedInCount = 0;

    public int $capacity = 0;

    public bool $isOpen = false;

    public ?int $selectedDayId = null;

    public function mount(int $id): void
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            abort(403);
        }

        $this->seminar = Seminar::withTrashed()->findOrFail($id);

        // For multi-day: get day from URL or default to first day
        if ($this->seminar->is_multi_day && $this->seminar->days()->exists()) {
            $dayParam = request()->query('day');
            if ($dayParam) {
                $day = $this->seminar->days()->find((int) $dayParam);
                $this->selectedDayId = $day?->id;
            }
            if (!$this->selectedDayId) {
                $firstDay = $this->seminar->days()->orderBy('day_number')->first();
                $this->selectedDayId = $firstDay?->id;
            }
        }

        $this->loadData();
    }

    public function updatedSelectedDayId(): void
    {
        $this->loadData();
    }

    protected function loadData(): void
    {
        if (!$this->seminar) {
            return;
        }

        $this->registeredCount = $this->seminar->attendees()->count();
        $this->isOpen = (bool) $this->seminar->is_open;
        $this->capacity = $this->seminar->is_open ? 0 : (int) $this->seminar->capacity;

        // For multi-day with day selected: count check-ins for that day only
        if ($this->seminar->is_multi_day && $this->selectedDayId) {
            $this->checkedInCount = \App\Models\AttendeeCheckIn::where('seminar_day_id', $this->selectedDayId)
                ->whereNotNull('checked_in_at')
                ->count();
        } elseif ($this->seminar->is_multi_day && $this->seminar->days()->exists()) {
            // Multi-day, no day selected: count unique attendees with any check-in
            $this->checkedInCount = $this->seminar->attendees()
                ->whereHas('checkIns', fn ($q) => $q->whereNotNull('checked_in_at'))
                ->count();
        } else {
            // Single-day
            $this->checkedInCount = $this->seminar->attendees()
                ->where(function ($q) {
                    $q->whereNotNull('checked_in_at')
                        ->orWhereHas('checkIns', fn ($cq) => $cq->whereNotNull('checked_in_at'));
                })
                ->count();
        }
    }

    public function placeholder(): string
    {
        return '<div class="min-h-screen flex items-center justify-center bg-gray-900"><div class="animate-pulse text-white text-xl">Loading...</div></div>';
    }

    public function render()
    {
        $this->loadData();

        return view('livewire.seminar-live-dashboard')
            ->layout('components.layouts.live-dashboard', [
                'seminarTitle' => $this->seminar?->title ?? 'Seminar',
            ]);
    }
}
