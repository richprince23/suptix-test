<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventSessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sessions';

    protected static ?string $title = 'Program / Schedule';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(100)
                    ->columnSpanFull(),

                TextInput::make('speaker')
                    ->nullable()
                    ->placeholder('eg. Jane Doe, CEO')
                    ->maxLength(150),

                TextInput::make('location')
                    ->nullable()
                    ->placeholder('eg. Main Hall, Room A')
                    ->maxLength(100),

                Textarea::make('description')
                    ->nullable()
                    ->maxLength(300)
                    ->rows(3)
                    ->columnSpanFull(),

                DatePicker::make('session_date')
                    ->nullable()
                    ->native(true)
                    ->label('Date')
                    ->helperText('Only needed for multi-day events. Leave blank for single-day events.')
                    ->visible(fn() => $this->getOwnerRecord()->isMultiDay()),

                TimePicker::make('start_time')
                    ->required()
                    ->seconds(false)
                    // ->step(5)
                    ->displayFormat('H:i A')
                    ->native(),

                TimePicker::make('end_time')
                    ->nullable()
                    ->seconds(false)
                    // ->step(5)
                    ->displayFormat('H:i A')
                    ->native(),

                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->label('Order')
                    ->helperText('Lower numbers appear first'),

                Hidden::make('organizer_id')
                    ->default(fn(RelationManager $livewire) => $livewire->getOwnerRecord()->organizer_id)
                    ->dehydrated()
                    ->required(),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('session_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable()
                    ->visible(fn() => $this->getOwnerRecord()->isMultiDay()),
                TextColumn::make('start_time')
                    ->label('Time')
                    ->formatStateUsing(fn($state, $record) => $state . ($record->end_time ? ' – ' . $record->end_time : ''))
                    ->sortable(),
                TextColumn::make('title')
                    ->description(fn($record) => $record->speaker ?? '')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('location')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->reorderable('sort_order')
            ->filters([])
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
