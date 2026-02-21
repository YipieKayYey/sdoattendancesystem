<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SeminarResource\Pages;
use App\Filament\Admin\Resources\SeminarResource\RelationManagers;
use App\Models\Seminar;
use App\Models\SeminarDay;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;

class SeminarResource extends Resource
{
    protected static ?string $model = Seminar::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Seminars';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Seminar Information Section
                Forms\Components\Section::make('Seminar Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('slug')
                                    ->label('URL for Pre-Registration')
                                    ->required()
                                    ->maxLength(8)
                                    ->minLength(8)
                                    ->unique(ignoreRecord: true)
                                    ->alphaNum()
                                    ->default(fn () => Str::random(8))
                                    ->helperText('8-character unique hash for registration URL. Auto-generated if left empty.')
                                    ->columnSpanFull(),
                                Forms\Components\DatePicker::make('date')
                                    ->label(fn (Get $get) => $get('is_multi_day') ? 'Primary Date (Day 1)' : 'Date')
                                    ->required()
                                    ->native(false)
                                    ->helperText(fn (Get $get) => $get('is_multi_day') ? 'This date will be used for Day 1. Additional days start from Day 2.' : null)
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state, Get $get) {
                                        // Store the date for the multi-day toggle to use
                                        $set('primary_date_for_multiday', $state);
                                        
                                        // For multi-day seminars, sync primary date with Day 1
                                        if ($get('is_multi_day') && $state) {
                                            $set('sync_primary_date_to_day1', true);
                                        }
                                    }),
                                Forms\Components\Toggle::make('is_multi_day')
                                    ->label('Multi-Day Seminar')
                                    ->helperText('Enable if this seminar spans multiple days')
                                    ->live()
                                    ->default(false)
                                    ->afterStateUpdated(function (Set $set, $state, Get $get) {
                                        // When multi-day is enabled, ensure Day 1 exists from primary date
                                        if ($state && $get('date')) {
                                            $days = $get('days') ?? [];
                                            $hasDay1 = false;
                                            
                                            // Check if Day 1 exists in the form
                                            foreach ($days as $idx => $day) {
                                                if (isset($day['day_number']) && $day['day_number'] == 1) {
                                                    $hasDay1 = true;
                                                    // Update Day 1's date to match primary date
                                                    $set("days.{$idx}.date", $get('date'));
                                                    break;
                                                }
                                            }
                                            
                                            // If Day 1 doesn't exist, add it as the first item
                                            if (!$hasDay1) {
                                                $newDays = [
                                                    [
                                                        'day_number' => 1,
                                                        'date' => $get('date'),
                                                        'start_time' => null,
                                                        'venue' => null,
                                                        'topic' => null,
                                                        'room' => null,
                                                    ],
                                                    ...$days
                                                ];
                                                $set('days', $newDays);
                                            }
                                        }
                                        
                                        // When multi-day is disabled, remove all days
                                        if (!$state) {
                                            $set('days', []);
                                        }
                                        
                                        // Handle sync trigger from date picker
                                        if ($get('sync_primary_date_to_day1') && $get('primary_date_for_multiday')) {
                                            $days = $get('days') ?? [];
                                            $primaryDate = $get('primary_date_for_multiday');
                                            $day1Updated = false;
                                            
                                            foreach ($days as $idx => $day) {
                                                if (isset($day['day_number']) && $day['day_number'] == 1) {
                                                    $set("days.{$idx}.date", $primaryDate);
                                                    $day1Updated = true;
                                                    break;
                                                }
                                            }
                                            
                                            if (!$day1Updated) {
                                                $newDays = [
                                                    [
                                                        'day_number' => 1,
                                                        'date' => $primaryDate,
                                                        'start_time' => null,
                                                        'venue' => null,
                                                        'topic' => null,
                                                        'room' => null,
                                                    ],
                                                    ...$days
                                                ];
                                                $set('days', $newDays);
                                            }
                                            
                                            // Clear the sync trigger
                                            $set('sync_primary_date_to_day1', false);
                                        }
                                    }),
                                Forms\Components\Toggle::make('is_open')
                                    ->label('Open Seminar (Unlimited Capacity)')
                                    ->helperText('Enable this if you don\'t know how many attendees will register. Capacity will be unlimited.')
                                    ->live()
                                    ->default(false)
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        if ($state) {
                                            $set('capacity', null);
                                        }
                                    }),
                                Forms\Components\TextInput::make('capacity')
                                    ->label('Capacity')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required(fn (Get $get) => !$get('is_open'))
                                    ->visible(fn (Get $get) => !$get('is_open'))
                                    ->dehydrated(fn (Get $get) => !$get('is_open'))
                                    ->helperText('Number of available spots. Not required for open seminars.'),
                                Forms\Components\Toggle::make('is_ended')
                                    ->label('Seminar Ended')
                                    ->helperText('Mark this seminar as ended to prevent further registrations')
                                    ->default(false),
                            ]),
                    ]),
                
                // Single Day Settings Section
                Forms\Components\Section::make('Single Day Settings')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\Select::make('time_hour')
                                            ->label('Hour')
                                            ->options(array_combine(range(1,12), range(1,12)))
                                            ->required()
                                            ->default(9),
                                        Forms\Components\Select::make('time_minute')
                                            ->label('Minute')
                                            ->options([
                                                '00' => '00',
                                                '05' => '05',
                                                '10' => '10',
                                                '15' => '15',
                                                '20' => '20',
                                                '25' => '25',
                                                '30' => '30',
                                                '35' => '35',
                                                '40' => '40',
                                                '45' => '45',
                                                '50' => '50',
                                                '55' => '55',
                                            ])
                                            ->required()
                                            ->default('00'),
                                        Forms\Components\Select::make('time_period')
                                            ->label('Period')
                                            ->options(['AM' => 'AM', 'PM' => 'PM'])
                                            ->required()
                                            ->default('AM'),
                                    ]),
                                Forms\Components\TextInput::make('venue')
                                    ->label('Venue')
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Textarea::make('topic')
                                    ->label('Topic/s')
                                    ->rows(3)
                                    ->helperText('For Attendance Sheet'),
                            ]),
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\TextInput::make('room')
                                    ->label('Room')
                                    ->maxLength(255)
                                    ->helperText('For Attendance Sheet'),
                            ]),
                    ])
                    ->visible(fn (Get $get) => !$get('is_multi_day')),
                
                // Multi-Day Settings Section
                Forms\Components\Section::make('Multi-Day Settings')
                    ->schema([
                        Forms\Components\Repeater::make('days')
                            ->relationship('days')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('day_number')
                                            ->label('Day')
                                            ->numeric()
                                            ->default(function ($get, $record) {
                                                $existing = $get('../../days') ?? [];
                                                
                                                // If we have a record (editing), check the actual relationship
                                                if ($record && $record->exists) {
                                                    $maxDayNumber = $record->days()->max('day_number') ?? 0;
                                                    return $maxDayNumber + 1;
                                                }
                                                
                                                // For new records, use the count of existing items in the form
                                                // Filter out items without day_number to get accurate count
                                                $existingWithNumbers = array_filter($existing, fn($day) => isset($day['day_number']) && $day['day_number'] > 0);
                                                $maxDayNumber = 0;
                                                foreach ($existingWithNumbers as $day) {
                                                    if (isset($day['day_number']) && $day['day_number'] > $maxDayNumber) {
                                                        $maxDayNumber = $day['day_number'];
                                                    }
                                                }
                                                
                                                return $maxDayNumber + 1;
                                            })
                                            ->required()
                                            ->disabled()
                                            ->dehydrated(),
                                        Forms\Components\DatePicker::make('date')
                                            ->required()
                                            ->native(false)
                                            ->helperText(fn (Get $get) => 
                                                $get('day_number') == 1 ? 'This is Day 1 - it syncs with the Primary Date above.' : null
                                            ),
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\Select::make('start_time_hour')
                                                    ->label('Hour')
                                                    ->options(array_combine(range(1,12), range(1,12)))
                                                    ->required()
                                                    ->default(9)
                                                    ->afterStateHydrated(function ($component, $state, $record) {
                                                        if ($record && $record->start_time) {
                                                            $time = substr($record->start_time, 0, 5); // Get HH:MM part
                                                            [$hour, $minute] = explode(':', $time);
                                                            
                                                            $period = $hour >= 12 ? 'PM' : 'AM';
                                                            $displayHour = $hour > 12 ? $hour - 12 : ($hour === 0 ? 12 : $hour);
                                                            
                                                            $component->state($displayHour);
                                                        }
                                                    }),
                                                Forms\Components\Select::make('start_time_minute')
                                                    ->label('Minute')
                                                    ->options([
                                                        '00' => '00',
                                                        '05' => '05',
                                                        '10' => '10',
                                                        '15' => '15',
                                                        '20' => '20',
                                                        '25' => '25',
                                                        '30' => '30',
                                                        '35' => '35',
                                                        '40' => '40',
                                                        '45' => '45',
                                                        '50' => '50',
                                                        '55' => '55',
                                                    ])
                                                    ->required()
                                                    ->default('00')
                                                    ->afterStateHydrated(function ($component, $state, $record) {
                                                        if ($record && $record->start_time) {
                                                            $time = substr($record->start_time, 0, 5); // Get HH:MM part
                                                            [$hour, $minute] = explode(':', $time);
                                                            
                                                            $component->state($minute);
                                                        }
                                                    }),
                                                Forms\Components\Select::make('start_time_period')
                                                    ->label('Period')
                                                    ->options(['AM' => 'AM', 'PM' => 'PM'])
                                                    ->required()
                                                    ->default('AM')
                                                    ->afterStateHydrated(function ($component, $state, $record) {
                                                        if ($record && $record->start_time) {
                                                            $time = substr($record->start_time, 0, 5); // Get HH:MM part
                                                            [$hour, $minute] = explode(':', $time);
                                                            
                                                            $period = $hour >= 12 ? 'PM' : 'AM';
                                                            
                                                            $component->state($period);
                                                        }
                                                    }),
                                            ]),
                                        Forms\Components\TextInput::make('venue')
                                            ->label('Venue')
                                            ->maxLength(255),
                                        Forms\Components\Textarea::make('topic')
                                            ->label('Topic/s')
                                            ->rows(2),
                                        Forms\Components\TextInput::make('room')
                                            ->label('Room')
                                            ->maxLength(255),
                                    ]),
                            ])
                            ->defaultItems(1)
                            ->minItems(1)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['date'] ? 'Day ' . ($state['day_number'] ?? '?') . ' - ' . \Carbon\Carbon::parse($state['date'])->format('M j, Y') : null),
                    ])
                    ->visible(fn (Get $get) => $get('is_multi_day')),

                Forms\Components\Section::make('Client Satisfaction Survey')
                    ->schema([
                        Forms\Components\TextInput::make('survey_form_url')
                            ->label('Microsoft Forms / Survey URL')
                            ->url()
                            ->maxLength(500)
                            ->placeholder('https://forms.office.com/...')
                            ->helperText('Optional. When set, use the tracking link in emails. Clicks are counted and shown in the seminar view.'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (config('app.debug')) {
            \Log::info('mutateFormDataBeforeCreate called with: ' . json_encode(array_keys($data)));
            if (isset($data['days'])) {
                \Log::info('Days data: ' . json_encode($data['days']));
            }
        }
        return self::convertTimeFields($data);
    }

    public static function mutateFormDataBeforeUpdate(array $data): array
    {
        if (config('app.debug')) {
            \Log::info('mutateFormDataBeforeUpdate called with: ' . json_encode(array_keys($data)));
            if (isset($data['days'])) {
                \Log::info('Days data: ' . json_encode($data['days']));
            }
        }
        return self::convertTimeFields($data);
    }

    public static function convertTimeFields(array $data): array
    {
        $debug = config('app.debug');
        if ($debug) {
            \Log::info('convertTimeFields called');
        }

        // Handle single-day seminar time conversion
        if (isset($data['time_hour'], $data['time_minute'], $data['time_period'])) {
            if ($debug) {
                \Log::info('Processing single-day time conversion');
            }
            $hour = (int)$data['time_hour'];
            $minute = $data['time_minute'];
            $period = $data['time_period'];
            
            // Convert to 24-hour format
            if ($period === 'PM' && $hour !== 12) {
                $hour += 12;
            } elseif ($period === 'AM' && $hour === 12) {
                $hour = 0;
            }
            
            // Store as HH:MM format (no seconds)
            $data['time'] = sprintf('%02d:%02d', $hour, $minute);
            
            // Clean up temporary fields
            unset($data['time_hour'], $data['time_minute'], $data['time_period']);
        }
        
        // Handle multi-day seminar time conversion
        if (isset($data['days']) && is_array($data['days'])) {
            if ($debug) {
                \Log::info('Processing multi-day time conversion for ' . count($data['days']) . ' days');
            }
            foreach ($data['days'] as $index => &$day) {
                if ($debug) {
                    \Log::info('Day ' . $index . ' keys: ' . json_encode(array_keys($day)));
                }
                if (isset($day['start_time_hour'], $day['start_time_minute'], $day['start_time_period'])) {
                    if ($debug) {
                        \Log::info('Converting time for day ' . $index);
                    }
                    $hour = (int)$day['start_time_hour'];
                    $minute = $day['start_time_minute'];
                    $period = $day['start_time_period'];

                    // Convert to 24-hour format
                    if ($period === 'PM' && $hour !== 12) {
                        $hour += 12;
                    } elseif ($period === 'AM' && $hour === 12) {
                        $hour = 0;
                    }

                    $day['start_time'] = sprintf('%02d:%02d:00', $hour, $minute);
                    if ($debug) {
                        \Log::info('Set start_time to: ' . $day['start_time']);
                    }

                    // Clean up temporary fields
                    unset($day['start_time_hour'], $day['start_time_minute'], $day['start_time_period']);
                } elseif ($debug) {
                    \Log::info('Missing time fields for day ' . $index . '. Keys: ' . json_encode(array_keys($day)));
                }
            }
        }

        if ($debug) {
            \Log::info('convertTimeFields completed');
        }
        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(fn (Seminar $record) => $record->title)
                    ->description(fn (Seminar $record) => $record->trashed() ? 'Archived' : null)
                    ->icon(fn (Seminar $record) => $record->trashed() ? 'heroicon-o-archive-box' : null)
                    ->iconColor(fn (Seminar $record) => $record->trashed() ? 'warning' : null),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable()
                    ->description(fn (?Seminar $record) => $record && $record->isMultiDay() ? $record->days()->count() . ' day(s)' : null),
                Tables\Columns\TextColumn::make('days_count')
                    ->label('Days')
                    ->getStateUsing(fn (Seminar $record) => $record ? $record->days()->count() : 0)
                    ->badge()
                    ->color(fn ($state) => $state > 1 ? 'info' : 'gray')
                    ->visible(fn (?Seminar $record) => $record && $record->isMultiDay()),
                Tables\Columns\TextColumn::make('is_ended')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state ? 'Ended' : 'Active')
                    ->badge()
                    ->color(fn ($state) => $state ? 'danger' : 'success')
                    ->icon(fn ($state) => $state ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Archive selected')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->delete();
                            }
                            Notification::make()
                                ->title('Seminars archived')
                                ->body(count($records) . ' seminar(s) have been archived successfully.')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AttendeesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeminars::route('/'),
            'create' => Pages\CreateSeminar::route('/create'),
            'view' => Pages\ViewSeminar::route('/{record}'),
            'edit' => Pages\EditSeminar::route('/{record}/edit'),
        ];
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        return self::convertTimeFields($data);
    }
}
