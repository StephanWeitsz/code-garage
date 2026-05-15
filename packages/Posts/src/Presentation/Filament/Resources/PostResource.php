<?php

namespace CodeGarage\Posts\Presentation\Filament\Resources;

use CodeGarage\Posts\Infrastructure\Persistence\Eloquent\Models\Post;
use CodeGarage\Posts\Presentation\Filament\Resources\PostResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Community';

    protected static ?string $navigationLabel = 'Discussions & Ads';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Post')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Hidden::make('author_id')
                            ->default(fn () => auth()->id()),
                        Forms\Components\Select::make('type')
                            ->options([
                                'discussion' => 'Discussion',
                                'announcement' => 'Announcement',
                                'ad' => 'Ad',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('status')
                            ->options(fn (Forms\Get $get): array => $get('type') === 'ad'
                                ? ['active' => 'Active', 'inactive' => 'Inactive']
                                : ['published' => 'Published', 'closed' => 'Closed', 'archived' => 'Archived'])
                            ->required()
                            ->default(fn (Forms\Get $get): string => $get('type') === 'ad' ? 'active' : 'published'),
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(180)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('body')
                            ->label(fn (Forms\Get $get): string => $get('type') === 'ad' ? 'Short description' : 'Message')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Ad image')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->disk('public')
                            ->directory('ads')
                            ->imageEditor()
                            ->visible(fn (Forms\Get $get): bool => $get('type') === 'ad')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('cta_label')
                            ->label('CTA label')
                            ->maxLength(80)
                            ->visible(fn (Forms\Get $get): bool => $get('type') === 'ad'),
                        Forms\Components\TextInput::make('cta_url')
                            ->label('CTA URL')
                            ->url()
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get): bool => $get('type') === 'ad'),
                    ]),
                Forms\Components\Section::make('Targeting and schedule')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('course_id')
                            ->relationship('course', 'title')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('lesson_id')
                            ->relationship('lesson', 'title')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Forms\Get $get): bool => $get('type') === 'discussion'),
                        Forms\Components\Toggle::make('is_pinned')
                            ->label('Feature at top')
                            ->inline(false),
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->seconds(false)
                            ->visible(fn (Forms\Get $get): bool => $get('type') === 'ad'),
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->seconds(false)
                            ->after('starts_at')
                            ->visible(fn (Forms\Get $get): bool => $get('type') === 'ad'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk('public')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title())
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_pinned')
                    ->label('Featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('author.name')
                    ->label('Author')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'discussion' => 'Discussion',
                        'announcement' => 'Announcement',
                        'ad' => 'Ad',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'published' => 'Published',
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'closed' => 'Closed',
                        'archived' => 'Archived',
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
            ->with(['author', 'course', 'lesson'])
            ->latest();

        $user = auth()->user();

        if ($user && $user->hasRole('lecturer') && ! $user->hasRole('admin')) {
            $query->where(function (Builder $inner) use ($user) {
                $inner->where('author_id', $user->id)
                    ->orWhereHas('course', fn (Builder $courseQuery) => $courseQuery->where('lecturer_id', $user->id));
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
