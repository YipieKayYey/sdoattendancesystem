<?php

namespace App\Filament\Admin\Pages;

use App\Models\Attendee;
use App\Models\Seminar;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
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
    public string $ticketHash = '';

    public function mount(?int $seminar = null): void
    {
        if ($seminar) {
            $this->seminar = Seminar::findOrFail($seminar);
        }
        
        $this->form->fill(['ticketHash' => $this->ticketHash]);
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
                ->label('Ticket Hash / QR Code')
                ->placeholder('Scan QR code or enter 16-character ticket hash')
                ->maxLength(16)
                ->autofocus()
                ->live()
                ->extraAttributes([
                    'x-on:keydown.enter' => '$wire.checkIn()',
                ]),
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

        // Check if already checked in
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

        // Check in the attendee
        $attendee->update(['checked_in_at' => now()]);
        $this->lastCheckedIn = $attendee;

        Notification::make()
            ->title('Check-in successful!')
            ->body("{$attendee->name} has been checked in.")
            ->success()
            ->send();

        $this->ticketHash = '';
        $this->form->fill(['ticketHash' => '']);
    }
}
