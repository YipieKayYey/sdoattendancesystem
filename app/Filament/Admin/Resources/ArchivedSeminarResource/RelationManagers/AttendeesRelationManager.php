<?php

namespace App\Filament\Admin\Resources\ArchivedSeminarResource\RelationManagers;

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
            ->modifyQueryUsing(fn (Builder $query) => $query
                // Order by latest DB update (e.g., check-in/out changes)
                ->orderByDesc('updated_at')
                // Stable fallback ordering
                ->orderByDesc('id'))
            ->poll('5s')
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->getStateUsing(fn ($record) => $record->full_name ?: $record->name)
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
                Tables\Columns\TextColumn::make('personnel_type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => $state === 'teaching' ? 'Teaching' : ($state === 'non_teaching' ? 'Non-Teaching' : '—'))
                    ->badge()
                    ->color(fn ($state) => $state === 'teaching' ? 'success' : ($state === 'non_teaching' ? 'gray' : null))
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->label('Position')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('checked_in_at')
                    ->label('Checked In')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not checked in'),
                Tables\Columns\TextColumn::make('checked_out_at')
                    ->label('Checked Out')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not checked out'),
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
                //
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
                            ->schema([
                                Forms\Components\Placeholder::make('checked_in_at')
                                    ->label('Checked In')
                                    ->content(fn ($record) => $record->checked_in_at
                                        ? $record->checked_in_at->format('M j, Y g:i A')
                                        : 'Not checked in'),
                                Forms\Components\Placeholder::make('checked_out_at')
                                    ->label('Checked Out')
                                    ->content(fn ($record) => $record->checked_out_at
                                        ? $record->checked_out_at->format('M j, Y g:i A')
                                        : 'Not checked out'),
                            ])
                            ->columns(2),
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
