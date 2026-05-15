<?php

namespace CodeGarage\Lessons\Presentation\Filament\Resources;

use App\Enums\CourseStatus;
use CodeGarage\Lessons\Presentation\Filament\Resources\LessonResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\CourseSection;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\Lesson;

class LessonResource extends Resource
{
    protected static ?string $model = Lesson::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationGroup = 'Learning';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                /*
                Forms\Components\Section::make('Student preview')
                    ->schema([
                        Forms\Components\Placeholder::make('student_preview_link')
                            ->hiddenLabel()
                            ->content(function (?Lesson $record): HtmlString {
                                if (! $record) {
                                    return new HtmlString('<span class="text-sm text-gray-500">Save the lesson before opening the student preview.</span>');
                                }

                                $record->loadMissing('course');

                                $url = route('lessons.show', [
                                    $record->course->slug,
                                    $record->slug,
                                    'preview_as_student' => 1,
                                ]);

                                
                                return new HtmlString(sprintf(
                                    '<a href="%s" target="_blank" rel="noopener noreferrer" class="fi-btn fi-btn-size-md inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">View lesson as student</a>',
                                    e($url),
                                ));
                                
                            }),
                    ]),
                */    
                Forms\Components\Select::make('course_id')
                    ->label('Course')
                    ->options(fn () => static::courseOptions())
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->live()
                    ->disabled(fn (string $operation): bool => $operation === 'create' && filled(request()->query('course_id')))
                    ->dehydrated()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('course_section_id', null))
                    ->required(),
                Forms\Components\Select::make('course_section_id')
                    ->label('Section')
                    ->placeholder('Select a section')
                    ->options(fn (Get $get) => static::sectionOptions($get('course_id')))
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->live()
                    ->disabled(fn (Get $get, string $operation): bool => blank($get('course_id')) || ($operation === 'create' && filled(request()->query('course_section_id'))))
                    ->dehydrated()
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('content')
                    ->required()
                    ->rows(16)
                    ->helperText('Use Markdown when the content type is Markdown. Headings, lists, links, and fenced code blocks will render for students.')
                    ->columnSpanFull(),
                Forms\Components\Select::make('content_type')
                    ->options([
                        'text' => 'Text',
                        'markdown' => 'Markdown',
                        'video' => 'Video',
                        'code' => 'Code',
                    ])
                    ->native(false)
                    ->required(),
                Forms\Components\TextInput::make('sequence')
                    ->numeric()
                    ->required(),
                Forms\Components\Toggle::make('is_preview'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('section.title')
                    ->label('Section')
                    ->searchable()
                    ->sortable()
                    ->weight('600'),
                Tables\Columns\TextColumn::make('sequence')
                    ->label('Lesson #')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('course.title')
                    ->label('Course')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('content_type'),
                Tables\Columns\IconColumn::make('is_preview')
                    ->label('Preview')
                    ->boolean(),
            ])
            ->filters([
                Filter::make('course_outline')
                    ->form([
                        Forms\Components\Select::make('course_id')
                            ->label('Course')
                            ->placeholder('Select a course')
                            ->options(fn () => static::courseOptions())
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('course_section_id', null)),
                        Forms\Components\Select::make('course_section_id')
                            ->label('Section')
                            ->placeholder('Select a section')
                            ->options(fn (Get $get) => static::sectionOptions($get('course_id')))
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled(fn (Get $get) => blank($get('course_id'))),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['course_id'] ?? null),
                                fn (Builder $query) => $query->where('course_id', $data['course_id'])
                            )
                            ->when(
                                filled($data['course_section_id'] ?? null),
                                fn (Builder $query) => $query->where('course_section_id', $data['course_section_id'])
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['course_id'] ?? null)) {
                            $indicators[] = 'Course: '.(static::courseOptions()[$data['course_id']] ?? 'Selected');
                        }

                        if (filled($data['course_section_id'] ?? null)) {
                            $indicators[] = 'Section: '.(static::sectionOptions($data['course_id'] ?? null)[$data['course_section_id']] ?? 'Selected');
                        }

                        return $indicators;
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(1)
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\Action::make('studentPreview')
                    ->label('Student preview')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Lesson $record): string => route('lessons.show', [
                        $record->course->slug,
                        $record->slug,
                        'preview_as_student' => 1,
                    ]))
                    ->openUrlInNewTab(),
                Tables\Actions\ViewAction::make()
                    ->url(fn (Lesson $record): string => static::getUrl('view', [
                        'record' => $record,
                        ...request()->query(),
                    ])),
                Tables\Actions\EditAction::make()
                    ->url(fn (Lesson $record): string => static::getUrl('edit', [
                        'record' => $record,
                        ...request()->query(),
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Select a course section or create your first lesson')
            ->emptyStateDescription('Organize lessons inside sections so each course can flow through clear modules.');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['course', 'section'])
            ->orderBy('course_section_id')
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
            'index' => Pages\ListLessons::route('/'),
            'create' => Pages\CreateLesson::route('/create'),
            'view' => Pages\ViewLesson::route('/{record}'),
            'edit' => Pages\EditLesson::route('/{record}/edit'),
        ];
    }

    protected static function courseOptions(): array
    {
        return static::courseQuery()
            ->pluck('title', 'id')
            ->all();
    }

    protected static function sectionOptions($courseId): array
    {
        if (blank($courseId)) {
            return [];
        }

        return static::sectionQuery()
            ->where('course_id', $courseId)
            ->orderBy('sequence')
            ->get()
            ->mapWithKeys(fn (CourseSection $section) => [$section->id => sprintf('%02d. %s', $section->sequence, $section->title)])
            ->all();
    }

    protected static function courseQuery(): Builder
    {
        $query = Course::query()->orderBy('title');
        $user = auth()->user();

        if ($user && $user->hasRole('lecturer') && ! $user->hasRole('admin')) {
            $query->where('lecturer_id', $user->id);
        }

        return $query->whereIn('status', CourseStatus::authoringStatuses());
    }

    protected static function sectionQuery(): Builder
    {
        $query = CourseSection::query()->with('course');
        $user = auth()->user();

        if ($user && $user->hasRole('lecturer') && ! $user->hasRole('admin')) {
            $query->whereHas('course', fn (Builder $courseQuery) => $courseQuery->where('lecturer_id', $user->id));
        }

        return $query;
    }
}


