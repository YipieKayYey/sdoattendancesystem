<?php

namespace App\Filament\Admin\Resources\SeminarResource\RelationManagers;

use App\Models\AttendeeCheckIn;
use App\Models\School;
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

    protected function getSelectedDayId(): ?int
    {
        $state = $this->getTableFilterState('day_id');
        $value = $state['value'] ?? $state['day_id'] ?? null;

        return $value ? (int) $value : null;
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

        $selectedDayId = $this->getSelectedDayId();
        $isMultiDay = $this->ownerRecord && $this->ownerRecord->isMultiDay();

        // When a day is selected (multi-day), show only that day's columns
        if ($isMultiDay && $selectedDayId) {
            $day = SeminarDay::find($selectedDayId);
            if ($day && $day->seminar_id === $this->ownerRecord->id) {
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
        } elseif ($isMultiDay) {
            // Multi-day, no day selected: show all days' columns
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
            ->filters(
                array_filter([
                    // Day filter first — choose a date, then check-in/out filters appear
                    $this->ownerRecord && $this->ownerRecord->isMultiDay() && $this->ownerRecord->days()->exists()
                        ? Tables\Filters\SelectFilter::make('day_id')
                            ->label('Day')
                            ->placeholder('Choose a day first...')
                            ->options(fn () => $this->ownerRecord->days()->orderBy('day_number')->get()->mapWithKeys(fn ($d) => [$d->id => "Day {$d->day_number} ({$d->date->format('M j, Y')})"]))
                            ->query(function ($query, array $data) {
                                $dayId = $data['value'] ?? null;
                                return filled($dayId)
                                    ? $query->whereHas('checkIns', fn ($q) => $q->where('seminar_day_id', $dayId))
                                    : $query;
                            })
                        : null,
                    // Check-in filter — visible only when a day is selected (multi-day)
                    Tables\Filters\TernaryFilter::make('checked_in_at')
                        ->label('Check-in Status')
                        ->placeholder('All attendees')
                        ->trueLabel('Checked in')
                        ->falseLabel('Not checked in')
                        ->visible(fn () => !$this->ownerRecord?->isMultiDay() || $this->getSelectedDayId() !== null)
                        ->queries(
                            true: function ($query) {
                                $dayId = $this->getSelectedDayId();
                                if ($this->ownerRecord && $this->ownerRecord->isMultiDay()) {
                                    $q = fn ($q) => $q->whereNotNull('checked_in_at');
                                    if ($dayId) {
                                        $q = fn ($q) => $q->where('seminar_day_id', $dayId)->whereNotNull('checked_in_at');
                                    }
                                    return $query->whereHas('checkIns', $q);
                                }
                                return $query->whereNotNull('checked_in_at');
                            },
                            false: function ($query) {
                                $dayId = $this->getSelectedDayId();
                                if ($this->ownerRecord && $this->ownerRecord->isMultiDay()) {
                                    if ($dayId) {
                                        return $query->whereDoesntHave('checkIns', fn ($q) => $q->where('seminar_day_id', $dayId)->whereNotNull('checked_in_at'));
                                    }
                                    return $query->whereDoesntHave('checkIns', fn ($q) => $q->whereNotNull('checked_in_at'));
                                }
                                return $query->whereNull('checked_in_at');
                            },
                        ),
                    Tables\Filters\TernaryFilter::make('checked_out_at')
                        ->label('Check-out Status')
                        ->placeholder('All attendees')
                        ->trueLabel('Checked out')
                        ->falseLabel('Not checked out')
                        ->visible(fn () => !$this->ownerRecord?->isMultiDay() || $this->getSelectedDayId() !== null)
                        ->queries(
                            true: function ($query) {
                                $dayId = $this->getSelectedDayId();
                                if ($this->ownerRecord && $this->ownerRecord->isMultiDay()) {
                                    $q = fn ($q) => $q->whereNotNull('checked_out_at');
                                    if ($dayId) {
                                        $q = fn ($q) => $q->where('seminar_day_id', $dayId)->whereNotNull('checked_out_at');
                                    }
                                    return $query->whereHas('checkIns', $q);
                                }
                                return $query->whereNotNull('checked_out_at');
                            },
                            false: function ($query) {
                                $dayId = $this->getSelectedDayId();
                                if ($this->ownerRecord && $this->ownerRecord->isMultiDay()) {
                                    if ($dayId) {
                                        return $query->whereDoesntHave('checkIns', fn ($q) => $q->where('seminar_day_id', $dayId)->whereNotNull('checked_out_at'));
                                    }
                                    return $query->whereDoesntHave('checkIns', fn ($q) => $q->whereNotNull('checked_out_at'));
                                }
                                return $query->whereNull('checked_out_at');
                            },
                        ),
                ])
            )
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
                    ->visible(fn () => $this->ownerRecord)
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
                    ->modalDescription('Choose which day (for date/venue) and attendees to include.')
                    ->form(function (): array {
                        $isMultiDay = $this->ownerRecord->isMultiDay();
                        $dayOptions = $isMultiDay
                            ? $this->ownerRecord->days->mapWithKeys(function ($day) {
                                $label = 'Day ' . $day->day_number . ' - ' . $day->date->format('F j, Y');
                                $venue = $day->venue ?? $this->ownerRecord->venue ?? 'N/A';
                                $label .= ' / ' . $venue;
                                return [$day->id => $label];
                            })
                            : [];
                        $form = [
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
                        ];
                        if ($isMultiDay && $dayOptions->isNotEmpty()) {
                            array_unshift($form, Forms\Components\Select::make('day_id')
                                ->label('Day (Date & Venue)')
                                ->options($dayOptions)
                                ->required()
                                ->default(fn () => $this->ownerRecord->days->first()?->id)
                                ->helperText('Date and venue in the sheet header will use the selected day'));
                        }
                        return $form;
                    })
                    ->action(function (array $data) {
                        $attendeeIds = $data['attendee_ids'];
                        $blankSignatures = $data['blank_signatures'] ?? false;
                        $params = [
                            'seminar' => $this->ownerRecord->id,
                            'attendee_ids' => implode(',', $attendeeIds),
                            'blank_signatures' => $blankSignatures ? '1' : '0',
                        ];
                        if ($this->ownerRecord->isMultiDay() && !empty($data['day_id'])) {
                            $params['day_id'] = $data['day_id'];
                        }
                        $url = route('seminars.export-registration-sheet', $params);
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
                Tables\Actions\Action::make('export_gnr_attendance_sheet')
                    ->label('GNR Attendance')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('info')
                    ->size('sm')
                    ->visible(fn () => $this->ownerRecord)
                    ->modalHeading('Select Attendees for GNR Attendance Sheet')
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
                                    return $query->get()->mapWithKeys(fn ($a) => [$a->id => $a->full_name ?? $a->name]);
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
                                ->helperText('Leave signature column blank (for handwritten signatures)')
                                ->default(false),
                        ];
                        if ($isMultiDay && $dayOptions->isNotEmpty()) {
                            array_unshift($form, Forms\Components\Select::make('day_id')
                                ->label('Day')
                                ->options($dayOptions)
                                ->required()
                                ->default(fn () => $this->ownerRecord->days->first()?->id)
                                ->helperText('The date and venue will follow the selected day'));
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
                        $url = route('seminars.export-gnr-attendance-sheet', $params);
                        \Filament\Notifications\Notification::make()
                            ->title('Opening GNR attendance sheet in new tab...')
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
                                    ->label('Full Name')
                                    ->content(fn ($record) => $record->full_name ?: $record->name),
                                Forms\Components\Placeholder::make('first_name')
                                    ->label('First Name')
                                    ->content(fn ($record) => $record->first_name ?? '—'),
                                Forms\Components\Placeholder::make('middle_name')
                                    ->label('Middle Name')
                                    ->content(fn ($record) => $record->middle_name ?? '—'),
                                Forms\Components\Placeholder::make('last_name')
                                    ->label('Last Name')
                                    ->content(fn ($record) => $record->last_name ?? '—'),
                                Forms\Components\Placeholder::make('suffix')
                                    ->label('Suffix')
                                    ->content(fn ($record) => $record->suffix ?? '—'),
                                Forms\Components\Placeholder::make('type')
                                    ->label('Personnel Type')
                                    ->content(fn ($record) => $record->personnel_type === 'teaching'
                                        ? 'Teaching'
                                        : ($record->personnel_type === 'non_teaching' ? 'Non-Teaching' : '—')),
                                Forms\Components\Placeholder::make('sex')
                                    ->label('Sex')
                                    ->content(fn ($record) => $record->sex ? ucfirst($record->sex) : '—'),
                                Forms\Components\Placeholder::make('school_office_agency')
                                    ->label('School/Office/Agency')
                                    ->content(fn ($record) => $record->school_office_agency_display),
                                Forms\Components\Placeholder::make('position')
                                    ->label('Position')
                                    ->content(fn ($record) => $record->position ?? '—'),
                            ])
                            ->columns(2),
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
                                        ? $record->prc_license_expiry->format('F j, Y')
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
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->size('sm')
                    ->modalHeading('Correct Attendee Information')
                    ->modalDescription('Fix typos or errors in attendee details.')
                    ->mutateRecordDataUsing(function (array $data): array {
                        if (!empty($data['school_other'])) {
                            $data['school_id'] = 'other';
                        } elseif (empty($data['school_id']) && !empty($data['school_office_agency'])) {
                            $data['school_id'] = 'other';
                            $data['school_other'] = $data['school_office_agency'];
                        }
                        return $data;
                    })
                    ->form([
                        Forms\Components\Section::make('Name')
                            ->schema([
                                Forms\Components\TextInput::make('first_name')
                                    ->label('First Name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('middle_name')
                                    ->label('Middle Name')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('last_name')
                                    ->label('Last Name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('suffix')
                                    ->label('Suffix (Jr., Sr., III, etc.)')
                                    ->maxLength(50)
                                    ->placeholder('Leave blank if none'),
                            ])
                            ->columns(2),
                        Forms\Components\Section::make('Contact & Details')
                            ->schema([
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('mobile_phone')
                                    ->label('Mobile Phone')
                                    ->tel()
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('position')
                                    ->label('Position')
                                    ->maxLength(255),
                                Forms\Components\Select::make('school_id')
                                    ->label('School/Office/Agency')
                                    ->options(function () {
                                        $schools = School::orderBy('name')->pluck('name', 'id')->all();
                                        $schools['other'] = 'Others (please specify)';
                                        return $schools;
                                    })
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                                        if ($state !== 'other') {
                                            $set('school_other', null);
                                        }
                                    }),
                                Forms\Components\TextInput::make('school_other')
                                    ->label('Specify School/Office/Agency')
                                    ->visible(fn (Forms\Get $get) => $get('school_id') === 'other')
                                    ->required(fn (Forms\Get $get) => $get('school_id') === 'other')
                                    ->maxLength(255)
                                    ->placeholder('Enter if not in the list'),
                                Forms\Components\Select::make('personnel_type')
                                    ->label('Personnel Type')
                                    ->options(['teaching' => 'Teaching', 'non_teaching' => 'Non-Teaching'])
                                    ->required(),
                                Forms\Components\Select::make('sex')
                                    ->label('Sex')
                                    ->options(['male' => 'Male', 'female' => 'Female']),
                            ])
                            ->columns(2),
                        Forms\Components\Section::make('PRC License')
                            ->schema([
                                Forms\Components\TextInput::make('prc_license_no')
                                    ->label('PRC License No.')
                                    ->maxLength(255),
                                Forms\Components\DatePicker::make('prc_license_expiry')
                                    ->label('PRC Expiry'),
                            ])
                            ->columns(2)
                            ->collapsible(),
                    ]),
                Tables\Actions\Action::make('check_in_manual')
                    ->label('Check In')
                    ->icon('heroicon-o-check-circle')
                    ->size('sm')
                    ->visible(fn () => $this->ownerRecord)
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
                    ->action(function ($record, array $data) {
                        if ($this->ownerRecord && $this->ownerRecord->isMultiDay()) {
                            $dayId = $data['day_id'] ?? null;
                            if (!$dayId) {
                                $hasUncheckedDay = $this->ownerRecord->days->contains(function ($day) use ($record) {
                                    return AttendeeCheckIn::where('attendee_id', $record->id)
                                        ->where('seminar_day_id', $day->id)
                                        ->whereNotNull('checked_in_at')
                                        ->doesntExist();
                                });
                                \Filament\Notifications\Notification::make()
                                    ->title($hasUncheckedDay ? 'Day selection required' : 'Already checked in')
                                    ->body($hasUncheckedDay ? 'Please select a day to check in.' : 'This attendee is already checked in for all days.')
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
                    ->visible(fn () => $this->ownerRecord)
                    ->modalSubmitActionLabel('Check Out')
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
                    ->action(function ($record, array $data) {
                        if ($this->ownerRecord && $this->ownerRecord->isMultiDay()) {
                            $dayId = $data['day_id'] ?? null;
                            if (!$dayId) {
                                $hasAnyToCheckOut = AttendeeCheckIn::where('attendee_id', $record->id)
                                    ->whereIn('seminar_day_id', $this->ownerRecord->days->pluck('id'))
                                    ->whereNotNull('checked_in_at')
                                    ->whereNull('checked_out_at')
                                    ->exists();
                                \Filament\Notifications\Notification::make()
                                    ->title($hasAnyToCheckOut ? 'Day selection required' : 'Not checked in')
                                    ->body($hasAnyToCheckOut ? 'Please select a day to check out.' : 'This attendee must be checked in first before checking out.')
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
                            if (!$record->checked_in_at) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Not checked in')
                                    ->body('This attendee must be checked in first before checking out.')
                                    ->warning()
                                    ->send();
                                return;
                            }
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
