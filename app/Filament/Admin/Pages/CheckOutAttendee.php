<?php

namespace App\Filament\Admin\Pages;

use App\Models\Attendee;
use App\Models\AttendeeCheckIn;
use App\Models\Seminar;
use App\Models\SeminarDay;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class CheckOutAttendee extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-on-rectangle';
    
    protected static ?string $navigationLabel = 'Check Out';

    protected static string $view = 'filament.admin.pages.check-out-attendee';
    
    protected static bool $shouldRegisterNavigation = false; // Hide from main navigation, only accessible via seminar
    
    public ?Seminar $seminar = null;
    public ?Attendee $lastCheckedOut = null;
    public ?AttendeeCheckIn $lastCheckOutRecord = null;
    public string $ticketHash = '';
    public ?int $selectedDayId = null;
    public ?SeminarDay $currentDay = null;
    public bool $showDayModal = false;
    public ?string $pendingTicketHash = null;

    public ?Form $dayModalForm = null;

    public function mount(?int $seminar = null): void
    {
        // Filament pages typically don't have route params here; read from query string too.
        $seminarId = $seminar ?: (int) request()->query('seminar');

        if ($seminarId) {
            $this->seminar = Seminar::with('days')->findOrFail($seminarId);
            
            // Get day parameter from query string
            $dayParam = request()->query('day');
            if ($dayParam) {
                // Day parameter from URL query string (when coming from header action)
                $this->selectedDayId = (int)$dayParam;
                $this->currentDay = $this->seminar->days()->find($this->selectedDayId);
            } else {
                // Auto-detect current day
                $this->currentDay = $this->seminar->getCurrentDay();
                $this->selectedDayId = $this->currentDay?->id;
            }
        }
        
        $this->form->fill([
            'ticketHash' => $this->ticketHash,
        ]);
    }

    public function dayModalForm(Form $form): Form
    {
        return $form
            ->schema($this->getDayModalFormSchema())
            ->statePath('dayModalForm')
            ->model(null);
    }

    public function getTitle(): string | Htmlable
    {
        return $this->seminar 
            ? "Check Out - {$this->seminar->title}"
            : 'Check Out Attendee';
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('ticketHash')
                ->label('Ticket Hash / QR Code')
                ->placeholder('Scan QR code or enter 16-character ticket hash')
                ->maxLength(16)
                ->autofocus()
                ->live()
                ->extraAttributes([
                    'x-on:keydown.enter' => '$wire.checkOut()',
                ]),
        ];
    }

    protected function getDayModalFormSchema(): array
    {
        $days = $this->seminar?->days ?? collect();
        
        $dayOptions = [];
        foreach ($days as $day) {
            $label = "Day {$day->day_number} - {$day->formatted_date}";
            if ($day->start_time) {
                $label .= " ({$day->formatted_time})";
            }
            if ($this->currentDay && $this->currentDay->id === $day->id) {
                $label .= " (Current)";
            }
            $dayOptions[$day->id] = $label;
        }
        
        return [
            Select::make('dayId')
                ->label('Select Day for Check-Out')
                ->options($dayOptions)
                ->default($this->selectedDayId ?? $this->currentDay?->id)
                ->required()
                ->live()
                ->placeholder('Choose a day...')
                ->helperText('Select which day you are checking out this attendee for.')
                ->searchable(false)
                ->native(false),
        ];
    }

    public function setTicketHashFromScan(string $hash): void
    {
        $this->ticketHash = $hash;
        $this->form->fill(['ticketHash' => $hash]);
        $this->checkOut();
    }

    public function checkOut(): void
    {
        // Get ticket hash from form state or property
        $data = $this->form->getState();
        $ticketHash = $data['ticketHash'] ?? $this->ticketHash;
        
        // If seminar has multiple days, require a day selection
        if ($this->seminar && $this->seminar->days()->count() > 1) {
            if (!$this->selectedDayId) {
                // Store the ticket hash and show modal
                $this->pendingTicketHash = $ticketHash;
                $this->showDayModal = true;
                return;
            }
        }
        
        // Proceed with check-out
        $this->proceedWithCheckOut($ticketHash);
    }

    public function proceedWithCheckOut(?string $ticketHash = null): void
    {
        // Use pending ticket hash if available, otherwise use parameter or form state
        $ticketHash = $ticketHash ?? $this->pendingTicketHash ?? $this->form->getState()['ticketHash'] ?? $this->ticketHash;
        
        if (empty($ticketHash)) {
            Notification::make()
                ->title('Ticket hash required')
                ->body('Please scan QR code or enter ticket hash.')
                ->danger()
                ->send();
            return;
        }

        if (strlen($ticketHash) !== 16) {
            Notification::make()
                ->title('Invalid ticket hash')
                ->body('Ticket hash must be exactly 16 characters.')
                ->danger()
                ->send();
            return;
        }

        $attendee = Attendee::where('ticket_hash', $ticketHash)->first();

        if (!$attendee) {
            Notification::make()
                ->title('Attendee not found')
                ->body('No attendee found with this ticket hash.')
                ->danger()
                ->send();
            return;
        }

        // If seminar is specified, verify it matches
        if ($this->seminar && $attendee->seminar_id !== $this->seminar->id) {
            Notification::make()
                ->title('Wrong seminar')
                ->body('This ticket belongs to a different seminar.')
                ->danger()
                ->send();
            return;
        }

        // Determine which day to check out for
        $seminarDay = null;
        if ($this->seminar) {
            // Re-check query parameter if selectedDayId is not set (in case it was lost)
            if (!$this->selectedDayId) {
                $dayParam = request()->query('day');
                if ($dayParam) {
                    $this->selectedDayId = (int)$dayParam;
                }
            }
            
            $dayId = $this->selectedDayId;
            
            // If this seminar has multiple days, day selection is required.
            if (!$dayId && $this->seminar->days()->count() > 1) {
                Notification::make()
                    ->title('Day selection required')
                    ->body('Please select a day for this multi-day seminar.')
                    ->danger()
                    ->send();
                return;
            }
            
            if ($dayId) {
                $seminarDay = SeminarDay::where('id', $dayId)
                    ->where('seminar_id', $this->seminar->id)
                    ->first();
            }
            
            if ($dayId && !$seminarDay) {
                Notification::make()
                    ->title('Invalid day')
                    ->body("Selected day (ID: {$dayId}) does not belong to this seminar.")
                    ->danger()
                    ->send();
                return;
            }
            
            if ($seminarDay) {
                // Find check-in record for this day
                $checkIn = AttendeeCheckIn::where('attendee_id', $attendee->id)
                    ->where('seminar_day_id', $seminarDay->id)
                    ->first();
                
                if (!$checkIn || !$checkIn->checked_in_at) {
                    Notification::make()
                        ->title('Not checked in')
                        ->body("This attendee must be checked in for Day {$seminarDay->day_number} before checking out.")
                        ->warning()
                        ->send();
                    $this->lastCheckedOut = $attendee;
                    $this->ticketHash = '';
                    $this->form->fill(['ticketHash' => '']);
                    return;
                }
                
                if ($checkIn->checked_out_at) {
                    Notification::make()
                        ->title('Already checked out')
                        ->body("This attendee was already checked out for Day {$seminarDay->day_number} at {$checkIn->checked_out_at->format('M j, Y g:i A')}.")
                        ->warning()
                        ->send();
                    $this->lastCheckedOut = $attendee;
                    $this->ticketHash = '';
                    $this->form->fill(['ticketHash' => '']);
                    return;
                }
                
                // Update check-out
                $checkIn->checked_out_at = now();
                $checkIn->save();
                
                // Store the check-out record for display
                $this->lastCheckOutRecord = $checkIn;
                
                // Update attendee's checked_out_at for backward compatibility (latest check-out)
                $latestCheckOut = $attendee->checkIns()->whereNotNull('checked_out_at')->latest('checked_out_at')->first();
                if ($latestCheckOut) {
                    $attendee->update(['checked_out_at' => $latestCheckOut->checked_out_at]);
                }
                
                $dayInfo = "Day {$seminarDay->day_number}";
                $checkInTime = $checkIn->checked_in_at->format('M j, Y g:i A');
                $duration = $checkIn->checked_in_at->diffForHumans($checkIn->checked_out_at, true);
            } else {
                // No day selected and seminar has only one day -> fall back to attendee columns
                if ($attendee->checked_out_at !== null) {
                    Notification::make()
                        ->title('Already checked out')
                        ->body("This attendee was already checked out at {$attendee->checked_out_at->format('M j, Y g:i A')}.")
                        ->warning()
                        ->send();
                    $this->lastCheckedOut = $attendee;
                    $this->ticketHash = '';
                    $this->form->fill(['ticketHash' => '']);
                    return;
                }
                
                if ($attendee->checked_in_at === null) {
                    Notification::make()
                        ->title('Not checked in')
                        ->body('This attendee must be checked in before checking out.')
                        ->warning()
                        ->send();
                    $this->lastCheckedOut = $attendee;
                    $this->ticketHash = '';
                    $this->form->fill(['ticketHash' => '']);
                    return;
                }
                
                $attendee->update(['checked_out_at' => now()]);
                $dayInfo = '';
                $checkInTime = $attendee->checked_in_at->format('M j, Y g:i A');
                $duration = $attendee->checked_in_at->diffForHumans($attendee->checked_out_at, true);
                $this->lastCheckOutRecord = null;
            }
        } else {
            // Single-day seminar - use old method
            if ($attendee->checked_out_at !== null) {
                Notification::make()
                    ->title('Already checked out')
                    ->body("This attendee was already checked out at {$attendee->checked_out_at->format('M j, Y g:i A')}.")
                    ->warning()
                    ->send();
                $this->lastCheckedOut = $attendee;
                $this->ticketHash = '';
                $this->form->fill(['ticketHash' => '']);
                return;
            }
            
            if ($attendee->checked_in_at === null) {
                Notification::make()
                    ->title('Not checked in')
                    ->body('This attendee must be checked in before checking out.')
                    ->warning()
                    ->send();
                $this->lastCheckedOut = $attendee;
                $this->ticketHash = '';
                $this->form->fill(['ticketHash' => '']);
                return;
            }
            
            $attendee->update(['checked_out_at' => now()]);
            $dayInfo = '';
            $checkInTime = $attendee->checked_in_at->format('M j, Y g:i A');
            $duration = $attendee->checked_in_at->diffForHumans($attendee->checked_out_at, true);
            $this->lastCheckOutRecord = null; // Single-day doesn't use check-in records
        }
        
        $this->lastCheckedOut = $attendee;
        $name = $attendee->full_name ?: $attendee->name;
        $dayText = $dayInfo ? " for {$dayInfo}" : '';
        
        Notification::make()
            ->title('Check-out successful!')
            ->body("{$name} has been checked out{$dayText}. (Checked in: {$checkInTime}, Duration: {$duration})")
            ->success()
            ->send();

        $this->ticketHash = '';
        $this->pendingTicketHash = null;
        $this->form->fill(['ticketHash' => '']);
    }

    public function selectDayAndCheckOut(): void
    {
        $data = $this->dayModalForm->getState();
        $dayId = $data['dayId'] ?? null;
        
        if (!$dayId) {
            Notification::make()
                ->title('Day selection required')
                ->body('Please select a day before checking out.')
                ->danger()
                ->send();
            return;
        }
        
        $this->selectedDayId = $dayId;
        $this->showDayModal = false;
        
        // Proceed with check-out using the pending ticket hash
        $this->proceedWithCheckOut();
    }

    public function closeDayModal(): void
    {
        $this->showDayModal = false;
        $this->pendingTicketHash = null;
        $this->dayModalForm->fill(['dayId' => null]);
    }
}
