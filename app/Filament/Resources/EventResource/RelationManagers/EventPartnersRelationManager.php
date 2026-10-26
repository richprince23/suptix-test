<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventPartnersRelationManager extends RelationManager
{
    protected static string $relationship = 'allPartners';

    protected static ?string $title = 'Partners & Sponsors';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('type')
                    ->required()
                    ->options([
                        'partner' => 'Partner',
                        'sponsor' => 'Sponsor',
                    ])
                    ->live(),

                TextInput::make('name')
                    ->required()
                    ->maxLength(100),

                Select::make('tier')
                    ->options([
                        'gold' => 'Gold',
                        'silver' => 'Silver',
                        'bronze' => 'Bronze',
                        'in-kind' => 'In-Kind',
                        'media' => 'Media',
                    ])
                    ->nullable()
                    ->hidden(fn ($get) => $get('type') !== 'sponsor')
                    ->helperText('Sponsor tier level'),

                TextInput::make('website')
                    ->url()
                    ->nullable()
                    ->maxLength(255),

                Textarea::make('description')
                    ->nullable()
                    ->maxLength(500)
                    ->rows(3)
                    ->columnSpanFull(),

                FileUpload::make('logo')
                    ->image()
                    ->nullable()
                    ->disk(config('filesystems.default'))
                    ->directory('event-partners')
                    ->maxSize(2048)
                    ->helperText('Max 2MB.'),

                Toggle::make('is_visible')
                    ->label('Show on event page')
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
            ->recordTitleAttribute('name')
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('logo_url')
                    ->label('Logo')
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&size=64&background=f3f4f6&color=6b7280'),

                TextColumn::make('name')
                    ->searchable()
                    ->description(fn ($record) => $record->description ? str($record->description)->limit(60) : null),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'sponsor' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                TextColumn::make('tier')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn ($state) => $state ? ucfirst($state) : null)
                    ->placeholder('—'),

                TextColumn::make('website')
                    ->limit(30)
                    ->placeholder('—'),

                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean(),

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
