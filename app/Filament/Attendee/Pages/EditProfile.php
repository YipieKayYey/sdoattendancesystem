<?php

namespace App\Filament\Attendee\Pages;

use App\Models\School;
use App\Services\SignatureSecurityService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class EditProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationLabel = 'Edit Profile';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Edit Profile';

    protected static string $view = 'filament.attendee.pages.edit-profile';

    public ?array $data = [];

    public ?string $signatureData = null;

    public bool $signatureConsent = false;

    public function mount(): void
    {
        $user = auth()->user();
        $profile = $user->attendeeProfile;
        if (!$profile) {
            $this->redirect(route('filament.attendee.pages.dashboard'));
            return;
        }
        $this->form->fill([
            'email' => $user->email ?? '',
            'personnel_type' => $profile->personnel_type,
            'first_name' => $profile->first_name,
            'middle_name' => $profile->middle_name,
            'last_name' => $profile->last_name,
            'suffix' => $profile->suffix,
            'sex' => $profile->sex,
            'school_id' => $profile->school_id ?? ($profile->school_other ? 'other' : null),
            'school_other' => $profile->school_other,
            'mobile_phone' => $profile->mobile_phone,
            'position' => $profile->position,
            'no_prc_license' => false,
            'prc_license_no' => $profile->prc_license_no,
            'prc_license_expiry' => $profile->prc_license_expiry,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Account')
                    ->description('Your login email. You can change it if the admin registered a different one.')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->rule(Rule::unique('users', 'email')->ignore(auth()->id()))
                            ->helperText('Change this if the admin registered a different email for you.'),
                    ])
                    ->columns(1),
                Forms\Components\Section::make('Change Password')
                    ->icon('heroicon-o-lock-closed')
                    ->description('Leave blank to keep your current password.')
                    ->schema([
                        Forms\Components\TextInput::make('current_password')
                            ->label('Current password')
                            ->password()
                            ->revealable()
                            ->autocomplete('current-password')
                            ->dehydrated(false)
                            ->required(fn (Get $get): bool => filled($get('password')))
                            ->rule('current_password:web')
                            ->live(debounce: 500)
                            ->helperText('Required when changing password.'),
                        Forms\Components\TextInput::make('password')
                            ->label('New password')
                            ->password()
                            ->revealable()
                            ->rule(Password::default())
                            ->autocomplete('new-password')
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->live(debounce: 500)
                            ->same('passwordConfirmation')
                            ->required(fn (Get $get): bool => filled($get('current_password'))),
                        Forms\Components\TextInput::make('passwordConfirmation')
                            ->label('Confirm new password')
                            ->password()
                            ->revealable()
                            ->required(fn (Get $get): bool => filled($get('password')))
                            ->visible(fn (Get $get): bool => filled($get('password')))
                            ->dehydrated(false),
                    ])
                    ->columns(1),
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\Select::make('personnel_type')
                            ->label('Personnel Type')
                            ->options(['teaching' => 'Teaching', 'non_teaching' => 'Non-Teaching'])
                            ->required()
                            ->helperText('Select whether you are teaching or non-teaching personnel.'),
                        Forms\Components\TextInput::make('first_name')->label('First Name')->maxLength(255)->required()->helperText('Enter your first name.'),
                        Forms\Components\TextInput::make('middle_name')->label('Middle Name')->maxLength(255)->required()->helperText('Enter your full middle name.'),
                        Forms\Components\TextInput::make('last_name')->label('Last Name')->maxLength(255)->required()->helperText('Enter your last name.'),
                        Forms\Components\TextInput::make('suffix')->label('Suffix')->maxLength(50)->placeholder('Jr., Sr., III')->helperText('Leave blank if you don\'t have a suffix (do not enter N/A).'),
                        Forms\Components\Select::make('sex')->label('Sex')->options(['male' => 'Male', 'female' => 'Female'])->helperText('Select your sex.'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Contact & Work')
                    ->schema([
                        Forms\Components\TextInput::make('mobile_phone')->label('Mobile Phone')->tel()->maxLength(20)->required()->helperText('Enter your mobile phone number.'),
                        Forms\Components\TextInput::make('position')->label('Position')->maxLength(255)->required()->helperText('Enter your position or job title.'),
                        Forms\Components\Select::make('school_id')
                            ->label('School/Office/Agency')
                            ->options(fn () => School::orderBy('name')->pluck('name', 'id')->all() + ['other' => 'Others (specify below)'])
                            ->searchable()->live()
                            ->required()
                            ->helperText('Choose your school, office, or agency.'),
                        Forms\Components\TextInput::make('school_other')
                            ->label('Specify School/Office/Agency')
                            ->visible(fn (Forms\Get $get) => $get('school_id') === 'other')
                            ->maxLength(255)
                            ->required(fn (Forms\Get $get) => $get('school_id') === 'other')
                            ->helperText('Enter your school, office, or agency if not listed above.'),
                        Forms\Components\Checkbox::make('no_prc_license')
                            ->label("I don't have a PRC license")
                            ->live()
                            ->helperText('Check this box if you do not have a PRC license.'),
                        Forms\Components\TextInput::make('prc_license_no')
                            ->label('PRC License No.')
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get) => !$get('no_prc_license'))
                            ->required(fn (Forms\Get $get) => !$get('no_prc_license'))
                            ->helperText('7 digits only.'),
                        Forms\Components\DatePicker::make('prc_license_expiry')
                            ->label('PRC Expiry')
                            ->visible(fn (Forms\Get $get) => !$get('no_prc_license'))
                            ->required(fn (Forms\Get $get) => !$get('no_prc_license'))
                            ->helperText('Enter your PRC license expiry date.'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Privacy Policy and E-Signature Collection')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Placeholder::make('privacy_notice')
                            ->content(view('filament.attendee.components.privacy-notice-modal-content')),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = auth()->user();
        $profile = $user->attendeeProfile;

        if (filled($data['password'] ?? null)) {
            $currentPassword = $this->data['current_password'] ?? '';
            if (empty($currentPassword) || ! Hash::check($currentPassword, $user->password)) {
                $this->addError('data.current_password', 'The current password is incorrect.');
                return;
            }
        }

        $profileData = [
            'personnel_type' => $data['personnel_type'],
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'],
            'last_name' => $data['last_name'],
            'suffix' => $data['suffix'],
            'sex' => $data['sex'],
            'mobile_phone' => $data['mobile_phone'],
            'position' => $data['position'],
            'prc_license_no' => ($data['no_prc_license'] ?? false) ? null : ($data['prc_license_no'] ?? null),
            'prc_license_expiry' => ($data['no_prc_license'] ?? false) ? null : ($data['prc_license_expiry'] ?? null),
        ];

        if (($data['school_id'] ?? null) === 'other') {
            $profileData['school_id'] = null;
            $profileData['school_office_agency'] = $data['school_other'] ?? null;
        } elseif (!empty($data['school_id'])) {
            $school = School::find((int) $data['school_id']);
            $profileData['school_id'] = (int) $data['school_id'];
            $profileData['school_office_agency'] = $school?->name;
        }

        $signatureData = $this->signatureData;
        if ($signatureData && str_starts_with($signatureData, 'data:image')) {
            if (!$this->signatureConsent) {
                \Filament\Notifications\Notification::make()
                    ->title('Consent required')
                    ->body('Please read and consent to the Privacy Policy and E-Signature Collection notice before saving your signature.')
                    ->danger()
                    ->send();
                return;
            }
            $secured = app(SignatureSecurityService::class)->processSignatureForProfile($signatureData, $profile);
            $profileData = array_merge($profileData, $secured);
        }

        $profile->update($profileData);

        $userData = [];
        if (isset($data['email']) && $data['email'] !== $user->email) {
            $userData['email'] = $data['email'];
        }
        if (filled($data['password'] ?? null)) {
            $userData['password'] = $data['password'];
        }
        if (!empty($userData)) {
            $user->update($userData);
        }

        $this->data['current_password'] = null;
        $this->data['password'] = null;
        $this->data['passwordConfirmation'] = null;

        \Filament\Notifications\Notification::make()
            ->title('Profile updated')
            ->success()
            ->send();

        $this->redirect(ViewProfile::getUrl());
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Back to Profile')
                ->icon('heroicon-o-arrow-left')
                ->url(ViewProfile::getUrl())
                ->color('gray'),
        ];
    }
}
