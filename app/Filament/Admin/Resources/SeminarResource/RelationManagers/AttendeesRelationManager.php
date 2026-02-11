<?php

namespace App\Filament\Admin\Resources\SeminarResource\RelationManagers;

use App\Models\AttendeeCheckIn;
use App\Models\SeminarDay;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AttendeesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendees';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    protected function getTableColumns(): array
    {
        $columns = [
            Tables\Columns\TextColumn::make('full_name')
                ->label('Name')
                ->getStateUsing(fn ($record) => $record->full_name ?? $record->name)
                ->searchable(query: function (Builder $query, string $search): Builder {
                    return $query->where(function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                          ->orWhere('middle_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%")
                          ->orWhere('suffix', 'like', "%{$search}%")
                          ->orWhere('name', 'like', "%{$search}%");
                    });
                })
                ->sortable(),
            Tables\Columns\TextColumn::make('position')
                ->label('Position')
                ->searchable()
                ->sortable()
                ->placeholder('—'),
        ];

        // For multi-day seminars, show per-day columns
        if ($this->ownerRecord && $this->ownerRecord->isMultiDay()) {
            $days = $this->ownerRecord->days()->orderBy('day_number')->get();
            foreach ($days as $day) {
                $columns[] = Tables\Columns\TextColumn::make("day_{$day->id}_checkin")
                    ->label("Day {$day->day_number} Check-in")
                    ->getStateUsing(function ($record) use ($day) {
                        $checkIn = AttendeeCheckIn::where('attendee_id', $record->id)
                            ->where('seminar_day_id', $day->id)
                            ->whereNotNull('checked_in_at')
                            ->first();
                        return $checkIn ? $checkIn->checked_in_at->format('M j, Y g:i A') : '—';
                    })
                    ->badge()
                    ->color(fn ($state) => $state !== '—' ? 'success' : 'gray')
                    ->icon(fn ($state) => $state !== '—' ? 'heroicon-o-check-circle' : null)
                    ->sortable(false);
                
                $columns[] = Tables\Columns\TextColumn::make("day_{$day->id}_checkout")
                    ->label("Day {$day->day_number} Check-out")
                    ->getStateUsing(function ($record) use ($day) {
                        $checkIn = AttendeeCheckIn::where('attendee_id', $record->id)
                            ->where('seminar_day_id', $day->id)
                            ->whereNotNull('checked_out_at')
                            ->first();
                        return $checkIn ? $checkIn->checked_out_at->format('M j, Y g:i A') : '—';
                    })
                    ->badge()
                    ->color(fn ($state) => $state !== '—' ? 'warning' : 'gray')
                    ->icon(fn ($state) => $state !== '—' ? 'heroicon-o-arrow-right-on-rectangle' : null)
                    ->sortable(false);
            }
        } else {
            // Single-day seminar - show simple columns
            $columns[] = Tables\Columns\TextColumn::make('checked_in_at')
                ->label('Checked In')
                ->dateTime()
                ->sortable()
                ->placeholder('Not checked in');
            
            $columns[] = Tables\Columns\TextColumn::make('checked_out_at')
                ->label('Checked Out')
                ->dateTime()
                ->sortable()
                ->placeholder('Not checked out');
        }

        return $columns;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn (Builder $query) => $query
                // Order by latest DB update (e.g., check-in/out changes)
                ->orderByDesc('updated_at')
                // Stable fallback ordering
                ->orderByDesc('id'))
            ->poll('5s')
            ->columns($this->getTableColumns())
            ->filters([
                Tables\Filters\TernaryFilter::make('checked_in_at')
                    ->label('Check-in Status')
                    ->placeholder('All attendees')
                    ->trueLabel('Checked in')
                    ->falseLabel('Not checked in')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('checked_in_at'),
                        false: fn ($query) => $query->whereNull('checked_in_at'),
                    ),
                Tables\Filters\TernaryFilter::make('checked_out_at')
                    ->label('Check-out Status')
                    ->placeholder('All attendees')
                    ->trueLabel('Checked out')
                    ->falseLabel('Not checked out')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('checked_out_at'),
                        false: fn ($query) => $query->whereNull('checked_out_at'),
                    ),
            ])
            ->headerActions([
                Tables\Actions\Action::make('check_in')
                    ->label('Check In')
                    ->icon('heroicon-o-qr-code')
                    ->color('success')
                    ->size('sm')
                    ->visible(fn () => $this->ownerRecord)
                    ->modalHeading('Select Day for Check-In')
                    ->modalDescription('This is a multi-day seminar. Please select which day you want to check in attendees for.')
                    ->modalSubmitActionLabel('Go to Check-In')
                    ->form(fn () => $this->ownerRecord->isMultiDay() ? [
                        Forms\Components\Select::make('day_id')
                            ->label('Select Day')
                            ->options(function () {
                                $days = $this->ownerRecord->days()->orderBy('day_number')->get();
                                $dayOptions = [];
                                foreach ($days as $day) {
                                    $label = "Day {$day->day_number} - {$day->formatted_date}";
                                    if ($day->start_time) {
                                        $label .= " ({$day->formatted_time})";
                                    }
                                    $dayOptions[$day->id] = $label;
                                }
                                return $dayOptions;
                            })
                            ->required()
                            ->placeholder('Choose a day...')
                            ->helperText('Select which day you will be checking in attendees for.')
                            ->searchable(false)
                            ->native(false),
                    ] : null)
                    ->action(function (array $data = []) {
                        // Single-day: redirect directly (no form data)
                        if (!$this->ownerRecord->isMultiDay()) {
                            $url = \App\Filament\Admin\Pages\CheckInAttendee::getUrl();
                            return redirect($url . '?' . http_build_query(['seminar' => $this->ownerRecord->id]));
                        }
                        
                        // Multi-day: use form data
                        if ($this->ownerRecord->isMultiDay() && !empty($data)) {
                            $dayId = $data['day_id'] ?? null;
                            if ($dayId) {
                                // Redirect to check-in page with day parameter as query string
                                $url = \App\Filament\Admin\Pages\CheckInAttendee::getUrl();
                                return redirect($url . '?' . http_build_query([
                                    'seminar' => $this->ownerRecord->id,
                                    'day' => $dayId,
                                ]));
                            }
                        }
                    }),
                Tables\Actions\Action::make('check_out')
                    ->label('Check Out')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('warning')
                    ->size('sm')
                    ->visible(fn () => $this->ownerRecord && $this->ownerRecord->attendees()->whereNotNull('checked_in_at')->exists())
                    ->modalHeading('Select Day for Check-Out')
                    ->modalDescription('This is a multi-day seminar. Please select which day you want to check out attendees for.')
                    ->modalSubmitActionLabel('Go to Check-Out')
                    ->form(fn () => $this->ownerRecord->isMultiDay() ? [
                        Forms\Components\Select::make('day_id')
                            ->label('Select Day')
                            ->options(function () {
                                $days = $this->ownerRecord->days()->orderBy('day_number')->get();
                                $dayOptions = [];
                                foreach ($days as $day) {
                                    $label = "Day {$day->day_number} - {$day->formatted_date}";
                                    if ($day->start_time) {
                                        $label .= " ({$day->formatted_time})";
                                    }
                                    $dayOptions[$day->id] = $label;
                                }
                                return $dayOptions;
                            })
                            ->required()
                            ->placeholder('Choose a day...')
                            ->helperText('Select which day you will be checking out attendees for.')
                            ->searchable(false)
                            ->native(false),
                    ] : null)
                    ->action(function (array $data = []) {
                        // Single-day: redirect directly (no form data)
                        if (!$this->ownerRecord->isMultiDay()) {
                            $url = \App\Filament\Admin\Pages\CheckOutAttendee::getUrl();
                            return redirect($url . '?' . http_build_query(['seminar' => $this->ownerRecord->id]));
                        }
                        
                        // Multi-day: use form data
                        if ($this->ownerRecord->isMultiDay() && !empty($data)) {
                            $dayId = $data['day_id'] ?? null;
                            if ($dayId) {
                                // Redirect to check-out page with day parameter as query string
                                $url = \App\Filament\Admin\Pages\CheckOutAttendee::getUrl();
                                return redirect($url . '?' . http_build_query([
                                    'seminar' => $this->ownerRecord->id,
                                    'day' => $dayId,
                                ]));
                            }
                        }
                    }),
                Tables\Actions\Action::make('export_registration_sheet')
                    ->label('CPD Registration')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->size('sm')
                    ->modalHeading('Select Attendees for Registration Sheet')
                    ->modalDescription('Choose which attendees to include in the registration sheet export.')
                    ->form([
                        Forms\Components\Select::make('attendee_ids')
                            ->label('Attendees')
                            ->options(function () {
                                return $this->ownerRecord->attendees()
                                    ->orderBy('created_at')
                                    ->get()
                                    ->mapWithKeys(function ($attendee) {
                                        $name = $attendee->full_name ?: $attendee->name;
                                        return [$attendee->id => $name];
                                    });
                            })
                            ->default(function () {
                                return $this->ownerRecord->attendees()->pluck('id')->toArray();
                            })
                            ->required()
                            ->multiple()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Toggle::make('blank_signatures')
                            ->label('Blank Signatures')
                            ->helperText('Leave signature column blank (for agencies that don\'t allow e-signatures)')
                            ->default(false),
                    ])
                    ->action(function (array $data) {
                        $attendeeIds = $data['attendee_ids'];
                        $blankSignatures = $data['blank_signatures'] ?? false;
                        $url = route('seminars.export-registration-sheet', [
                            'seminar' => $this->ownerRecord->id,
                            'attendee_ids' => implode(',', $attendeeIds),
                            'blank_signatures' => $blankSignatures ? '1' : '0',
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Opening registration sheet in new tab...')
                            ->success()
                            ->send();
                        $this->js("window.open('{$url}', '_blank')");
                    }),
                Tables\Actions\Action::make('export_attendance_sheet')
                    ->label('CPD Attendance')
                    ->icon('heroicon-o-document-check')
                    ->color('info')
                    ->size('sm')
                    ->visible(fn () => $this->ownerRecord)
                    ->modalHeading('Select Attendees for Attendance Sheet')
                    ->modalDescription('Choose which day and checked-in attendees to include.')
                    ->form(function (): array {
                        $isMultiDay = $this->ownerRecord->isMultiDay();
                        $dayOptions = $isMultiDay
                            ? $this->ownerRecord->days->mapWithKeys(function ($day) {
                                $label = 'Day ' . $day->day_number . ' - ' . $day->date->format('F j, Y');
                                if ($day->start_time) {
                                    $label .= ' ' . $day->formatted_time;
                                }
                                return [$day->id => $label];
                            })
                            : [];
                        $form = [
                            Forms\Components\Select::make('attendee_ids')
                                ->label('Attendees')
                                ->options(function () use ($isMultiDay) {
                                    $query = $this->ownerRecord->attendees()
                                        ->orderByRaw('COALESCE(NULLIF(last_name, ""), name) ASC')
                                        ->orderBy('first_name');
                                    if ($isMultiDay) {
                                        $query->whereHas('checkIns', fn ($q) => $q->whereNotNull('checked_in_at'));
                                    } else {
                                        $query->whereNotNull('checked_in_at');
                                    }
                                    return $query->get()->mapWithKeys(function ($attendee) {
                                        $name = $attendee->full_name ?: $attendee->name;
                                        return [$attendee->id => $name];
                                    });
                                })
                                ->default(function () use ($isMultiDay) {
                                    $query = $this->ownerRecord->attendees();
                                    if ($isMultiDay) {
                                        $query->whereHas('checkIns', fn ($q) => $q->whereNotNull('checked_in_at'));
                                    } else {
                                        $query->whereNotNull('checked_in_at');
                                    }
                                    return $query->pluck('id')->toArray();
                                })
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->helperText('Leave empty to generate a blank sheet'),
                            Forms\Components\Toggle::make('blank_signatures')
                                ->label('Blank Signatures')
                                ->helperText('Leave signature column blank (for agencies that don\'t allow e-signatures)')
                                ->default(false),
                        ];
                        if ($isMultiDay && $dayOptions->isNotEmpty()) {
                            array_unshift($form, Forms\Components\Select::make('day_id')
                                ->label('Day')
                                ->options($dayOptions)
                                ->required()
                                ->default(fn () => $this->ownerRecord->days->first()?->id)
                                ->helperText('The date, time, venue and room will follow the selected day\'s schedule'));
                        }
                        return $form;
                    })
                    ->action(function (array $data) {
                        $attendeeIds = $data['attendee_ids'] ?? [];
                        $blankSignatures = $data['blank_signatures'] ?? false;
                        $params = [
                            'seminar' => $this->ownerRecord->id,
                            'attendee_ids' => is_array($attendeeIds) ? implode(',', $attendeeIds) : $attendeeIds,
                            'blank_signatures' => $blankSignatures ? '1' : '0',
                        ];
                        if ($this->ownerRecord->isMultiDay() && !empty($data['day_id'])) {
                            $params['day_id'] = $data['day_id'];
                        }
                        $url = route('seminars.export-attendance-sheet', $params);
                        \Filament\Notifications\Notification::make()
                            ->title('Opening attendance sheet in new tab...')
                            ->success()
                            ->send();
                        $this->js("window.open('{$url}', '_blank')");
                    }),
                Tables\Actions\Action::make('export_attendance_csv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->size('sm')
                    ->modalHeading('Select Attendees for CSV Export')
                    ->modalDescription('Choose which attendees to include in the CSV export.')
                    ->form([
                        Forms\Components\Select::make('attendee_ids')
                            ->label('Attendees')
                            ->options(function () {
                                return $this->ownerRecord->attendees()
                                    ->orderBy('updated_at', 'desc')
                                    ->get()
                                    ->mapWithKeys(function ($attendee) {
                                        $name = $attendee->full_name ?: $attendee->name;
                                        return [$attendee->id => $name];
                                    });
                            })
                            ->default(function () {
                                return $this->ownerRecord->attendees()->pluck('id')->toArray();
                            })
                            ->required()
                            ->multiple()
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (array $data) {
                        $attendeeIds = $data['attendee_ids'];
                        $url = route('seminars.export-attendance-csv', [
                            'seminar' => $this->ownerRecord->id,
                            'attendee_ids' => implode(',', $attendeeIds)
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Generating CSV export...')
                            ->success()
                            ->send();
                        return redirect($url);
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->size('sm')
                    ->modalHeading('Attendee Details')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->form([
                        Forms\Components\Section::make('Basic Information')
                            ->schema([
                                Forms\Components\Placeholder::make('name')
                                    ->label('Name')
                                    ->content(fn ($record) => $record->full_name ?: $record->name),
                                Forms\Components\Placeholder::make('type')
                                    ->label('Type')
                                    ->content(fn ($record) => $record->personnel_type === 'teaching'
                                        ? 'Teaching'
                                        : ($record->personnel_type === 'non_teaching' ? 'Non-Teaching' : '—')),
                                Forms\Components\Placeholder::make('position')
                                    ->label('Position')
                                    ->content(fn ($record) => $record->position ?? '—'),
                            ])
                            ->columns(3),
                        Forms\Components\Section::make('Contact')
                            ->schema([
                                Forms\Components\Placeholder::make('email')
                                    ->label('Email')
                                    ->content(fn ($record) => $record->email ?? '—'),
                                Forms\Components\Placeholder::make('mobile_phone')
                                    ->label('Mobile Phone')
                                    ->content(fn ($record) => $record->mobile_phone ?? '—'),
                            ])
                            ->columns(2),
                        Forms\Components\Section::make('PRC')
                            ->schema([
                                Forms\Components\Placeholder::make('prc_license_no')
                                    ->label('PRC License No.')
                                    ->content(fn ($record) => $record->isTeaching()
                                        ? ($record->prc_license_no ?? '—')
                                        : 'N/A'),
                                Forms\Components\Placeholder::make('prc_license_expiry')
                                    ->label('PRC Expiry')
                                    ->content(fn ($record) => $record->isTeaching() && $record->prc_license_expiry
                                        ? $record->prc_license_expiry->format('d/m/Y')
                                        : 'N/A'),
                            ])
                            ->columns(2),
                        Forms\Components\Section::make('Attendance')
                            ->schema(function ($record) {
                                $schema = [];
                                
                                // For multi-day seminars, show per-day attendance
                                if ($this->ownerRecord && $this->ownerRecord->isMultiDay()) {
                                    $days = $this->ownerRecord->days()->orderBy('day_number')->get();
                                    foreach ($days as $day) {
                                        $checkIn = AttendeeCheckIn::where('attendee_id', $record->id)
                                            ->where('seminar_day_id', $day->id)
                                            ->first();
                                        
                                        $schema[] = Forms\Components\Placeholder::make("day_{$day->id}_info")
                                            ->label("Day {$day->day_number} - {$day->formatted_date}")
                                            ->content(function () use ($checkIn) {
                                                if (!$checkIn) {
                                                    return 'Not checked in';
                                                }
                                                
                                                $info = [];
                                                if ($checkIn->checked_in_at) {
                                                    $info[] = "Checked in: " . $checkIn->checked_in_at->format('M j, Y g:i A');
                                                }
                                                if ($checkIn->checked_out_at) {
                                                    $info[] = "Checked out: " . $checkIn->checked_out_at->format('M j, Y g:i A');
                                                    if ($checkIn->checked_in_at) {
                                                        $duration = $checkIn->checked_in_at->diffForHumans($checkIn->checked_out_at, true);
                                                        $info[] = "Duration: {$duration}";
                                                    }
                                                }
                                                
                                                return !empty($info) ? implode(' | ', $info) : 'Not checked in';
                                            });
                                    }
                                } else {
                                    // Single-day seminar
                                    $schema[] = Forms\Components\Placeholder::make('checked_in_at')
                                        ->label('Checked In')
                                        ->content(fn ($record) => $record->checked_in_at
                                            ? $record->checked_in_at->format('M j, Y g:i A')
                                            : 'Not checked in');
                                    
                                    $schema[] = Forms\Components\Placeholder::make('checked_out_at')
                                        ->label('Checked Out')
                                        ->content(fn ($record) => $record->checked_out_at
                                            ? $record->checked_out_at->format('M j, Y g:i A')
                                            : 'Not checked out');
                                }
                                
                                return $schema;
                            })
                            ->columns(1),
                        Forms\Components\Section::make('Ticket & Signature')
                            ->schema([
                                Forms\Components\Placeholder::make('ticket_hash')
                                    ->label('Ticket Hash')
                                    ->content(fn ($record) => $record->ticket_hash ?? '—'),
                                Forms\Components\Placeholder::make('signature_status')
                                    ->label('Signature Provided')
                                    ->content(fn ($record) => $record->hasSignature() ? 'Yes' : 'No'),
                                Forms\Components\Placeholder::make('signature_timestamp')
                                    ->label('Signed At')
                                    ->content(fn ($record) => $record->signature_timestamp
                                        ? $record->signature_timestamp->format('M j, Y g:i A')
                                        : '—'),
                            ])
                            ->columns(3),
                        Forms\Components\Section::make('System')
                            ->schema([
                                Forms\Components\Placeholder::make('created_at')
                                    ->label('Registered At')
                                    ->content(fn ($record) => $record->created_at
                                        ? $record->created_at->format('M j, Y g:i A')
                                        : '—'),
                                Forms\Components\Placeholder::make('updated_at')
                                    ->label('Updated At')
                                    ->content(fn ($record) => $record->updated_at
                                        ? $record->updated_at->format('M j, Y g:i A')
                                        : '—'),
                            ])
                            ->columns(2),
                    ]),
                Tables\Actions\Action::make('check_in_manual')
                    ->label('Check In')
                    ->icon('heroicon-o-check-circle')
                    ->size('sm')
                    ->visible(function ($record) {
                        if ($this->ownerRecord && $this->ownerRecord->isMultiDay()) {
                            // For multi-day, show if not checked in for all days
                            $days = $this->ownerRecord->days;
                            foreach ($days as $day) {
                                $checkIn = AttendeeCheckIn::where('attendee_id', $record->id)
                                    ->where('seminar_day_id', $day->id)
                                    ->whereNotNull('checked_in_at')
                                    ->doesntExist();
                                if ($checkIn) {
                                    return true; // Has at least one day not checked in
                                }
                            }
                            return false;
                        }
                        return $record->checked_in_at === null;
                    })
                    ->form(function ($record) {
                        $formFields = [];
                        
                        if ($this->ownerRecord && $this->ownerRecord->isMultiDay()) {
                            $days = $this->ownerRecord->days()->orderBy('day_number')->get();
                            $dayOptions = [];
                            foreach ($days as $day) {
                                $checkIn = AttendeeCheckIn::where('attendee_id', $record->id)
                                    ->where('seminar_day_id', $day->id)
                                    ->whereNotNull('checked_in_at')
                                    ->doesntExist();
                                if ($checkIn) {
                                    $dayOptions[$day->id] = "Day {$day->day_number} - {$day->formatted_date}";
                                }
                            }
                            
                            if (!empty($dayOptions)) {
                                $formFields[] = Forms\Components\Select::make('day_id')
                                    ->label('Select Day')
                                    ->options($dayOptions)
                                    ->required()
                                    ->default(array_key_first($dayOptions));
                            }
                        }
                        
                        return $formFields;
                    })
                    ->requiresConfirmation()
                    ->action(function ($record, array $data) {
                        if ($this->ownerRecord && $this->ownerRecord->isMultiDay()) {
                            $dayId = $data['day_id'] ?? null;
                            if (!$dayId) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Day selection required')
                                    ->body('Please select a day to check in.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            $checkIn = AttendeeCheckIn::firstOrNew([
                                'attendee_id' => $record->id,
                                'seminar_day_id' => $dayId,
                            ]);
                            $checkIn->checked_in_at = now();
                            $checkIn->save();
                            
                            // Update attendee's checked_in_at for backward compatibility
                            $latestCheckIn = $record->checkIns()->whereNotNull('checked_in_at')->latest('checked_in_at')->first();
                            if ($latestCheckIn) {
                                $record->update(['checked_in_at' => $latestCheckIn->checked_in_at]);
                            }
                            
                            $day = SeminarDay::find($dayId);
                            \Filament\Notifications\Notification::make()
                                ->title('Attendee checked in successfully!')
                                ->body("Checked in for Day {$day->day_number}.")
                                ->success()
                                ->send();
                        } else {
                            $record->update(['checked_in_at' => now()]);
                            \Filament\Notifications\Notification::make()
                                ->title('Attendee checked in successfully!')
                                ->success()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('check_out_manual')
                    ->label('Check Out')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->size('sm')
                    ->visible(function ($record) {
                        if ($this->ownerRecord && $this->ownerRecord->isMultiDay()) {
                            // For multi-day, show if checked in but not checked out for at least one day
                            $days = $this->ownerRecord->days;
                            foreach ($days as $day) {
                                $checkIn = AttendeeCheckIn::where('attendee_id', $record->id)
                                    ->where('seminar_day_id', $day->id)
                                    ->whereNotNull('checked_in_at')
                                    ->whereNull('checked_out_at')
                                    ->exists();
                                if ($checkIn) {
                                    return true;
                                }
                            }
                            return false;
                        }
                        return $record->checked_in_at !== null && $record->checked_out_at === null;
                    })
                    ->form(function ($record) {
                        $formFields = [];
                        
                        if ($this->ownerRecord && $this->ownerRecord->isMultiDay()) {
                            $days = $this->ownerRecord->days()->orderBy('day_number')->get();
                            $dayOptions = [];
                            foreach ($days as $day) {
                                $checkIn = AttendeeCheckIn::where('attendee_id', $record->id)
                                    ->where('seminar_day_id', $day->id)
                                    ->whereNotNull('checked_in_at')
                                    ->whereNull('checked_out_at')
                                    ->exists();
                                if ($checkIn) {
                                    $dayOptions[$day->id] = "Day {$day->day_number} - {$day->formatted_date}";
                                }
                            }
                            
                            if (!empty($dayOptions)) {
                                $formFields[] = Forms\Components\Select::make('day_id')
                                    ->label('Select Day')
                                    ->options($dayOptions)
                                    ->required()
                                    ->default(array_key_first($dayOptions));
                            }
                        }
                        
                        return $formFields;
                    })
                    ->requiresConfirmation()
                    ->action(function ($record, array $data) {
                        if ($this->ownerRecord && $this->ownerRecord->isMultiDay()) {
                            $dayId = $data['day_id'] ?? null;
                            if (!$dayId) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Day selection required')
                                    ->body('Please select a day to check out.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            $checkIn = AttendeeCheckIn::where('attendee_id', $record->id)
                                ->where('seminar_day_id', $dayId)
                                ->whereNotNull('checked_in_at')
                                ->whereNull('checked_out_at')
                                ->first();
                            
                            if (!$checkIn) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Not checked in')
                                    ->body('This attendee must be checked in for the selected day before checking out.')
                                    ->warning()
                                    ->send();
                                return;
                            }
                            
                            $checkIn->checked_out_at = now();
                            $checkIn->save();
                            
                            // Update attendee's checked_out_at for backward compatibility
                            $latestCheckOut = $record->checkIns()->whereNotNull('checked_out_at')->latest('checked_out_at')->first();
                            if ($latestCheckOut) {
                                $record->update(['checked_out_at' => $latestCheckOut->checked_out_at]);
                            }
                            
                            $day = SeminarDay::find($dayId);
                            \Filament\Notifications\Notification::make()
                                ->title('Attendee checked out successfully!')
                                ->body("Checked out for Day {$day->day_number}.")
                                ->success()
                                ->send();
                        } else {
                            $record->update(['checked_out_at' => now()]);
                            \Filament\Notifications\Notification::make()
                                ->title('Attendee checked out successfully!')
                                ->success()
                                ->send();
                        }
                    }),
                Tables\Actions\DeleteAction::make()
                    ->size('sm'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
