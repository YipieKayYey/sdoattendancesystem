<?php

namespace App\Filament\Admin\Resources\SeminarResource\Pages;

use App\Filament\Admin\Resources\SeminarResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewSeminar extends ViewRecord
{
    protected static string $resource = SeminarResource::class;

    protected function resolveRecord(int | string $key): \Illuminate\Database\Eloquent\Model
    {
        return static::getModel()::withTrashed()->findOrFail($key);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['registration_url'] = $this->record->registration_url;
        
        // CRITICAL: Prevent any Carbon parsing of time field
        // Get raw value directly from database to avoid any casting
        $rawTime = $this->record->getRawOriginal('time');
        
        if ($rawTime) {
            $rawTime = trim((string)$rawTime);
            // Reject date formats immediately
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}/', $rawTime) || preg_match('/^\d{4}-\d{2}-\d{2}/', $rawTime)) {
                $data['time'] = null;
            }
            // Extract HH:MM from HH:MM:SS
            elseif (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $rawTime, $matches)) {
                $data['time'] = sprintf('%02d:%02d', $matches[1], $matches[2]);
            }
            // Validate it's a time format
            elseif (preg_match('/^(\d{1,2}):(\d{2})$/', $rawTime)) {
                $data['time'] = $rawTime;
            } else {
                $data['time'] = null;
            }
        } else {
            $data['time'] = null;
        }
        
        return $data;
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                // Seminar Information Section
                Forms\Components\Section::make('Seminar Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Seminar Title')
                                    ->disabled()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('slug')
                                    ->label('URL for Pre-Registration')
                                    ->disabled()
                                    ->columnSpanFull(),
                                Forms\Components\DatePicker::make('date')
                                    ->label('Primary Date')
                                    ->disabled(),
                                Forms\Components\Toggle::make('is_multi_day')
                                    ->label('Multi-Day Seminar')
                                    ->disabled(),
                                Forms\Components\Toggle::make('is_open')
                                    ->label('Open Seminar (Unlimited Capacity)')
                                    ->disabled(),
                                Forms\Components\TextInput::make('capacity')
                                    ->label('Capacity')
                                    ->disabled()
                                    ->visible(fn ($record) => !$record->is_open),
                                Forms\Components\Toggle::make('is_ended')
                                    ->label('Seminar Ended')
                                    ->disabled(),
                            ]),
                    ]),
                
                // Single Day Settings Section
                Forms\Components\Section::make('Single Day Settings')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('time')
                                    ->label('Time')
                                    ->disabled(),
                                Forms\Components\TextInput::make('venue')
                                    ->label('Venue')
                                    ->disabled(),
                                Forms\Components\Textarea::make('topic')
                                    ->label('Topic/s')
                                    ->rows(3)
                                    ->disabled(),
                                Forms\Components\TextInput::make('room')
                                    ->label('Room')
                                    ->disabled(),
                            ]),
                    ])
                    ->visible(fn ($record) => !$record->is_multi_day),
                
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
                                            ->disabled(),
                                        Forms\Components\DatePicker::make('date')
                                            ->label('Date')
                                            ->disabled(),
                                        Forms\Components\TextInput::make('start_time')
                                            ->label('Start Time')
                                            ->disabled(),
                                        Forms\Components\TextInput::make('venue')
                                            ->label('Venue')
                                            ->disabled(),
                                        Forms\Components\Textarea::make('topic')
                                            ->label('Topic/s')
                                            ->rows(2)
                                            ->disabled(),
                                        Forms\Components\TextInput::make('room')
                                            ->label('Room')
                                            ->disabled(),
                                    ]),
                            ])
                            ->disabled()
                            ->itemLabel(fn (array $state): ?string => isset($state['day_number']) ? 'Day ' . $state['day_number'] : null)
                            ->collapsible()
                            ->collapsed(),
                    ])
                    ->visible(fn ($record) => $record->is_multi_day),
                
                // Registration Information Section
                Forms\Components\Section::make('Registration Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('registration_url')
                                    ->label('Registration URL')
                                    ->disabled()
                                    ->columnSpanFull()
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('copy')
                                            ->icon('heroicon-o-clipboard')
                                            ->action(function () {
                                                $url = $this->record->registration_url;
                                                $this->js("navigator.clipboard.writeText('{$url}')");
                                                Notification::make()
                                                    ->title('Registration URL copied!')
                                                    ->body($url)
                                                    ->success()
                                                    ->send();
                                            })
                                    )
                                    ->helperText('Click the clipboard icon to copy the registration URL.'),
                                Forms\Components\ViewField::make('registration_qr')
                                    ->label('Registration QR Code')
                                    ->view('components.seminar-registration-qr')
                                    ->viewData(['seminar' => $this->record]),
                                Forms\Components\Placeholder::make('registered_count')
                                    ->label('Registered Attendees')
                                    ->content(fn ($record) => $record->attendees()->count() . ' / ' . ($record->is_open ? 'Unlimited' : $record->capacity)),
                                Forms\Components\Placeholder::make('checked_in_count')
                                    ->label('Checked In')
                                    ->content(fn ($record) => $record->attendees()->whereNotNull('checked_in_at')->count()),
                                Forms\Components\Placeholder::make('checked_out_count')
                                    ->label('Checked Out')
                                    ->content(fn ($record) => $record->attendees()->whereNotNull('checked_out_at')->count()),
                                Forms\Components\Placeholder::make('available_spots')
                                    ->label('Available Spots')
                                    ->content(fn ($record) => $record->is_open ? 'Unlimited' : max(0, $record->capacity - $record->attendees()->count()))
                                    ->visible(fn ($record) => !$record->is_open),
                            ]),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->size('sm')
                ->visible(fn () => !$this->record->trashed()),
            Actions\Action::make('archive')
                ->label('Archive')
                ->icon('heroicon-o-archive-box')
                ->color('warning')
                ->size('sm')
                ->visible(fn () => !$this->record->trashed())
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->delete();
                    Notification::make()
                        ->title('Seminar archived')
                        ->body('The seminar has been archived successfully.')
                        ->success()
                        ->send();
                    $this->redirect(SeminarResource::getUrl('index'));
                }),
            Actions\Action::make('restore')
                ->label('Restore')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('success')
                ->size('sm')
                ->visible(fn () => $this->record->trashed())
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->restore();
                    Notification::make()
                        ->title('Seminar restored')
                        ->body('The seminar has been restored successfully.')
                        ->success()
                        ->send();
                    $this->redirect(SeminarResource::getUrl('index'));
                }),
        ];
    }
}
