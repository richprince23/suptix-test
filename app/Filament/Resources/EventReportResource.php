<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventReportResource\Pages;
use App\Filament\Resources\EventReportResource\RelationManagers;
use App\Models\Event;
use App\Models\EventReport;
use App\Models\SystemAdmin;
use App\Notifications\NewEventReportNotification;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class EventReportResource extends Resource
{
    protected static ?string $model = EventReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Event Reports';

    protected static ?string $modelLabel = 'Event Report';

    protected static ?string $pluralModelLabel = 'Event Reports';

    protected static ?string $navigationGroup = 'Support';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Report Details')
                    ->schema([
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
                        // TextInput::make('event.title')
                        //     ->label('Event Name')
                        //     ->disabled()
                        //     ->dehydrated(false)
                        //     ->formatStateUsing(function ($record) {
                        //         return $record->event?->title ?? 'Event not found';
                        //     }),

                        Select::make('event_id')
                            ->label('Event')
                            ->options(function () {
                                $tenant = Filament::getTenant();
                                if (!$tenant) {
                                    return [];
                                }
                                return Event::where('organizer_id', $tenant->id)
                                    ->orderBy('start_date', 'desc')
                                    ->pluck('title', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->disabledOn('edit')
                            ->required(),

                        TextInput::make('event.start_date')
                            ->label('Event Date')
                            ->disabled()
                            ->dehydrated(false)
                            // ->disabledOn('edit')
                            ->formatStateUsing(function ($record) {
                                if ($record?->event != null)
                                return $record->event?->start_date ?
                                    \Carbon\Carbon::parse($record->event->start_date)->format('M j, Y') : 'N/A';
                            }),

                        TextInput::make('reporter_name')
                            ->label('Reporter Name')
                            // ->disabled()
                            ->disabledOn('edit')
                            ->dehydrated(false)
                            // ->default(function ($record) {
                            //     if ($record?->reporter_name != null)
                            //     return Auth::user()->name;
                            // })
                            ->formatStateUsing(function ($record) {
                                if ($record?->reporter_name != null)
                                return Auth::user()->name;
                            }),

                        TextInput::make('reporter_email')
                            ->label('Reporter Email')
                            // ->disabled()
                            ->disabledOn('edit')
                            ->dehydrated(false)
                            ->formatStateUsing(function ($record) {
                                if ($record?->reporter_email != null)
                                return $record->reporter_email ?: ($record->organizer?->email ?? 'N/A');
                            }),
                    ])
                    ->collapsible(),

                Section::make('Response & Notes')
                    ->schema([
                        Textarea::make('organizer_notes')
                            ->label('Organizer Notes')
                            ->rows(3)
                            ->helperText('Internal notes visible to organizers only'),
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
                    ->limit(30)
                    ->tooltip(function (EventReport $record): ?string {
                        return $record->event?->title ?? 'Event not found';
                    })
                    ->formatStateUsing(function (EventReport $record): string {
                        return $record->event?->title ?? 'Event not found';
                    }),

                TextColumn::make('report_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'bug' => 'Technical Bug',
                        'content_issue' => 'Content Issue',
                        'spam' => 'Spam',
                        'inappropriate' => 'Inappropriate',
                        'other' => 'Other',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'bug' => 'danger',
                        'content_issue' => 'warning',
                        'spam' => 'danger',
                        'inappropriate' => 'danger',
                        'other' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('subject')
                    ->searchable()
                    ->limit(40)
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
                    ->label('Escalated to Admin')
                    ->query(fn(Builder $query): Builder => $query->escalated()),

                Tables\Filters\Filter::make('unescalated')
                    ->label('Not Escalated')
                    ->query(fn(Builder $query): Builder => $query->unescalated()),
            ])->headerActions([
                    ExportAction::make()
                        ->label('Export Data'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Action::make('escalateToAdmin')
                    ->label('Escalate to Admin')
                    ->icon('heroicon-o-arrow-up')
                    ->color('warning')
                    ->visible(fn(EventReport $record): bool => !$record->admin_notified_at)
                    ->requiresConfirmation()
                    ->modalHeading('Escalate to System Admin')
                    ->modalDescription('This will notify the system admin about this report. Use this for issues you cannot resolve or need admin intervention.')
                    ->action(function (EventReport $record) {
                        try {
                            $systemAdmin = new SystemAdmin();
                            $systemAdmin->notify(new NewEventReportNotification($record->load('event')));

                            $record->markAdminNotified();

                            Notification::make()
                                ->title('Report escalated to system admin')
                                ->success()
                                ->send();

                            Log::info('Event report escalated to system admin', [
                                'event_report_id' => $record->id,
                                'event_id' => $record->event_id,
                                'escalated_by' => auth()->id(),
                            ]);

                        } catch (\Exception $e) {
                            Log::error('Failed to escalate report to admin', [
                                'error' => $e->getMessage(),
                                'event_report_id' => $record->id,
                            ]);

                            Notification::make()
                                ->title('Failed to escalate report')
                                ->danger()
                                ->send();
                        }
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
        $organizer = filament()->getTenant();

        return parent::getEloquentQuery()
            ->with(['event.organizer', 'organizer'])
            ->whereHas('event', function (Builder $query) use ($organizer) {
                $query->where('organizer_id', $organizer->id);
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEventReports::route('/'),
            'create' => Pages\CreateEventReport::route('/create'),
            'edit' => Pages\EditEventReport::route('/{record}/edit'),
        ];
    }
}
