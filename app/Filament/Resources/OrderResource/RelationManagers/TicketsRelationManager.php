<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\TicketType;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TicketsRelationManager extends RelationManager
{
    protected static string $relationship = 'tickets';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('attendee_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('attendee_email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('ticket_type_id')
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
                // dd($form),
                Hidden::make('event_id')->default(fn (RelationManager $livewire) => $livewire->getOwnerRecord()->event_id),
                Hidden::make('organizer_id')->default(Filament::getTenant()->id),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->prefix('GHS')
                    ->required()
                    ->readOnly()
                    ->helperText('Price is automatically set based on ticket type')
                    ->rules([
                        function (Get $get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                if (! $value) {
                                    return;
                                }

                                // Get the order from the relation manager
                                $order = $this->getOwnerRecord();
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
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'used' => 'Used',
                        'refunded' => 'Refunded',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ticket_number')
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number'),
                Tables\Columns\TextColumn::make('attendee_name'),
                Tables\Columns\TextColumn::make('attendee_email'),
                Tables\Columns\TextColumn::make('ticketType.name')
                    ->label('Type'),
                Tables\Columns\TextColumn::make('price')
                    ->money('GHS'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'warning' => 'used',
                        'danger' => ['refunded', 'cancelled'],
                    ]),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
