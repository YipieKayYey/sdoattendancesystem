<?php

namespace App\Filament\Attendee\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class SeminarHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Seminar History';

    protected static ?string $title = 'My Seminar History';

    protected static string $view = 'filament.attendee.pages.seminar-history';

    public function table(Table $table): Table
    {
        return $table
            ->query(auth()->user()->attendees()->with(['seminar', 'checkIns.seminarDay'])->getQuery())
            ->columns([
                Tables\Columns\TextColumn::make('seminar.title')
                    ->label('Seminar')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->seminar?->title)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->paginated([10, 25, 50])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('View')
                    ->modalHeading(fn ($record) => $record->seminar?->title ?? 'Seminar Details')
                    ->modalContent(fn ($record) => view('filament.attendee.components.seminar-details-modal', [
                        'attendee' => $record,
                        'seminar' => $record->seminar,
                        'checkIns' => $record->checkIns()->with('seminarDay')->orderBy('seminar_day_id')->get(),
                    ])),
            ]);
    }
}
