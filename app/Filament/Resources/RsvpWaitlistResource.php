<?php

namespace App\Filament\Resources;

use App\Exceptions\InsufficientCreditsException;
use App\Filament\Resources\RsvpWaitlistResource\Pages;
use App\Mail\WaitlistApprovalMail;
use App\Models\Event;
use App\Models\RsvpWaitlist;
use App\Services\EventCreditService;
use Filament\Facades\Filament;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RsvpWaitlistResource extends Resource
{
    protected static ?string $model = RsvpWaitlist::class;

    protected static ?string $navigationGroup = 'Event Management';

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationLabel = 'Waitlist';

    protected static ?string $modelLabel = 'Waitlist Entry';

    protected static ?string $pluralModelLabel = 'Waitlist';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('organizer_id', Filament::getTenant()->id)
            ->orderBy('position');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.title')
                    ->label('Event')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('guest_name')
                    ->label('Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('masked_email')
                    ->label('Email')
                    ->state(fn (RsvpWaitlist $record) => $record->maskedEmail()),
                Tables\Columns\TextColumn::make('masked_phone')
                    ->label('Phone')
                    ->state(fn (RsvpWaitlist $record) => $record->maskedPhone() ?? '—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('position')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined Waitlist')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('position')
            ->filters([
                SelectFilter::make('event_id')
                    ->label('Event')
                    ->options(function () {
                        $organizer = Filament::getTenant();

                        return Event::where('organizer_id', $organizer->id)
                            ->where('is_rsvp', true)
                            ->orderBy('start_date', 'desc')
                            ->pluck('title', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (RsvpWaitlist $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Waitlist Entry')
                    ->modalDescription(fn (RsvpWaitlist $record) => "Approve {$record->guest_name} ({$record->email}) for this event? This will consume one attendee slot and send them a confirmation email.")
                    ->action(function (RsvpWaitlist $record) {
                        static::handleApprove($record);
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (RsvpWaitlist $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Waitlist Entry')
                    ->modalDescription(fn (RsvpWaitlist $record) => "Reject {$record->guest_name} from the waitlist?")
                    ->action(function (RsvpWaitlist $record) {
                        $record->update([
                            'status' => 'rejected',
                            'actioned_at' => now(),
                            'actioned_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Entry rejected')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $approved = 0;
                            $failed = 0;

                            foreach ($records->where('status', 'pending') as $record) {
                                try {
                                    static::handleApprove($record);
                                    $approved++;
                                } catch (\Exception) {
                                    $failed++;
                                }
                            }

                            if ($approved > 0) {
                                Notification::make()
                                    ->title("{$approved} " . str('entry')->plural($approved) . ' approved')
                                    ->success()
                                    ->send();
                            }

                            if ($failed > 0) {
                                Notification::make()
                                    ->title("{$failed} " . str('entry')->plural($failed) . ' could not be approved — no slots available')
                                    ->warning()
                                    ->send();
                            }
                        }),
                    BulkAction::make('reject_selected')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->where('status', 'pending')->each(function (RsvpWaitlist $record) {
                                $record->update([
                                    'status' => 'rejected',
                                    'actioned_at' => now(),
                                    'actioned_by' => auth()->id(),
                                ]);
                            });

                            Notification::make()
                                ->title('Selected entries rejected')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Event Information')
                ->schema([
                    TextEntry::make('event.title')->label('Event'),
                    TextEntry::make('event.start_date')->label('Date')->date(),
                    TextEntry::make('event.start_time')->label('Time')->time(),
                ])
                ->columns(3),

            Section::make('Guest Information')
                ->schema([
                    TextEntry::make('guest_name')->label('Full Name'),
                    TextEntry::make('email')->label('Email Address')->copyable(),
                    TextEntry::make('phone')->label('Phone Number')->copyable()->placeholder('Not provided'),
                    TextEntry::make('referral_source')->label('Referral Source')->badge()->color('gray')->placeholder('Not provided'),
                ])
                ->columns(2),

            Section::make('Notes')
                ->schema([
                    TextEntry::make('notes')->label('Special Notes')->placeholder('No notes provided'),
                ])
                ->visible(fn (RsvpWaitlist $record) => ! empty($record->notes)),

            Section::make('Waitlist Details')
                ->schema([
                    TextEntry::make('position')->label('Queue Position'),
                    TextEntry::make('status')->badge()->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                    TextEntry::make('waitlist_code')->label('Waitlist Code')->badge()->color('primary'),
                    TextEntry::make('actioned_at')->label('Actioned At')->dateTime()->placeholder('Not yet actioned'),
                    TextEntry::make('actionedBy.name')->label('Actioned By')->placeholder('N/A'),
                    TextEntry::make('created_at')->label('Joined Waitlist')->dateTime(),
                ])
                ->columns(3),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRsvpWaitlist::route('/'),
            'view' => Pages\ViewRsvpWaitlist::route('/{record}'),
        ];
    }

    public static function handleApprove(RsvpWaitlist $record): void
    {
        $creditService = app(EventCreditService::class);

        try {
            $rsvp = $creditService->approveWaitlistEntry($record);

            $record->event->load(['organizer']);

            Mail::to($rsvp->email)->sendNow(new WaitlistApprovalMail($rsvp, $record->event));

            Log::info('Waitlist entry approved', [
                'waitlist_id' => $record->id,
                'rsvp_id' => $rsvp->id,
                'email' => $rsvp->email,
                'event_id' => $record->event_id,
            ]);

            Notification::make()
                ->title("{$record->guest_name} approved successfully")
                ->success()
                ->send();
        } catch (InsufficientCreditsException $e) {
            Notification::make()
                ->title('Cannot approve — no slots available')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (\Exception $e) {
            Log::error('Failed to approve waitlist entry', [
                'waitlist_id' => $record->id,
                'error' => $e->getMessage(),
            ]);

            Notification::make()
                ->title('Approval failed')
                ->body('An unexpected error occurred. Please try again.')
                ->danger()
                ->send();
        }
    }
}
