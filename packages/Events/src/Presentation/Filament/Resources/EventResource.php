<?php

namespace CodeGarage\Events\Presentation\Filament\Resources;

use CodeGarage\Events\Infrastructure\Persistence\Eloquent\Models\Event;
use CodeGarage\Events\Presentation\Filament\Resources\EventResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Community';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Event details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Hidden::make('created_by')
                            ->default(fn () => auth()->id()),
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->options([
                                'coding_day' => 'Coding day',
                                'project_day' => 'Project day',
                                'workshop' => 'Workshop',
                                'graduation' => 'Graduation',
                            ])
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                'closed' => 'Closed',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\Textarea::make('summary')
                            ->rows(3)
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Schedule and venue')
                    ->columns(2)
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->seconds(false)
                            ->required(),
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->seconds(false)
                            ->after('starts_at'),
                        Forms\Components\TextInput::make('location')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('capacity')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10000),
                        Forms\Components\Toggle::make('is_online')
                            ->label('Online event')
                            ->inline(false)
                            ->live(),
                        Forms\Components\TextInput::make('meeting_url')
                            ->url()
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get): bool => (bool) $get('is_online')),
                    ]),
                Forms\Components\Section::make('Feedback and closure')
                    ->columns(2)
                    ->schema([
                        Forms\Components\DateTimePicker::make('published_at')
                            ->seconds(false),
                        Forms\Components\DateTimePicker::make('closed_at')
                            ->seconds(false)
                            ->visible(fn (Forms\Get $get): bool => $get('status') === 'closed'),
                        Forms\Components\Textarea::make('feedback_notes')
                            ->label('Event feedback')
                            ->rows(5)
                            ->helperText('Capture attendance notes, outcomes, improvements, or parent/student feedback.')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('internal_notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title())
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created by')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->sortable(),
                Tables\Columns\TextColumn::make('closed_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('visibility')
                    ->label('Visibility')
                    ->options([
                        'active' => 'Active only',
                        'closed' => 'Closed only',
                        'all' => 'All events',
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
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'closed' => 'Closed',
                    ]),
            ])
            ->actions([
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
            ->with('creator')
            ->latest('starts_at');

        $user = auth()->user();

        if ($user && $user->hasRole('lecturer') && ! $user->hasRole('admin')) {
            $query->where('created_by', $user->id);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
