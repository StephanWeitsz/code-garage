<?php

namespace CodeGarage\DevelopmentRequests\Presentation\Filament\Resources;

use CodeGarage\DevelopmentRequests\Infrastructure\Persistence\Eloquent\Models\DevelopmentRequest;
use CodeGarage\DevelopmentRequests\Presentation\Filament\Resources\DevelopmentRequestResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DevelopmentRequestResource extends Resource
{
    protected static ?string $model = DevelopmentRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Services';

    protected static ?string $navigationLabel = 'Development Requests';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Client')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('client_name')->required()->maxLength(255),
                        Forms\Components\TextInput::make('client_email')->email()->required()->maxLength(255),
                        Forms\Components\TextInput::make('client_phone')->maxLength(50),
                        Forms\Components\TextInput::make('company_name')->maxLength(255),
                        Forms\Components\Select::make('preferred_contact_method')
                            ->options([
                                'email' => 'Email',
                                'phone' => 'Phone',
                                'whatsapp' => 'WhatsApp',
                            ])
                            ->required(),
                    ]),
                Forms\Components\Section::make('Requirement')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('project_name')->required()->maxLength(255),
                        Forms\Components\TextInput::make('project_type')->required()->maxLength(120),
                        Forms\Components\Textarea::make('project_goal')->rows(5)->required()->columnSpanFull(),
                        Forms\Components\Textarea::make('target_users')->rows(3),
                        Forms\Components\Textarea::make('current_process')->rows(3),
                        Forms\Components\TagsInput::make('must_have_features')->placeholder('Add a required feature'),
                        Forms\Components\TagsInput::make('nice_to_have_features')->placeholder('Add an optional feature'),
                        Forms\Components\Textarea::make('integrations')->rows(3),
                        Forms\Components\Textarea::make('content_and_data')->rows(3),
                        Forms\Components\TextInput::make('timeline')->maxLength(120),
                        Forms\Components\TextInput::make('budget_range')->maxLength(120),
                        Forms\Components\Textarea::make('success_measure')->rows(3),
                        Forms\Components\Textarea::make('additional_context')->rows(4),
                    ]),
                Forms\Components\Section::make('Admin quote')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'new' => 'New',
                                'reviewing' => 'Reviewing',
                                'waiting_on_client' => 'Waiting on client',
                                'quoted' => 'Quoted',
                                'accepted' => 'Accepted',
                                'declined' => 'Declined',
                                'closed' => 'Closed',
                            ])
                            ->required(),
                        Forms\Components\Select::make('quote_status')
                            ->options([
                                'not_started' => 'Not started',
                                'estimating' => 'Estimating',
                                'ready_to_send' => 'Ready to send',
                                'sent' => 'Sent',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('quote_currency')
                            ->maxLength(3)
                            ->required()
                            ->dehydrateStateUsing(fn (?string $state): ?string => $state ? strtoupper($state) : null),
                        Forms\Components\TextInput::make('quote_amount_min')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('quote_amount_max')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\DateTimePicker::make('contacted_at')->seconds(false),
                        Forms\Components\DateTimePicker::make('quoted_at')->seconds(false),
                        Forms\Components\Textarea::make('costing_notes')->rows(5)->columnSpanFull(),
                        Forms\Components\Textarea::make('admin_response')
                            ->label('Email response draft')
                            ->rows(5)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('internal_notes')->rows(5)->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('project_type')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('quote_status')
                    ->label('Quote')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quote_amount_min')
                    ->label('Estimate from')
                    ->money(fn (DevelopmentRequest $record): string => $record->quote_currency ?: 'ZAR')
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
                        'all' => 'All requests',
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
                        'new' => 'New',
                        'reviewing' => 'Reviewing',
                        'waiting_on_client' => 'Waiting on client',
                        'quoted' => 'Quoted',
                        'accepted' => 'Accepted',
                        'declined' => 'Declined',
                        'closed' => 'Closed',
                    ]),
                Tables\Filters\SelectFilter::make('quote_status')
                    ->options([
                        'not_started' => 'Not started',
                        'estimating' => 'Estimating',
                        'ready_to_send' => 'Ready to send',
                        'sent' => 'Sent',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('emailClient')
                    ->label('Email')
                    ->icon('heroicon-o-envelope')
                    ->url(fn (DevelopmentRequest $record): string => static::mailtoUrl($record))
                    ->openUrlInNewTab(),
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
        return parent::getEloquentQuery()
            ->latest();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDevelopmentRequests::route('/'),
            'edit' => Pages\EditDevelopmentRequest::route('/{record}/edit'),
        ];
    }

    private static function mailtoUrl(DevelopmentRequest $record): string
    {
        $subject = rawurlencode('Code Garage quote: '.$record->project_name);
        $body = rawurlencode($record->admin_response ?: "Hi {$record->client_name},\n\nThank you for sending your development requirements for {$record->project_name}.\n\n");

        return "mailto:{$record->client_email}?subject={$subject}&body={$body}";
    }
}
