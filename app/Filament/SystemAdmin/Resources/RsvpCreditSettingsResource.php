<?php

namespace App\Filament\SystemAdmin\Resources;

use App\Filament\SystemAdmin\Resources\RsvpCreditSettingsResource\Pages\ManageRsvpCreditSettings;
use App\Models\RsvpCreditSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RsvpCreditSettingsResource extends Resource
{
    protected static ?string $model = RsvpCreditSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'System Settings';

    protected static ?string $navigationLabel = 'RSVP Credit Settings';

    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Default Free Tier')
                    ->description('These values are applied to each new RSVP event when its credit account is created.')
                    ->schema([
                        Forms\Components\TextInput::make('free_attendee_slots_default')
                            ->label('Free Attendee Slots (default)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->helperText('Number of free RSVP slots each new event receives.'),
                        Forms\Components\TextInput::make('free_email_credits_default')
                            ->label('Free Email Credits (default)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->helperText('Number of free confirmation emails each new event receives.'),
                        Forms\Components\TextInput::make('free_sms_credits_default')
                            ->label('Free SMS Credits (default)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->helperText('Free SMS credits per event. SMS is always paid — set 0 to keep it paid-only.'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Shared Email Quota Guard')
                    ->description('The shared Brevo SMTP account is limited to 300 emails/day across all organizers. Set a safe daily threshold to reserve headroom for system emails.')
                    ->schema([
                        Forms\Components\TextInput::make('brevo_daily_limit')
                            ->label('Brevo Daily Email Limit')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(300)
                            ->helperText('Recommended: 270 (300 minus 30 reserve for system emails).'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('free_attendee_slots_default')->label('Free Slots'),
                Tables\Columns\TextColumn::make('free_email_credits_default')->label('Free Emails'),
                Tables\Columns\TextColumn::make('free_sms_credits_default')->label('Free SMS'),
                Tables\Columns\TextColumn::make('brevo_daily_limit')->label('Brevo Daily Limit'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->label('Last Updated'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRsvpCreditSettings::route('/'),
        ];
    }
}
