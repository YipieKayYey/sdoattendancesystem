<?php

namespace App\Livewire;

use App\Models\Attendee;
use App\Models\Seminar;
use Illuminate\Support\Str;
use Livewire\Component;

class RegisterAttendee extends Component
{
    public string $slug;
    public ?Seminar $seminar = null;
    
    public string $name = '';
    public string $email = '';
    public string $position = '';

    public function mount(string $slug)
    {
        $this->slug = $slug;
        $this->seminar = Seminar::where('slug', $slug)->firstOrFail();
    }

    public function register()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    $exists = Attendee::where('seminar_id', $this->seminar->id)
                        ->where('email', $value)
                        ->exists();
                    
                    if ($exists) {
                        $fail('This email is already registered for this seminar.');
                    }
                },
            ],
            'position' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Please enter your name.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        // Check capacity
        if ($this->seminar->isFull()) {
            $this->addError('capacity', 'Sorry, this seminar is full.');
            return;
        }

        // Generate unique 16-character ticket hash
        do {
            $ticketHash = Str::random(16);
        } while (Attendee::where('ticket_hash', $ticketHash)->exists());

        // Create attendee
        $attendee = Attendee::create([
            'seminar_id' => $this->seminar->id,
            'name' => $this->name,
            'email' => $this->email,
            'position' => $this->position,
            'ticket_hash' => $ticketHash,
        ]);

        // Redirect to success page
        return redirect()->route('registration.success', ['ticket_hash' => $ticketHash]);
    }

    public function render()
    {
        return view('livewire.register-attendee', [
            'seminar' => $this->seminar,
        ])->layout('components.layouts.app');
    }
}
