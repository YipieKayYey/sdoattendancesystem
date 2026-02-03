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
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('slug')
                                    ->label('URL for Pre-Registration')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->alphaDash()
                                    ->helperText('This will be used in the registration URL.')
                                    ->columnSpanFull(),
                                Forms\Components\Toggle::make('is_multi_day')
                                    ->label('Multi-Day Seminar')
                                    ->helperText('Enable if this seminar spans multiple days')
                                    ->live()
                                    ->default(false)
                                    ->dehydrated(false)
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
                                    }),
                                Forms\Components\DatePicker::make('date')
                                    ->label(fn (Get $get) => $get('is_multi_day') ? 'Primary Date (Day 1)' : 'Date')
                                    ->required()
                                    ->native(false)
                                    ->helperText(fn (Get $get) => $get('is_multi_day') ? 'This date will be used for Day 1. Additional days start from Day 2.' : null)
                                    ->afterStateUpdated(function (Set $set, $state, Get $get) {
                                        // For multi-day seminars, sync primary date with Day 1
                                        if ($get('is_multi_day') && $state) {
                                            $days = $get('days') ?? [];
                                            // Update Day 1's date if it exists in the form
                                            foreach ($days as $idx => $day) {
                                                if (isset($day['day_number']) && $day['day_number'] == 1) {
                                                    $set("days.{$idx}.date", $state);
                                                    break;
                                                }
                                            }
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
                                Forms\Components\TextInput::make('time')
                                    ->label('Time')
                                    ->helperText('For Attendance Sheet (Format: HH:MM, e.g., 08:00 or 13:30)')
                                    ->placeholder('08:00')
                                    ->maxLength(5)
                                    ->regex('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/')
                                    ->validationMessages([
                                        'regex' => 'Please enter time in HH:MM format (e.g., 08:00 or 13:30)',
                                    ])
                                    ->afterStateHydrated(function ($component, $state) {
                                        if ($state === null || $state === '') {
                                            $component->state(null);
                                            return;
                                        }
                                        if ($state instanceof \DateTime || $state instanceof \Carbon\Carbon) {
                                            $component->state($state->format('H:i'));
                                            return;
                                        }
                                        $timeStr = trim((string)$state);
                                        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}/', $timeStr) || preg_match('/^\d{4}-\d{2}-\d{2}/', $timeStr)) {
                                            $component->state(null);
                                            return;
                                        }
                                        if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $timeStr, $matches)) {
                                            $component->state(sprintf('%02d:%02d', $matches[1], $matches[2]));
                                        } elseif (preg_match('/^(\d{1,2}):(\d{2})$/', $timeStr)) {
                                            $component->state($timeStr);
                                        } else {
                                            $component->state(null);
                                        }
                                    }),
                                Forms\Components\TextInput::make('venue')
                                    ->label('Venue')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('topic')
                                    ->label('Topic/s')
                                    ->rows(3)
                                    ->helperText('For Attendance Sheet'),
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
                                            ->helperText(fn (Get $get) => $get('day_number') == 1 ? 'This is Day 1 - it syncs with the Primary Date above.' : null)
                                            ->afterStateUpdated(function (Set $set, $state, Get $get, $component) {
                                                // If this is Day 1, sync with primary date
                                                $dayNumber = $get('day_number');
                                                if ($dayNumber == 1 && $state) {
                                                    $set('../../date', $state);
                                                }
                                            }),
                                        Forms\Components\TextInput::make('start_time')
                                            ->label('Start Time')
                                            ->helperText('Format: HH:MM, e.g., 08:00')
                                            ->placeholder('08:00')
                                            ->maxLength(5)
                                            ->regex('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'),
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
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
                Tables\Columns\TextColumn::make('is_open')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => $state ? 'Open' : 'Limited')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->icon(fn ($state) => $state ? 'heroicon-o-globe-alt' : 'heroicon-o-lock-closed'),
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
                Tables\Actions\EditAction::make()
                    ->visible(fn (Seminar $record) => !$record->trashed()),
                Tables\Actions\Action::make('endSeminar')
                    ->label(fn (Seminar $record) => $record->is_ended ? 'Reopen Seminar' : 'End Seminar')
                    ->icon(fn (Seminar $record) => $record->is_ended ? 'heroicon-o-arrow-path' : 'heroicon-o-x-circle')
                    ->color(fn (Seminar $record) => $record->is_ended ? 'success' : 'danger')
                    ->visible(fn (Seminar $record) => !$record->trashed())
                    ->requiresConfirmation()
                    ->modalHeading(fn (Seminar $record) => $record->is_ended ? 'Reopen Seminar' : 'End Seminar')
                    ->modalDescription(fn (Seminar $record) => $record->is_ended 
                        ? 'This will reopen registration for this seminar. Users will be able to register again.'
                        : 'This will end the seminar and prevent new registrations. Existing registrations will remain.')
                    ->action(function (Seminar $record) {
                        $record->is_ended = !$record->is_ended;
                        $record->save();
                        Notification::make()
                            ->title($record->is_ended ? 'Seminar ended' : 'Seminar reopened')
                            ->body($record->is_ended 
                                ? 'Registration for this seminar has been closed.'
                                : 'Registration for this seminar has been reopened.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('archive')
                    ->label('Archive')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->visible(fn (Seminar $record) => !$record->trashed())
                    ->requiresConfirmation()
                    ->action(function (Seminar $record) {
                        $record->delete();
                        Notification::make()
                            ->title('Seminar archived')
                            ->body('The seminar has been archived successfully.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('restore')
                    ->label('Restore')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->visible(fn (Seminar $record) => $record->trashed())
                    ->requiresConfirmation()
                    ->action(function (Seminar $record) {
                        $record->restore();
                        Notification::make()
                            ->title('Seminar restored')
                            ->body('The seminar has been restored successfully.')
                            ->success()
                            ->send();
                    }),
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
}
