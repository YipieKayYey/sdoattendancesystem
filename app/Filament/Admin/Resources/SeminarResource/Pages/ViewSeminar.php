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
        return $data;
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->disabled(),
                    Forms\Components\TextInput::make('slug')
                        ->label('URL for Pre-Registration')
                        ->disabled(),
                Forms\Components\DatePicker::make('date')
                    ->disabled(),
                Forms\Components\Toggle::make('is_open')
                    ->label('Open Seminar (Unlimited Capacity)')
                    ->disabled(),
                Forms\Components\TextInput::make('capacity')
                    ->label('Capacity')
                    ->disabled(),
                Forms\Components\TextInput::make('registration_url')
                    ->label('Registration URL')
                    ->disabled()
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
                    ->helperText('Click the clipboard icon to copy the registration URL. You can also select and copy the text manually.'),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => !$this->record->trashed()),
            Actions\Action::make('archive')
                ->label('Archive')
                ->icon('heroicon-o-archive-box')
                ->color('warning')
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
