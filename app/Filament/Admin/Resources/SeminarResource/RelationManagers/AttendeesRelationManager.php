<?php

namespace App\Filament\Admin\Resources\SeminarResource\RelationManagers;

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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->label('Position')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('ticket_hash')
                    ->label('Ticket Hash')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Ticket hash copied!')
                    ->fontFamily('mono'),
                Tables\Columns\IconColumn::make('checked_in_at')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn ($record) => $record->checked_in_at !== null),
                Tables\Columns\TextColumn::make('checked_in_at')
                    ->label('Checked In At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not checked in'),
            ])
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
            ])
            ->headerActions([
                Tables\Actions\Action::make('check_in')
                    ->label('Check In Attendee')
                    ->icon('heroicon-o-qr-code')
                    ->url(fn () => \App\Filament\Admin\Pages\CheckInAttendee::getUrl(['seminar' => $this->ownerRecord->id]))
                    ->color('success'),
            ])
            ->actions([
                Tables\Actions\Action::make('check_in_manual')
                    ->label('Check In')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn ($record) => $record->checked_in_at === null)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['checked_in_at' => now()]);
                        \Filament\Notifications\Notification::make()
                            ->title('Attendee checked in successfully!')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
