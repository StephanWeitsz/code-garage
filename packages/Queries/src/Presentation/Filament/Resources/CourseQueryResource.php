<?php

namespace CodeGarage\Queries\Presentation\Filament\Resources;

use CodeGarage\Queries\Infrastructure\Persistence\Eloquent\Models\CourseQuery;
use CodeGarage\Queries\Presentation\Filament\Resources\CourseQueryResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourseQueryResource extends Resource
{
    protected static ?string $model = CourseQuery::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Learning';

    protected static ?string $navigationLabel = 'Course Queries';

    protected static ?string $modelLabel = 'course query';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Query')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('mobile')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('subject')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('course.title')
                            ->label('Course')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('audience')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Textarea::make('message')
                            ->rows(6)
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Admin follow-up')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'open' => 'Open',
                                'in_progress' => 'In progress',
                                'waiting_on_student' => 'Waiting on student',
                                'resolved' => 'Resolved',
                                'closed' => 'Closed',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\DateTimePicker::make('resolved_at')
                            ->seconds(false)
                            ->visible(fn (Forms\Get $get): bool => in_array($get('status'), ['resolved', 'closed'], true)),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject')
                    ->default('Course query')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('audience')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('visibility')
                    ->label('Visibility')
                    ->options([
                        'active' => 'Active only',
                        'closed' => 'Closed only',
                        'all' => 'All queries',
                    ])
                    ->default('active')
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? 'active') {
                            'closed' => $query->where('status', 'closed'),
                            'all' => $query,
                            default => $query->where('status', '!=', 'closed'),
                        };
                    }),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In progress',
                        'waiting_on_student' => 'Waiting on student',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ]),
                Tables\Filters\SelectFilter::make('audience')
                    ->options([
                        'prospective_student' => 'Prospective student',
                        'registered_student' => 'Registered student',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('emailClient')
                    ->label('Email')
                    ->icon('heroicon-o-envelope')
                    ->url(fn (CourseQuery $record): string => static::mailtoUrl($record))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->hasRole('admin') ?? false),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['course', 'student'])
            ->latest();

        $user = auth()->user();

        if ($user && $user->hasRole('lecturer') && ! $user->hasRole('admin')) {
            $query->whereHas('course', fn (Builder $courseQuery) => $courseQuery->where('lecturer_id', $user->id));
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourseQueries::route('/'),
            'edit' => Pages\EditCourseQuery::route('/{record}/edit'),
        ];
    }

    private static function mailtoUrl(CourseQuery $record): string
    {
        $subject = rawurlencode($record->subject ?: 'Code Garage course query');
        $body = rawurlencode("Hi {$record->name},\n\nThank you for your query about {$record->course?->title}.\n\n");

        return "mailto:{$record->email}?subject={$subject}&body={$body}";
    }
}
