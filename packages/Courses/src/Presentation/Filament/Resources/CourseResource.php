<?php

namespace CodeGarage\Courses\Presentation\Filament\Resources;

use CodeGarage\Courses\Presentation\Filament\Resources\CourseResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Learning';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('lecturer_id')
                    ->label('Lecturer')
                    ->options(fn () => User::role('lecturer')->orderBy('name')->pluck('name', 'id'))
                    ->default(fn () => auth()->id())
                    ->required()
                    ->visible(fn () => auth()->user()?->hasRole('admin')),
                Forms\Components\Hidden::make('lecturer_id')
                    ->default(fn () => auth()->id())
                    ->visible(fn () => ! auth()->user()?->hasRole('admin')),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('cover_image')
                    ->label('Course cover image')
                    ->image()
                    ->directory('course-covers')
                    ->disk('public')
                    ->visibility('public')
                    ->imageEditor(false)
                    ->fetchFileInformation(false)
                    ->maxSize(4096)
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->helperText(fn () => static::coverImageUploadWarning())
                    ->columnSpanFull(),
                Forms\Components\Placeholder::make('cover_image_preview')
                    ->label('Course cover image')
                    ->content(function (?Course $record): HtmlString {
                        $path = (string) ($record?->cover_image ?? '');
                        $url = str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
                            ? $path
                            : '/storage/'.ltrim($path, '/');

                        return new HtmlString('<img src="'.$url.'" alt="Course cover image" style="max-width: 100%; max-height: 260px; border-radius: 0.75rem; object-fit: cover;" />');
                    })
                    ->visible(fn (string $operation, ?Course $record): bool => $operation !== 'create' && filled($record?->cover_image))
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('cover_image_upload')
                    ->label('Replace cover image')
                    ->image()
                    ->directory('course-covers')
                    ->disk('public')
                    ->visibility('public')
                    ->imageEditor(false)
                    ->fetchFileInformation(false)
                    ->maxSize(4096)
                    ->visible(fn (string $operation): bool => $operation === 'edit')
                    ->helperText(fn () => 'Upload a new file to replace the current cover image. '.static::coverImageUploadWarning())
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('remove_cover_image')
                    ->label('Remove current cover image')
                    ->visible(fn (string $operation, ?Course $record): bool => $operation === 'edit' && filled($record?->cover_image))
                    ->helperText('Enable this, then save, to remove the existing image.'),
                Forms\Components\Repeater::make('knowledge_prerequisites')
                    ->label('Knowledge prerequisites')
                    ->simple(
                        Forms\Components\TextInput::make('value')
                            ->label('Requirement')
                            ->required()
                            ->maxLength(255)
                    )
                    ->default([])
                    ->columnSpanFull()
                    ->helperText('Examples: Basic computer literacy, Intro to Scratch, Algebra basics.'),
                Forms\Components\Repeater::make('equipment_requirements')
                    ->label('Equipment')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(120),
                        Forms\Components\TextInput::make('url')
                            ->label('Purchase link (optional)')
                            ->url()
                            ->maxLength(2048),
                        Forms\Components\Textarea::make('notes')
                            ->rows(2)
                            ->maxLength(500),
                    ])
                    ->default([])
                    ->columnSpanFull(),
                Forms\Components\Select::make('difficulty_level')
                    ->options([
                        'beginner' => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'advanced' => 'Advanced',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('category')
                    ->required()
                    ->maxLength(120),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'build' => 'Build',
                        'published' => 'Published',
                    ])
                    ->required()
                    ->live(),
                Forms\Components\Select::make('pricing_type')
                    ->options([
                        'free' => 'Free',
                        'once_off' => 'Once-off',
                        'per_lesson' => 'Per lesson',
                        'hourly' => 'Hourly',
                    ])
                    ->default('free')
                    ->required()
                    ->live(),
                Forms\Components\TextInput::make('pricing_amount')
                    ->label('Price amount')
                    ->numeric()
                    ->minValue(0)
                    ->required(fn (Forms\Get $get): bool => $get('pricing_type') !== 'free')
                    ->visible(fn (Forms\Get $get): bool => $get('pricing_type') !== 'free'),
                Forms\Components\TextInput::make('pricing_currency')
                    ->label('Currency')
                    ->default('ZAR')
                    ->required()
                    ->minLength(3)
                    ->maxLength(3)
                    ->dehydrateStateUsing(fn (?string $state): ?string => $state ? strtoupper($state) : null),
                Forms\Components\TextInput::make('default_meeting_url')
                    ->label('Default live session link (optional)')
                    ->url()
                    ->maxLength(2048)
                    ->placeholder('https://meet.google.com/... or https://teams.microsoft.com/...'),
                Forms\Components\DateTimePicker::make('published_at')
                    ->visible(fn (Forms\Get $get) => $get('status') === 'published'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lecturer.name')
                    ->label('Lecturer')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('difficulty_level')
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('pricing_type')
                    ->label('Pricing')
                    ->formatStateUsing(fn (string $state) => str($state)->replace('_', ' ')->title())
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'build' => 'Build',
                        'published' => 'Published',
                    ])
                    ->default('published'),
                Tables\Filters\SelectFilter::make('difficulty_level')
                    ->label('Level')
                    ->options([
                        'beginner' => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'advanced' => 'Advanced',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
        $query = parent::getEloquentQuery()->with('lecturer');
        $user = auth()->user();

        if ($user && $user->hasRole('lecturer') && ! $user->hasRole('admin')) {
            $query->where('lecturer_id', $user->id);
        }

        return $query;
    }

    public static function getRecordRouteKeyName(): ?string
    {
        return 'id';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'view' => Pages\ViewCourse::route('/{record}'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }

    protected static function coverImageUploadWarning(): string
    {
        $uploadMax = (string) ini_get('upload_max_filesize');
        $postMax = (string) ini_get('post_max_size');
        $effectiveBytes = min(
            static::iniSizeToBytes($uploadMax),
            static::iniSizeToBytes($postMax),
        );

        return sprintf(
            'Server upload limit: %s (effective max: %s). If uploads fail, increase php.ini upload_max_filesize and post_max_size.',
            $uploadMax,
            static::formatBytes($effectiveBytes),
        );
    }

    protected static function iniSizeToBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $number = (float) $value;
        $unit = strtolower(substr($value, -1));

        return match ($unit) {
            'g' => (int) ($number * 1024 * 1024 * 1024),
            'm' => (int) ($number * 1024 * 1024),
            'k' => (int) ($number * 1024),
            default => (int) $number,
        };
    }

    protected static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024 * 1024) {
            return number_format($bytes / (1024 * 1024 * 1024), 1).' GB';
        }

        if ($bytes >= 1024 * 1024) {
            return number_format($bytes / (1024 * 1024), 1).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }
}
