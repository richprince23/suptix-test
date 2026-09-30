<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class EventFormFieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'formFields';

    protected static ?string $title = 'Custom Form Fields';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('label')
                    ->label('Question / Label')
                    ->required()
                    ->maxLength(200)
                    ->placeholder('eg. Dietary requirement')
                    ->columnSpanFull(),

                Select::make('field_type')
                    ->label('Field Type')
                    ->options([
                        'text' => 'Short text',
                        'textarea' => 'Long text',
                        'select' => 'Dropdown (select)',
                        'radio' => 'Radio buttons',
                        'checkbox' => 'Checkboxes (multi-select)',
                    ])
                    ->required()
                    ->default('text')
                    ->reactive(),

                TextInput::make('placeholder')
                    ->label('Placeholder text')
                    ->nullable()
                    ->maxLength(150)
                    ->placeholder('eg. Enter your dietary preference')
                    ->visible(fn ($get) => in_array($get('field_type'), ['text', 'textarea'])),

                Repeater::make('options')
                    ->label('Options')
                    ->schema([
                        TextInput::make('value')
                            ->label('Option')
                            ->required()
                            ->placeholder('eg. Vegetarian'),
                    ])
                    ->addActionLabel('Add option')
                    ->minItems(1)
                    ->columnSpanFull()
                    ->visible(fn ($get) => in_array($get('field_type'), ['select', 'radio', 'checkbox']))
                    ->helperText('Add the choices guests can pick from.'),

                Toggle::make('is_required')
                    ->label('Required')
                    ->default(false),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),

                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->label('Order')
                    ->helperText('Lower numbers appear first'),

                Hidden::make('organizer_id')
                    ->default(fn (RelationManager $livewire) => $livewire->getOwnerRecord()->organizer_id)
                    ->dehydrated()
                    ->required(),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('label')
                    ->label('Question')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('field_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'text' => 'Short text',
                        'textarea' => 'Long text',
                        'select' => 'Dropdown',
                        'radio' => 'Radio',
                        'checkbox' => 'Checkboxes',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'select', 'radio', 'checkbox' => 'warning',
                        default => 'gray',
                    }),

                IconColumn::make('is_required')
                    ->label('Required')
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Active'),
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
