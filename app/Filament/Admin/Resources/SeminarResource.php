<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SeminarResource\Pages;
use App\Filament\Admin\Resources\SeminarResource\RelationManagers;
use App\Models\Seminar;
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
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')
                    ->label('URL for Pre-Registration')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->alphaDash()
                    ->helperText('This will be used in the registration URL.'),
                Forms\Components\DatePicker::make('date')
                    ->required()
                    ->native(false),
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
                    ->sortable(),
                Tables\Columns\TextColumn::make('is_open')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => $state ? 'Open' : 'Limited')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->icon(fn ($state) => $state ? 'heroicon-o-globe-alt' : 'heroicon-o-lock-closed'),
                Tables\Columns\TextColumn::make('registration_url')
                    ->label('Registration URL')
                    ->url(fn (Seminar $record) => $record->registration_url)
                    ->copyable()
                    ->copyMessage('Registration URL copied!')
                    ->icon('heroicon-o-clipboard')
                    ->limit(50),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (Seminar $record) => !$record->trashed()),
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
