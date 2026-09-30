<?php

namespace App\Filament\SystemAdmin\Resources;

use App\Filament\SystemAdmin\Resources\EventReportResource\Pages;
use App\Models\EventReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class EventReportResource extends Resource
{
    protected static ?string $model = EventReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationLabel = 'Event Reports';

    protected static ?string $modelLabel = 'Event Report';

    protected static ?string $pluralModelLabel = 'Event Reports';

    protected static ?string $navigationGroup = 'Support & Security';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Report Details')
                    ->schema([
                        TextInput::make('event.name')
                            ->label('Event')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('subject')
                            ->required()
                            ->maxLength(255),

                        Select::make('report_type')
                            ->label('Issue Type')
                            ->options([
                                'bug' => 'Technical Bug',
                                'content_issue' => 'Content Issue',
                                'spam' => 'Spam',
                                'inappropriate' => 'Inappropriate Content',
                                'other' => 'Other',
                            ])
                            ->required(),

                        Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->required(),

                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'resolved' => 'Resolved',
                                'closed' => 'Closed',
                            ])
                            ->required(),

                        Textarea::make('description')
                            ->required()
                            ->rows(4)
                            ->maxLength(2000),
                    ]),

                Section::make('Event & Reporter Information')
                    ->schema([
                        TextInput::make('event.title')
                            ->label('Event Name')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(function ($record) {
                                return $record->event?->title ?? 'Event not found';
                            }),

                        TextInput::make('event.start_date')
                            ->label('Event Date')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(function ($record) {
                                return $record->event?->start_date ?
                                    \Carbon\Carbon::parse($record->event->start_date)->format('M j, Y') : 'N/A';
                            }),

                        TextInput::make('event.organizer.name')
                            ->label('Event Organizer')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(function ($record) {
                                return $record->event?->organizer?->name ?? 'Organizer not found';
                            }),

                        TextInput::make('reporter_name')
                            ->label('Reporter Name')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(function ($record) {
                                return $record->reporter_name ?: ($record->organizer?->name ?? 'Anonymous');
                            }),

                        TextInput::make('reporter_email')
                            ->label('Reporter Email')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(function ($record) {
                                return $record->reporter_email ?: ($record->organizer?->email ?? 'N/A');
                            }),
                    ])
                    ->collapsible(),

                Section::make('Internal Notes')
                    ->schema([
                        Textarea::make('organizer_notes')
                            ->label('Organizer Notes')
                            ->rows(3)
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Notes from the organizer (read-only)'),

                        Textarea::make('admin_notes')
                            ->label('Admin Notes')
                            ->rows(3)
                            ->helperText('Internal admin notes'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event.title')
                    ->label('Event')
                    ->searchable()
                    ->sortable()
                    ->limit(25)
                    ->tooltip(function (EventReport $record): ?string {
                        return $record->event?->title ?? 'Event not found';
                    })
                    ->formatStateUsing(function (EventReport $record): string {
                        return $record->event?->title ?? 'Event not found';
                    }),

                TextColumn::make('event.organizer.name')
                    ->label('Organizer')
                    ->searchable()
                    ->sortable()
                    ->limit(20)
                    ->formatStateUsing(function (EventReport $record): string {
                        return $record->event?->organizer?->name ?? 'Organizer not found';
                    }),

                TextColumn::make('report_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'bug' => 'Technical Bug',
                        'content_issue' => 'Content Issue',
                        'spam' => 'Spam',
                        'inappropriate' => 'Inappropriate',
                        'other' => 'Other',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'bug' => 'danger',
                        'content_issue' => 'warning',
                        'spam' => 'danger',
                        'inappropriate' => 'danger',
                        'other' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('subject')
                    ->searchable()
                    ->limit(30)
                    ->sortable(),

                BadgeColumn::make('priority')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'danger' => 'urgent',
                    ]),

                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'in_progress',
                        'success' => 'resolved',
                        'gray' => 'closed',
                    ]),

                TextColumn::make('reporter')
                    ->label('Reporter')
                    ->getStateUsing(function (EventReport $record) {
                        // If reported by an organizer, show organizer info
                        if ($record->organizer) {
                            return $record->organizer->name . ' (Organizer)';
                        }
                        // Otherwise show reporter name or Anonymous
                        return $record->reporter_name ?? 'Anonymous';
                    }),

                TextColumn::make('admin_notified_at')
                    ->label('Escalated')
                    ->since()
                    ->placeholder('Not escalated')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Reported')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event')
                    ->relationship('event', 'title')
                    ->label('Event')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('organizer')
                    ->relationship('event.organizer', 'name')
                    ->label('Event Organizer')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ]),
                SelectFilter::make('report_type')
                    ->label('Issue Type')
                    ->options([
                        'bug' => 'Technical Bug',
                        'content_issue' => 'Content Issue',
                        'spam' => 'Spam',
                        'inappropriate' => 'Inappropriate Content',
                        'other' => 'Other',
                    ]),

                SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),

                Tables\Filters\Filter::make('escalated')
                    ->label('Escalated Reports')
                    ->query(fn (Builder $query): Builder => $query->escalated()),

                Tables\Filters\Filter::make('public_reports')
                    ->label('Public Reports')
                    ->query(fn (Builder $query): Builder => $query->whereNull('organizer_id')),

                Tables\Filters\Filter::make('organizer_reports')
                    ->label('Organizer Reports')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('organizer_id')),
            ])->headerActions([
                ExportAction::make()
                    ->label('Export Data'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Action::make('markResolved')
                    ->label('Mark Resolved')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (EventReport $record): bool => $record->status !== 'resolved')
                    ->requiresConfirmation()
                    ->action(function (EventReport $record) {
                        $record->markAsResolved();

                        Notification::make()
                            ->title('Report marked as resolved')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['event.organizer', 'organizer']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEventReports::route('/'),
            'create' => Pages\CreateEventReport::route('/create'),
            'view' => Pages\ViewEventReport::route('/{record}'),
            'edit' => Pages\EditEventReport::route('/{record}/edit'),
        ];
    }
}
