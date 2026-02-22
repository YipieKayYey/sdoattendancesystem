<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\School;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Users';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Account')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255),
                        Forms\Components\Select::make('role')
                            ->options(['admin' => 'Admin', 'attendee' => 'Attendee'])
                            ->required()
                            ->default('attendee')
                            ->live(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Attendee Profile')
                    ->description('Profile for division personnel. Signature is added by the attendee in their dashboard.')
                    ->schema(static::getProfileFormSchema())
                    ->visible(fn (Forms\Get $get) => $get('role') === 'attendee')
                    ->columns(2),
            ]);
    }

    protected static function getProfileFormSchema(): array
    {
        return [
            Forms\Components\Select::make('personnel_type')
                ->label('Personnel Type')
                ->options(['teaching' => 'Teaching', 'non_teaching' => 'Non-Teaching'])
                ->required(),
            Forms\Components\TextInput::make('first_name')
                ->label('First Name')
                ->maxLength(255),
            Forms\Components\TextInput::make('middle_name')
                ->label('Middle Name')
                ->maxLength(255),
            Forms\Components\TextInput::make('last_name')
                ->label('Last Name')
                ->maxLength(255),
            Forms\Components\TextInput::make('suffix')
                ->label('Suffix (Jr., Sr., III, etc.)')
                ->maxLength(50)
                ->placeholder('Leave blank if none'),
            Forms\Components\Select::make('sex')
                ->label('Sex')
                ->options(['male' => 'Male', 'female' => 'Female']),
            Forms\Components\TextInput::make('mobile_phone')
                ->label('Mobile Phone')
                ->tel()
                ->maxLength(20),
            Forms\Components\TextInput::make('position')
                ->label('Position')
                ->maxLength(255),
            Forms\Components\Select::make('school_id')
                ->label('School/Office/Agency')
                ->options(function () {
                    $schools = School::orderBy('name')->pluck('name', 'id')->all();
                    $schools['other'] = 'Others (please specify)';
                    return $schools;
                })
                ->searchable()
                ->live()
                ->afterStateUpdated(function (Forms\Set $set, $state) {
                    if ($state !== 'other') {
                        $set('school_other', null);
                    }
                }),
            Forms\Components\TextInput::make('school_other')
                ->label('Specify School/Office/Agency')
                ->visible(fn (Forms\Get $get) => $get('school_id') === 'other')
                ->required(fn (Forms\Get $get) => $get('school_id') === 'other')
                ->maxLength(255)
                ->placeholder('Enter if not in the list'),
            Forms\Components\TextInput::make('prc_license_no')
                ->label('PRC License No.')
                ->maxLength(255),
            Forms\Components\DatePicker::make('prc_license_expiry')
                ->label('PRC Expiry'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'attendee' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('attendeeProfile.universal_qr_hash')
                    ->label('Universal QR')
                    ->placeholder('—')
                    ->copyable()
                    ->copyMessage('Copied')
                    ->visible(fn ($record) => $record?->role === 'attendee' && $record?->attendeeProfile),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options(['admin' => 'Admin', 'attendee' => 'Attendee']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
