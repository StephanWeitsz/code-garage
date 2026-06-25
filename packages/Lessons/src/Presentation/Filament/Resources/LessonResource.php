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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
                Forms\Components\Select::make('content_type')
                    ->options([
                        'text' => 'Text',
                        'markdown' => 'Markdown',
                        'video' => 'Video',
                        'code' => 'Code',
                        'image' => 'Image gallery',
                    ])
                    ->native(false)
                    ->live()
                    ->required(),
                Forms\Components\Textarea::make('content')
                    ->default('')
                    ->required(fn (Get $get): bool => $get('content_type') !== 'image')
                    ->dehydrateStateUsing(fn (?string $state): string => $state ?? '')
                    ->rows(16)
                    ->helperText('Use Markdown when the content type is Markdown. Paste image snippets from the lesson images panel where you want them to appear.')
                    ->columnSpanFull(),
                Forms\Components\Section::make('Lesson images')
                    ->description('Upload images for this lesson. Markdown lessons can use these files inline; image gallery lessons display the uploaded images as the lesson content.')
                    ->schema([
                        Forms\Components\FileUpload::make('lesson_images')
                            ->label('Images')
                            ->disk('public')
                            ->directory(fn (Get $get, ?Lesson $record): string => static::lessonImageDirectory($get('course_id'), $get('title'), $record))
                            ->image()
                            ->multiple()
                            ->live()
                            ->reorderable()
                            ->openable()
                            ->downloadable()
                            ->preserveFilenames()
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'])
                            ->helperText('Files are stored under the course and lesson folder on the public disk.'),
                        Forms\Components\Placeholder::make('lesson_image_markdown')
                            ->label('Markdown snippets')
                            ->content(fn (Get $get): HtmlString => static::lessonImageMarkdownExamples($get('lesson_images'))),
                    ])
                    ->columnSpanFull(),
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


    protected static function lessonImageDirectory($courseId, ?string $title, ?Lesson $record): string
    {
        $course = $record?->course ?? (filled($courseId) ? Course::query()->find($courseId) : null);
        $courseSlug = $course?->slug ?: 'course-'.$courseId;
        $lessonSlug = $record?->slug ?: Str::slug($title ?: 'new-lesson');

        return sprintf('courses/%s/lessons/%s/images', $courseSlug, $lessonSlug ?: 'new-lesson');
    }

    protected static function lessonImageMarkdownExamples($images): HtmlString
    {
        $images = collect(is_array($images) ? $images : [])
            ->filter()
            ->values();

        if ($images->isEmpty()) {
            return new HtmlString('<span class="text-sm text-gray-500">Upload lesson images, then copy the generated Markdown into the lesson content.</span>');
        }

        $items = $images->map(function (string $path): string {
            $url = Storage::disk('public')->url($path);
            $name = pathinfo($path, PATHINFO_FILENAME) ?: 'lesson image';
            $alt = Str::headline(str_replace(['-', '_'], ' ', $name));
            $markdown = sprintf('![%s](%s)', $alt, $url);

            return sprintf(
                '<li class="space-y-1"><img src="%s" alt="%s" class="h-20 w-auto rounded border border-gray-200 object-contain"><code class="block whitespace-pre-wrap rounded bg-gray-100 px-2 py-1 text-xs text-gray-800">%s</code></li>',
                e($url),
                e($alt),
                e($markdown),
            );
        })->implode('');

        return new HtmlString('<ul class="space-y-3">'.$items.'</ul>');
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


