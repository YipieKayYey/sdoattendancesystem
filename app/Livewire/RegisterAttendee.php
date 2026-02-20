<?php

namespace App\Livewire;

use App\Models\Attendee;
use App\Models\School;
use App\Models\Seminar;
use App\Services\SignatureSecurityService;
use Illuminate\Support\Str;
use Livewire\Component;
class RegisterAttendee extends Component
{

    public string $slug;
    public ?Seminar $seminar = null;
    
    public int $currentStep = 1;
    
    public string $personnelType = '';
    public string $firstName = '';
    public string $middleName = '';
    public string $lastName = '';
    public string $suffix = '';
    public string $sex = '';
    /** @var int|string|null */
    public $schoolId = null;
    public string $schoolOther = '';
    public string $email = '';
    public string $mobilePhone = '';
    public string $position = '';
    public string $prcLicenseNo = '';
    public ?string $prcLicenseExpiry = null;
    public bool $noPrcLicense = false;
    public bool $signatureConsent = false;
    public ?string $signatureData = null;

    public function mount(string $slug)
    {
        $this->slug = $slug;
        $this->seminar = Seminar::where('slug', $slug)->firstOrFail();
        
        // Check if seminar is ended
        if ($this->seminar->is_ended) {
            abort(403, 'Registration for this seminar has been closed.');
        }
    }

