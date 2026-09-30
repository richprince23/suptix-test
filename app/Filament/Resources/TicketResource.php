<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Mail\TicketEmail;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Sales';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('organizer_id', Filament::getTenant()->id)->whereNotIn('status', ['used', 'cancelled', 'pending', 'refunded']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Ticket Information')->schema([
                    TextInput::make('ticket_number')
                        ->disabled()
                        ->dehydrated(false),
                    Select::make('event_id')
                        ->relationship('event', 'title', function (Builder $query) {
                            return $query->where('organizer_id', Filament::getTenant()->id);
                        })
                        ->required()
                        ->searchable()
                        ->preload(),
                    Select::make('ticket_type_id')
                        ->relationship('ticketType', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->afterStateUpdated(function ($state, $set) {
                            if ($state) {
                                $ticketType = TicketType::find($state);
                                if ($ticketType) {
                                    $set('price', $ticketType->price);
                                }
                            }
                        }),
                    Select::make('order_id')
                        ->relationship('order', 'order_number')
                        ->required()
                        ->searchable()
                        ->preload(),
                ])->columns(2),

                Section::make('Attendee Information')->schema([
                    TextInput::make('attendee_name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('attendee_email')
                        ->email()
                        ->required()
                        ->maxLength(255),
                ])->columns(2),

                Section::make('Ticket Details')->schema([
                    TextInput::make('price')
                        ->numeric()
                        ->prefix('GHS')
                        ->required()
                        ->readOnly()
                        ->helperText('Price is automatically set based on ticket type')
                        ->rules([
                            function (Get $get) {
                                return function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $orderId = $get('order_id');
                                    if (! $orderId || ! $value) {
                                        return;
                                    }

                                    $order = Order::find($orderId);
                                    if (! $order) {
                                        return;
                                    }

                                    // Get existing tickets for this order (excluding current ticket if editing)
                                    $existingTicketsTotal = $order->tickets()
                                        ->where('id', '!=', $get('id') ?? 0)
                                        ->sum('price');

                                    $newTotal = $existingTicketsTotal + $value;

                                    if ($newTotal > $order->total_amount) {
                                        $remaining = $order->total_amount - $existingTicketsTotal;
                                        $fail("Ticket price (GHS {$value}) exceeds remaining order amount. Maximum allowed: GHS {$remaining}");
                                    }
                                };
                            },
                        ]),
                    Select::make('status')
                        ->options([
                            'active' => 'Active',
                            'used' => 'Used',
                            'refunded' => 'Refunded',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('active')
                        ->required(),
                    DateTimePicker::make('used_at')
                        ->nullable(),
                    DateTimePicker::make('cancelled_at')
                        ->nullable()
                        ->visible(fn ($get) => $get('status') === 'cancelled'),
                    TextInput::make('notes')
                        ->label('Notes/Reason')
                        ->maxLength(1000)
                        ->columnSpanFull(),
                ])->columns(2),

                Section::make('Additional Information')
                    ->schema(function (?Ticket $record) {
                        if (! $record) {
                            return [];
                        }

                        $responses = $record->responses()->with('field')->get();
                        if ($responses->isEmpty()) {
                            return [];
                        }

                        return $responses->map(function ($response) {
                            return \Filament\Forms\Components\Placeholder::make('custom_field_' . $response->id)
                                ->label($response->field?->label ?? 'Custom Field')
                                ->content($response->value);
                        })->toArray();
                    })
                    ->visible(function (?Ticket $record) {
                        if (! $record) {
                            return false;
                        }
                        return $record->responses()->exists();
                    })
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_number')->description(fn ($record) => $record->order->order_number)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event.title')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('attendee_name')->description(fn ($record) => $record->attendee_email)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ticketType.name')
                    ->label('Ticket Type')
                    ->sortable(),
                TextColumn::make('price')
                    ->money('GHS')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'warning' => 'used',
                        'danger' => ['refunded', 'cancelled'],
                    ]),
                TextColumn::make('used_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cancelled_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('notes')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'used' => 'Used',
                        'refunded' => 'Refunded',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('event')
                    ->relationship('event', 'title', function (Builder $query) {
                        return $query->where('organizer_id', Filament::getTenant()->id);
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Export Tickets'),
            ])
            ->actions([

                ActionGroup::make([
                    Action::make('changeEmail')
                        ->label('Change Email')
                        ->icon('heroicon-o-envelope')
                        ->color('warning')
                        ->form([
                            TextInput::make('new_email')
                                ->label('New Email Address')
                                ->email()
                                ->required()
                                ->helperText('This will update the ticket email and resend the ticket.'),
                        ])
                        ->action(function (array $data, Ticket $record): void {
                            self::changeTicketEmail($record, $data['new_email']);
                        })
                        ->requiresConfirmation()
                        ->modalDescription('This will update the ticket email address and resend the ticket.')
                        ->modalHeading('Change Ticket Email')
                        ->visible(fn (Ticket $record) => $record->status === 'active'),

                    Action::make('resendTicket')
                        ->label('Resend Ticket')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->action(function (Ticket $record): void {
                            self::resendTicketEmail($record);
                        })
                        ->requiresConfirmation()
                        ->modalDescription('This will resend the ticket email to the attendee.')
                        ->visible(fn (Ticket $record) => $record->status === 'active'),

                ])->tooltip('More'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label('Export Selected'),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->whereIn('status', ['active', 'used'])->count();
    }

    /**
     * Change individual ticket email and resend
     */
    protected static function changeTicketEmail(Ticket $ticket, string $newEmail): void
    {
        $oldEmail = $ticket->attendee_email;

        // Update ticket email
        $ticket->update([
            'attendee_email' => $newEmail,
            'notes' => ($ticket->notes ? $ticket->notes."\n" : '')."Email changed from {$oldEmail} to {$newEmail}",
        ]);

        // Send ticket email to new address
        try {
            Mail::to($newEmail)->sendNow(new TicketEmail($ticket, $ticket->order));

            Notification::make()
                ->title('Email Changed Successfully')
                ->body("Ticket email updated to {$newEmail} and ticket resent.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Log::error('Failed to send ticket email after email change', [
                'ticket_id' => $ticket->id,
                'old_email' => $oldEmail,
                'new_email' => $newEmail,
                'error' => $e->getMessage(),
            ]);

            Notification::make()
                ->title('Email Changed but Send Failed')
                ->body("Email updated to {$newEmail} but failed to send ticket. Please try resending manually.")
                ->warning()
                ->send();
        }
    }

    /**
     * Resend individual ticket email
     */
    protected static function resendTicketEmail(Ticket $ticket): void
    {
        try {
            Mail::to($ticket->attendee_email)->sendNow(new TicketEmail($ticket, $ticket->order));

            Notification::make()
                ->title('Ticket Resent')
                ->body("Ticket resent successfully to {$ticket->attendee_email}")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Log::error('Failed to resend ticket email', [
                'ticket_id' => $ticket->id,
                'email' => $ticket->attendee_email,
                'error' => $e->getMessage(),
            ]);

            Notification::make()
                ->title('Failed to Resend Ticket')
                ->body('Failed to send ticket email. Please check the email address and try again.')
                ->danger()
                ->send();
        }
    }
}
