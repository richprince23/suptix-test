<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Traits\OwnedResource;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TicketTypesRelationManager extends RelationManager
{
    use OwnedResource;

    protected static string $relationship = 'ticketTypes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required()->maxLength(50),
                Textarea::make('description')->maxLength(200),
                TextInput::make('price')->prefix('GHS')
                    ->minValue(1)
                    ->maxLength(10)
                    ->required()->numeric(),
                TextInput::make('quantity')->numeric()->required(),
                DateTimePicker::make('sales_start')
                    ->native(function (Get $get) {
                        return $get('default');
                    })->displayFormat('d M Y H:i')->seconds(false)->minutesStep(15)
                    ->placeholder('Choose Date and Time')->hint('Click to set today\'s date')
                    ->hintAction(function (Set $set, $state) {
                        $state = Carbon::now()->format('d-M-Y');
                        $set('sales_start', $state);
                    })
                    ->required(),
                DateTimePicker::make('sales_end')
                    ->placeholder('Choose Date and Time')->hint('Click to set today\'s date')
                    ->native(function (Get $get) {
                        return $get('default');
                    })->displayFormat('d M Y H:i')->seconds(false)->minutesStep(15)
                    ->minDate(Carbon::now()->format('d-M-Y'))
                    ->required(),
                Toggle::make('default')->default(false)->reactive()->dehydrated(false)->label('Show default date/time selector'),
                Hidden::make('organizer_id')
                    ->default(fn(RelationManager $livewire) => $livewire->getOwnerRecord()->organizer_id)
                    ->dehydrated()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('event_id')
            ->columns([
                TextColumn::make('name')->description(fn($record) => $record->description ?? '')->wrap()->searchable()->sortable(),
                // TextColumn::make('event.title')->wrap(),
                TextColumn::make('price')->money('GHS')->sortable(),
                TextColumn::make('quantity')->sortable(),
                TextColumn::make('sales_start')->date('d-M-y h:i A')->sortable(),
                TextColumn::make('sales_end')->date('d-M-y h:i A')->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->hidden(function (RelationManager $livewire): bool {
                        return $livewire->getOwnerRecord()->is_rsvp == true;
                    }
                ),
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
