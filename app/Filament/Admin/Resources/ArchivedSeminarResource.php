<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ArchivedSeminarResource\Pages;
use App\Filament\Admin\Resources\ArchivedSeminarResource\RelationManagers;
use App\Models\Seminar;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class ArchivedSeminarResource extends Resource
{
    protected static ?string $model = Seminar::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Archived Seminars';

    protected static ?string $modelLabel = 'Archived Seminar';

    protected static ?string $pluralModelLabel = 'Archived Seminars';

    protected static ?string $navigationGroup = 'Seminars';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'archived-seminars';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->disabled(),
                Forms\Components\TextInput::make('slug')
                    ->disabled(),
                Forms\Components\DatePicker::make('date')
                    ->disabled(),
                Forms\Components\TextInput::make('venue')
                    ->label('Venue')
                    ->disabled(),
                Forms\Components\Textarea::make('topic')
                    ->label('Topic/s')
                    ->disabled(),
                Forms\Components\TextInput::make('time')
                    ->label('Time')
                    ->disabled(),
                Forms\Components\TextInput::make('room')
                    ->label('Room')
                    ->disabled(),
                Forms\Components\Toggle::make('is_open')
                    ->label('Open Seminar (Unlimited Capacity)')
                    ->disabled(),
                Forms\Components\TextInput::make('capacity')
                    ->label('Capacity')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->description('Archived')
                    ->icon('heroicon-o-archive-box')
                    ->iconColor('warning'),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('is_open')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => $state ? 'Open' : 'Limited')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->icon(fn ($state) => $state ? 'heroicon-o-globe-alt' : 'heroicon-o-lock-closed'),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Archived At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('restore')
                    ->label('Restore')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
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
                    Tables\Actions\BulkAction::make('restore')
                        ->label('Restore selected')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->restore();
                            }
                            Notification::make()
                                ->title('Seminars restored')
                                ->body(count($records) . ' seminar(s) have been restored successfully.')
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
            'index' => Pages\ListArchivedSeminars::route('/'),
            'view' => Pages\ViewArchivedSeminar::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
