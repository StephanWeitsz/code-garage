<?php

namespace CodeGarage\Assignments\Presentation\Filament\Resources;

use CodeGarage\Assignments\Presentation\Filament\Resources\AssignmentResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use CodeGarage\Assignments\Infrastructure\Persistence\Eloquent\Models\Assignment;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\Lesson;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Learning';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('course_id')
                    ->label('Course')
                    ->options(fn () => static::courseOptions())
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('lesson_id', null))
                    ->required(),
                Forms\Components\Select::make('lesson_id')
                    ->label('Linked lesson (optional)')
                    ->placeholder('Select a lesson')
                    ->options(fn (Get $get) => static::lessonOptions($get('course_id')))
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->disabled(fn (Get $get): bool => blank($get('course_id'))),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('instructions')
                    ->rows(8)
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('due_at')
                    ->seconds(false),
                Forms\Components\TextInput::make('due_days_after_completion')
                    ->label('Days after lesson completion')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(60)
                    ->helperText('Set a relative deadline for each student once they mark this lesson complete.'),
                Forms\Components\Toggle::make('requires_completion_before_lesson_complete')
                    ->label('Must be submitted before lesson can be completed')
                    ->default(false)
                    ->inline(false),
                Forms\Components\TextInput::make('max_points')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(1000)
                    ->default(100)
                    ->required(),
                Forms\Components\Toggle::make('is_published')
                    ->label('Published')
                    ->default(true)
                    ->inline(false),
                Forms\Components\Hidden::make('author_id')
                    ->default(fn () => auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lesson.title')
                    ->label('Lesson')
                    ->placeholder('No lesson linked')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('due_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('due_days_after_completion')
                    ->label('Relative due')
                    ->formatStateUsing(fn ($state): string => filled($state) ? $state.' days' : '-')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('requires_completion_before_lesson_complete')
                    ->label('Blocks completion')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('is_published')
                    ->label('Status')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Published' : 'Draft')
                    ->colors([
                        'success' => true,
                        'gray' => false,
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course_id')
                    ->label('Course')
                    ->options(fn () => static::courseOptions()),
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published'),
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
        $query = parent::getEloquentQuery()
            ->with(['course', 'lesson'])
            ->orderByDesc('created_at');

        $user = auth()->user();

        if ($user && $user->hasRole('lecturer') && ! $user->hasRole('admin')) {
            $query->whereHas('course', fn (Builder $courseQuery) => $courseQuery->where('lecturer_id', $user->id));
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssignments::route('/'),
            'create' => Pages\CreateAssignment::route('/create'),
            'edit' => Pages\EditAssignment::route('/{record}/edit'),
        ];
    }

    protected static function courseOptions(): array
    {
        $query = Course::query()->orderBy('title');
        $user = auth()->user();

        if ($user && $user->hasRole('lecturer') && ! $user->hasRole('admin')) {
            $query->where('lecturer_id', $user->id);
        }

        return $query->pluck('title', 'id')->all();
    }

    protected static function lessonOptions($courseId): array
    {
        if (blank($courseId)) {
            return [];
        }

        return Lesson::query()
            ->where('course_id', $courseId)
            ->orderBy('sequence')
            ->get()
            ->mapWithKeys(fn (Lesson $lesson) => [$lesson->id => sprintf('%02d. %s', $lesson->sequence, $lesson->title)])
            ->all();
    }
}
