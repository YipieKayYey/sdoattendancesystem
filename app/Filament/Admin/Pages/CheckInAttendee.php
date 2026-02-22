<?php

namespace App\Filament\Admin\Pages;

use App\Models\Attendee;
use App\Models\AttendeeCheckIn;
use App\Models\AttendeeProfile;
use App\Models\Seminar;
use App\Models\SeminarDay;
use Illuminate\Support\Str;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class CheckInAttendee extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    
    protected static ?string $navigationLabel = 'Check In';

    protected static string $view = 'filament.admin.pages.check-in-attendee';
    
    protected static bool $shouldRegisterNavigation = false; // Hide from main navigation, only accessible via seminar
    
    public ?Seminar $seminar = null;
    public ?Attendee $lastCheckedIn = null;
    public ?AttendeeCheckIn $lastCheckInRecord = null;
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
            ? "Check In - {$this->seminar->title}"
            : 'Check In Attendee';
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('ticketHash')
                ->label('Ticket Hash / Universal QR')
                ->placeholder('Scan Universal QR or ticket hash (16 characters)')
                ->maxLength(16)
                ->autofocus()
                ->live()
                ->extraAttributes([
                    'x-on:keydown.enter' => '$wire.checkIn()',
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
                ->label('Select Day for Check-In')
                ->options($dayOptions)
                ->default($this->selectedDayId ?? $this->currentDay?->id)
                ->required()
                ->live()
                ->placeholder('Choose a day...')
                ->helperText('Select which day you are checking in this attendee for.')
                ->searchable(false)
                ->native(false),
        ];
    }

    public function setTicketHashFromScan(string $hash): void
    {
        $this->ticketHash = $hash;
        $this->form->fill(['ticketHash' => $hash]);
        $this->checkIn();
    }

    public function checkIn(): void
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
        
        // Proceed with check-in
        $this->proceedWithCheckIn($ticketHash);
    }

    public function proceedWithCheckIn(?string $ticketHash = null): void
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
                ->title('Invalid hash')
                ->body('Hash must be exactly 16 characters.')
                ->danger()
                ->send();
            return;
        }

        // Dual lookup: AttendeeProfile (universal QR) first, then Attendee (ticket hash)
        $profile = AttendeeProfile::findByUniversalQrHash($ticketHash);
        $attendee = null;

        if ($profile) {
            // User flow: resolve or create attendee for this seminar
            if (!$this->seminar) {
                Notification::make()
                    ->title('Seminar required')
                    ->body('Please access check-in from a seminar.')
                    ->danger()
                    ->send();
                return;
            }
            $user = $profile->user;
            if (!$user) {
                Notification::make()
                    ->title('Profile error')
                    ->body('Attendee profile has no linked user.')
                    ->danger()
                    ->send();
                return;
            }
            $attendee = Attendee::where('seminar_id', $this->seminar->id)
                ->where('user_id', $user->id)
                ->first();
            if (!$attendee) {
                if ($this->seminar->isFull()) {
                    Notification::make()
                        ->title('Seminar full')
                        ->body('This seminar has reached capacity.')
                        ->danger()
                        ->send();
                    return;
                }
                $attendee = $this->createAttendeeFromProfile($profile, $user);
            }
        } else {
            $attendee = Attendee::where('ticket_hash', $ticketHash)->first();
        }

        if (!$attendee) {
            Notification::make()
                ->title('Not found')
                ->body('No attendee or profile found with this hash.')
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

        // Determine which day to check in for
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
            
            // If a day is selected, use per-day check-in records
            if ($seminarDay) {
                // Check if already checked in for this day
                $existingCheckIn = AttendeeCheckIn::where('attendee_id', $attendee->id)
                    ->where('seminar_day_id', $seminarDay->id)
                    ->whereNotNull('checked_in_at')
                    ->first();
                
                if ($existingCheckIn) {
                    Notification::make()
                        ->title('Already checked in')
                        ->body("This attendee was already checked in for Day {$seminarDay->day_number} at {$existingCheckIn->checked_in_at->format('M j, Y g:i A')}.")
                        ->warning()
                        ->send();
                    $this->lastCheckedIn = $attendee;
                    $this->lastCheckInRecord = $existingCheckIn;
                    $this->ticketHash = '';
                    $this->form->fill(['ticketHash' => '']);
                    return;
                }
                
                // Create or update check-in record for this day
                $checkIn = AttendeeCheckIn::firstOrNew([
                    'attendee_id' => $attendee->id,
                    'seminar_day_id' => $seminarDay->id,
                ]);
                $checkIn->checked_in_at = now();
                $checkIn->save();
                
                // Store the check-in record for display
                $this->lastCheckInRecord = $checkIn;
                
                // Update attendee's checked_in_at for backward compatibility (latest check-in)
                $latestCheckIn = $attendee->checkIns()->whereNotNull('checked_in_at')->latest('checked_in_at')->first();
                if ($latestCheckIn) {
                    $attendee->update(['checked_in_at' => $latestCheckIn->checked_in_at]);
                }
                
                $dayInfo = "Day {$seminarDay->day_number}";
            } else {
                // No day selected and seminar has only one day -> fall back to attendee columns
                if ($attendee->checked_in_at !== null) {
                    Notification::make()
                        ->title('Already checked in')
                        ->body("This attendee was already checked in at {$attendee->checked_in_at->format('M j, Y g:i A')}.")
                        ->warning()
                        ->send();
                    $this->lastCheckedIn = $attendee;
                    $this->ticketHash = '';
                    $this->form->fill(['ticketHash' => '']);
                    return;
                }
                
                $attendee->update(['checked_in_at' => now()]);
                $dayInfo = '';
                $this->lastCheckInRecord = null;
            }
        } else {
            // Single-day seminar - use old method for backward compatibility
            if ($attendee->checked_in_at !== null) {
                Notification::make()
                    ->title('Already checked in')
                    ->body("This attendee was already checked in at {$attendee->checked_in_at->format('M j, Y g:i A')}.")
                    ->warning()
                    ->send();
                $this->lastCheckedIn = $attendee;
                $this->ticketHash = '';
                $this->form->fill(['ticketHash' => '']);
                return;
            }
            
            $attendee->update(['checked_in_at' => now()]);
            $dayInfo = '';
            $this->lastCheckInRecord = null; // Single-day doesn't use check-in records
        }
        
        $this->lastCheckedIn = $attendee;
        $name = $attendee->full_name ?: $attendee->name;
        $signatureStatus = $attendee->hasSignature() ? ' (Signature verified)' : ' (No signature)';
        $dayText = $dayInfo ? " for {$dayInfo}" : '';
        
        Notification::make()
            ->title('Check-in successful!')
            ->body("{$name} has been checked in{$dayText}.{$signatureStatus}")
            ->success()
            ->send();

        $this->ticketHash = '';
        $this->pendingTicketHash = null;
        $this->form->fill(['ticketHash' => '']);
    }

    public function selectDayAndCheckIn(): void
    {
        $data = $this->dayModalForm->getState();
        $dayId = $data['dayId'] ?? null;
        
        if (!$dayId) {
            Notification::make()
                ->title('Day selection required')
                ->body('Please select a day before checking in.')
                ->danger()
                ->send();
            return;
        }
        
        $this->selectedDayId = $dayId;
        $this->showDayModal = false;
        
        // Proceed with check-in using the pending ticket hash
        $this->proceedWithCheckIn();
    }

    public function closeDayModal(): void
    {
        $this->showDayModal = false;
        $this->pendingTicketHash = null;
    }

    public function backToSeminar(): void
    {
        if ($this->seminar) {
            $this->redirect(\App\Filament\Admin\Resources\SeminarResource::getUrl('view', ['record' => $this->seminar->id]));
        } else {
            $this->redirect(\App\Filament\Admin\Resources\SeminarResource::getUrl('index'));
        }
    }

    protected function createAttendeeFromProfile(AttendeeProfile $profile, \App\Models\User $user): Attendee
    {
        $schoolOfficeAgency = $profile->school_office_agency ?? $profile->school_other;
        if ($profile->school_id && $profile->school) {
            $schoolOfficeAgency = $profile->school->name;
        }

        do {
            $ticketHash = Str::random(16);
        } while (Attendee::where('ticket_hash', $ticketHash)->exists());

        return Attendee::create([
            'seminar_id' => $this->seminar->id,
            'user_id' => $user->id,
            'ticket_hash' => $ticketHash,
            'email' => $user->email,
            'personnel_type' => $profile->personnel_type,
            'first_name' => $profile->first_name,
            'middle_name' => $profile->middle_name,
            'last_name' => $profile->last_name,
            'suffix' => $profile->suffix,
            'sex' => $profile->sex,
            'school_id' => $profile->school_id,
            'school_other' => $profile->school_other,
            'school_office_agency' => $schoolOfficeAgency,
            'mobile_phone' => $profile->mobile_phone,
            'position' => $profile->position,
            'prc_license_no' => $profile->prc_license_no,
            'prc_license_expiry' => $profile->prc_license_expiry,
            'signature_image' => $profile->signature_image,
            'signature_upload_path' => $profile->signature_upload_path,
            'signature_hash' => $profile->signature_hash,
            'signature_metadata' => $profile->signature_metadata,
            'signature_consent' => $profile->signature_consent ?? false,
            'signature_timestamp' => $profile->signature_timestamp,
        ]);
    }
}
