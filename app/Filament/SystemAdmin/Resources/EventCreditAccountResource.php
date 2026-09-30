<?php

namespace App\Filament\SystemAdmin\Resources;

use App\Filament\SystemAdmin\Resources\EventCreditAccountResource\Pages\ListEventCreditAccounts;
use App\Filament\SystemAdmin\Resources\EventCreditAccountResource\Pages\ViewEventCreditAccount;
use App\Models\EventCreditAccount;
use App\Services\EventCreditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EventCreditAccountResource extends Resource
{
    protected static ?string $model = EventCreditAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'System Settings';

    protected static ?string $navigationLabel = 'Event Credit Accounts';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Free Tier Limits')
                    ->description('Adjust the free tier limits for this event. Changes take effect immediately.')
                    ->schema([
                        Forms\Components\TextInput::make('free_attendee_slots')
                            ->label('Free Attendee Slots')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\TextInput::make('free_email_credits')
                            ->label('Free Email Credits')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\TextInput::make('free_sms_credits')
                            ->label('Free SMS Credits')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Usage (read-only)')
                    ->schema([
                        Forms\Components\TextInput::make('used_attendee_slots')->disabled(),
                        Forms\Components\TextInput::make('used_email_credits')->disabled(),
                        Forms\Components\TextInput::make('used_sms_credits')->disabled(),
                        Forms\Components\TextInput::make('purchased_attendee_slots')->disabled(),
                        Forms\Components\TextInput::make('purchased_email_credits')->disabled(),
                        Forms\Components\TextInput::make('purchased_sms_credits')->disabled(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.title')
                    ->label('Event')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('organizer.name')
                    ->label('Organizer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('used_attendee_slots')
                    ->label('Slots Used')
                    ->formatStateUsing(fn ($record) => $record->used_attendee_slots . ' / ' . $record->getTotalAttendeeSlots()),
                Tables\Columns\TextColumn::make('used_email_credits')
                    ->label('Emails Used')
                    ->formatStateUsing(fn ($record) => $record->used_email_credits . ' / ' . $record->getTotalEmailCredits()),
                Tables\Columns\TextColumn::make('used_sms_credits')
                    ->label('SMS Used')
                    ->formatStateUsing(fn ($record) => $record->used_sms_credits . ' / ' . $record->getTotalSmsCredits()),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('grant_comp_credits')
                    ->label('Grant Comp Credits')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('attendee_slots')
                            ->label('Attendee Slots')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\TextInput::make('email_credits')
                            ->label('Email Credits')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\TextInput::make('sms_credits')
                            ->label('SMS Credits')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\Textarea::make('note')
                            ->label('Reason / Note')
                            ->required()
                            ->rows(2),
                    ])
                    ->action(function (array $data, EventCreditAccount $record) {
                        app(EventCreditService::class)->grantAdminCredits(
                            $record,
                            (int) $data['attendee_slots'],
                            (int) $data['email_credits'],
                            (int) $data['sms_credits'],
                            $data['note'],
                        );

                        Notification::make()
                            ->success()
                            ->title('Credits granted successfully')
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventCreditAccounts::route('/'),
            'view'  => ViewEventCreditAccount::route('/{record}'),
            'edit'  => \App\Filament\SystemAdmin\Resources\EventCreditAccountResource\Pages\EditEventCreditAccount::route('/{record}/edit'),
        ];
    }
}
