<?php

namespace CodeGarage\Lessons\Presentation\Filament\Resources;

use App\Enums\CourseStatus;
use CodeGarage\Lessons\Presentation\Filament\Resources\CourseSectionResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\CourseSection;

class CourseSectionResource extends Resource
{
    protected static ?string $model = CourseSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Learning';

    protected static ?string $navigationLabel = 'Course sections';

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
                    ->disabled(fn (string $operation): bool => $operation === 'create' && filled(request()->query('course_id')))
                    ->dehydrated()
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('sequence')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sequence')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('600'),
                Tables\Columns\TextColumn::make('course.title')
                    ->label('Course')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lessons_count')
                    ->counts('lessons')
                    ->label('Lessons'),
            ])
            ->filters([
                Filter::make('course_scope')
                    ->form([
                        Forms\Components\Select::make('course_id')
                            ->label('Course')
                            ->placeholder('Select a course')
                            ->options(fn () => static::courseOptions())
                            ->native(false)
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['course_id'] ?? null),
                        fn (Builder $query) => $query->where('course_id', $data['course_id'])
                    ))
                    ->indicateUsing(fn (array $data): array => filled($data['course_id'] ?? null)
                        ? ['Course: '.(static::courseOptions()[$data['course_id']] ?? 'Selected')]
                        : []),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(1)
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn (CourseSection $record): string => static::getUrl('edit', [
                        'record' => $record,
                        ...request()->query(),
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Select a course or create your first section')
            ->emptyStateDescription('Organize each course into sections before you add the lessons inside them.');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with('course')
            ->orderBy('course_id')
            ->orderBy('sequence');
        $user = auth()->user();

        if ($user && $user->hasRole('lecturer') && ! $user->hasRole('admin')) {
            $query->whereHas('course', fn (Builder $courseQuery) => $courseQuery->where('lecturer_id', $user->id));
        }

        return $query->whereHas('course', fn (Builder $courseQuery) => $courseQuery->whereIn('status', CourseStatus::authoringStatuses()));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourseSections::route('/'),
            'create' => Pages\CreateCourseSection::route('/create'),
            'edit' => Pages\EditCourseSection::route('/{record}/edit'),
        ];
    }

    protected static function courseOptions(): array
    {
        $query = Course::query()->orderBy('title');
        $user = auth()->user();

        if ($user && $user->hasRole('lecturer') && ! $user->hasRole('admin')) {
            $query->where('lecturer_id', $user->id);
        }

        return $query
            ->whereIn('status', CourseStatus::authoringStatuses())
            ->pluck('title', 'id')
            ->all();
    }
}
