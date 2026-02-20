<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AnalyticsResource\Pages;
use App\Models\Seminar;
use App\Services\SeminarAnalyticsService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AnalyticsResource extends Resource
{
    protected static ?string $model = Seminar::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Analytics & Reports';

    protected static ?int $navigationSort = 3;

    protected static ?string $label = 'Seminar Analytics';

    protected static ?string $pluralLabel = 'Seminar Analytics';

    protected static ?string $modelLabel = 'Seminar Analytics';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('seminar_id')
                    ->label('Select Seminar')
                    ->options(Seminar::pluck('title', 'id'))
                    ->required()
                    ->searchable()
                    ->live(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('registered_count')
                    ->label('Registrations')
                    ->getStateUsing(fn ($record) => $record->attendees()->count())
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withCount('attendees')->orderBy('attendees_count', $direction);
                    }),
                Tables\Columns\TextColumn::make('checked_in_count')
                    ->label('Checked In')
                    ->getStateUsing(fn ($record) => app(SeminarAnalyticsService::class)->getCheckInCountsOnly($record)[0])
                    ->sortable(false),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->isMultiDay() ? 'Multi-day' : 'Single day')
                    ->color(fn (string $state) => $state === 'Multi-day' ? 'info' : 'gray'),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view_analytics')
                    ->label('View Analytics')
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn ($record) => Pages\ViewAnalytics::getUrl(['record' => $record->id])),
            ]);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnalytics::route('/'),
            'view' => Pages\ViewAnalytics::route('/{record}'),
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

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