    public function nextStep()
    {
        // Validate step 1 fields
        $this->validate([
            'personnelType' => ['required', 'in:teaching,non_teaching'],
            'firstName' => ['required', 'string', 'max:255'],
            'middleName' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if ($value === null || trim((string) $value) === '') {
                        return;
                    }
                    $v = trim((string) $value);
                    if (preg_match('/^[A-Za-z]\\.?$/', $v)) {
                        $fail('Please enter your full middle name (not just an initial).');
                    }
                },
            ],
            'lastName' => ['required', 'string', 'max:255'],
            'suffix' => [
                'nullable',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    if ($value === null || trim((string) $value) === '') {
                        return;
                    }
                    $normalized = strtolower(trim((string) $value));
                    $invalidValues = ['n/a', 'na', 'none', 'nil', 'null', '-', '--', '---'];
                    if (in_array($normalized, $invalidValues)) {
                        $fail('Please leave the suffix field blank if you do not have a suffix (do not enter N/A, None, etc.).');
                    }
                },
            ],
            'sex' => ['required', 'in:male,female'],
            'schoolId' => ['required'],
            'schoolOther' => ['required_if:schoolId,other', 'nullable', 'string', 'max:255'],
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
        ], [
            'personnelType.required' => 'Please select personnel type.',
            'firstName.required' => 'Please enter your first name.',
            'lastName.required' => 'Please enter your last name.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'sex.required' => 'Please select your sex.',
            'sex.in' => 'Please select either Male or Female.',
            'schoolId.required' => 'Please select your School/Office/Agency.',
            'schoolOther.required_if' => 'Please enter your School/Office/Agency when selecting Others.',
        ]);

        $this->currentStep = 2;
    }

    public function previousStep()
    {
        $this->currentStep = 1;
    }

    protected function getSchoolOfficeAgencyValue(): string
    {
        if ($this->schoolId === 'other') {
            return $this->schoolOther;
        }
        if ($this->schoolId && $this->schoolId !== 'other') {
            $school = School::find((int) $this->schoolId);
            return $school ? $school->name : '';
        }
        return '';
    }

    public function getSchoolsProperty()
    {
        return School::orderBy('name')->pluck('name', 'id');
    }

    public function updatedNoPrcLicense($value)
    {
        // Clear PRC license fields when "no license" is checked
        if ($value) {
            $this->prcLicenseNo = '';
            $this->prcLicenseExpiry = null;
        }
    }

    public function register()
    {
        // Validate step 2 fields
        $this->validate([
            'mobilePhone' => ['required', 'string', 'max:20'],
            'position' => ['required', 'string', 'max:255'],
            'noPrcLicense' => ['nullable', 'boolean'],
            'prcLicenseNo' => [
                function ($attribute, $value, $fail) {
                    // Required for teaching personnel unless they check "no license"
                    if ($this->personnelType === 'teaching' && !$this->noPrcLicense && empty($value)) {
                        $fail('PRC License Number is required for teaching personnel, or check "I don\'t have a PRC license".');
                        return;
                    }
                    // If provided, must be numeric and exactly 7 digits
                    if (!empty($value)) {
                        if (!preg_match('/^\d+$/', $value)) {
                            $fail('PRC License Number must contain numbers only.');
                            return;
                        }
                        if (strlen($value) !== 7) {
                            $fail('PRC License Number must be exactly 7 digits.');
                        }
                    }
                },
                'nullable',
                'string',
                'max:255',
            ],
            'prcLicenseExpiry' => [
                function ($attribute, $value, $fail) {
                    // Required for teaching personnel unless they check "no license"
                    if ($this->personnelType === 'teaching' && !$this->noPrcLicense && empty($value)) {
                        $fail('PRC License Expiry Date is required for teaching personnel, or check "I don\'t have a PRC license".');
                    }
                    // If provided, must be a future date
                    if (!empty($value) && $value <= now()->format('Y-m-d')) {
                        $fail('PRC License Expiry Date must be a future date.');
                    }
                },
                'nullable',
                'date',
            ],
            'signatureConsent' => ['required', 'accepted'],
            'signatureData' => ['required', 'string'],
        ], [
            'mobilePhone.required' => 'Please enter your mobile phone number.',
            'position.required' => 'Please enter your position.',
            'signatureConsent.required' => 'You must certify that the information is true and correct.',
            'signatureData.required' => 'Please provide your signature by drawing it on the signature pad.',
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

        // Process signature
        $signatureImage = $this->signatureData;
        $signatureUploadPath = null;

        // Create attendee first (without signature processing)
        $suffix = trim((string) $this->suffix);
        $suffix = ltrim($suffix, " \t\n\r\0\x0B,"); // allow user to type ", Jr." but store "Jr."
        
        // Normalize invalid values to null (N/A, None, etc.)
        if ($suffix !== '') {
            $normalized = strtolower($suffix);
            $invalidValues = ['n/a', 'na', 'none', 'nil', 'null', '-', '--', '---'];
            if (in_array($normalized, $invalidValues)) {
                $suffix = null;
            }
        }
        
        // Set to null if empty after processing
        if ($suffix === '') {
            $suffix = null;
        }

        $attendee = Attendee::create([
            'seminar_id' => $this->seminar->id,
            'name' => trim($this->firstName . ' ' . ($this->middleName ? $this->middleName . ' ' : '') . $this->lastName . ($suffix ? ', ' . $suffix : '')), // Keep for backward compatibility
            'email' => $this->email,
            'position' => $this->position,
            'ticket_hash' => $ticketHash,
            'personnel_type' => $this->personnelType,
            'first_name' => $this->firstName,
            'middle_name' => $this->middleName,
            'last_name' => $this->lastName,
            'suffix' => $suffix ?: null,
            'sex' => $this->sex,
            'school_id' => $this->schoolId === 'other' || $this->schoolId === '' ? null : (int) $this->schoolId,
            'school_other' => $this->schoolId === 'other' ? $this->schoolOther : null,
            'school_office_agency' => $this->getSchoolOfficeAgencyValue(),
            'mobile_phone' => $this->mobilePhone,
            'prc_license_no' => $this->noPrcLicense ? null : ($this->prcLicenseNo ?: null),
            'prc_license_expiry' => $this->noPrcLicense ? null : ($this->prcLicenseExpiry ?: null),
            'signature_consent' => $this->signatureConsent,
            'signature_image' => null, // Will be set after processing
            'signature_upload_path' => $signatureUploadPath,
        ]);

        // Process and secure signature
        if ($signatureImage) {
            $securityService = app(SignatureSecurityService::class);
            $processed = $securityService->processSignature($signatureImage, $attendee, $this->seminar);
            
            // Store file to disk
            $processed['signature_upload_path'] = $securityService->storeSignatureFile($processed['signature_image'], $attendee, $this->seminar);
            
            $attendee->update($processed);
        }

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
