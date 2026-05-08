<?php

namespace App\Filament\Resources;

use App\Enums\UserStatus;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Identity';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

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
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('mobile')
                            ->tel()
                            ->required()
                            ->maxLength(30),
                        Forms\Components\Select::make('status')
                            ->options([
                                UserStatus::Active->value => 'Active',
                                UserStatus::Inactive->value => 'Inactive',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation) => $operation === 'create')
                            ->rule(Password::defaults())
                            ->dehydrateStateUsing(fn (?string $state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn (?string $state) => filled($state)),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Roles')
                    ->schema([
                        Forms\Components\CheckboxList::make('roles')
                            ->relationship('roles', 'name')
                            ->options(fn () => Role::query()
                                ->whereIn('name', ['admin', 'lecturer', 'student'])
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->mapWithKeys(fn (string $name, int|string $id) => [$id => str($name)->headline()->toString()])
                                ->all())
                            ->columns(3)
                            ->required()
                            ->live(),
                        Forms\Components\Toggle::make('is_featured_lecturer')
                            ->label('Featured lecturer on homepage')
                            ->helperText('Only one lecturer can be featured at a time. Turn this on for the lecturer you want highlighted on the landing page.')
                            ->visible(fn (Forms\Get $get) => static::hasLecturerRole($get('roles'))),
                    ]),
                Forms\Components\Section::make('Public lecturer profile')
                    ->schema([
                        Forms\Components\TextInput::make('lecturer_headline')
                            ->maxLength(120),
                        Forms\Components\TextInput::make('lecturer_specialties')
                            ->maxLength(255)
                            ->helperText('Comma-separated, for example: Python, Robotics, AI Fundamentals'),
                        Forms\Components\Textarea::make('lecturer_bio')
                            ->rows(5)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Forms\Get $get) => static::hasLecturerRole($get('roles')))
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_photo_path')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl(fn (User $record) => $record->profile_photo_url),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_featured_lecturer')
                    ->label('Featured')
                    ->boolean(),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('roles');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function hasLecturerRole(array|null $roleIds): bool
    {
        if (blank($roleIds)) {
            return false;
        }

        $lecturerRoleId = Role::query()->where('name', 'lecturer')->value('id');

        return $lecturerRoleId !== null && in_array((string) $lecturerRoleId, array_map('strval', $roleIds), true);
    }
}
