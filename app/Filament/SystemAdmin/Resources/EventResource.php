<?php

namespace App\Filament\SystemAdmin\Resources;

use App\Enums\EventType;
use App\Filament\Resources\EventResource\RelationManagers\TicketTypesRelationManager;
use App\Filament\SystemAdmin\Resources\EventResource\Pages;
use App\Models\Event;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Events & Venues';

    protected static ?string $navigationLabel = 'All Events';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Event Details')->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(250),
                    Select::make('event_type')
                        ->label('Event Type')
                        ->options(
                            collect(EventType::cases())->mapWithKeys(function ($item) {
                                return [$item->value => $item->label()];
                            })
                        )->searchable()
                        ->required()
                        ->reactive(),
                    Select::make('organizer_id')
                        ->relationship('organizer', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                    TextInput::make('venue_name')->label('Venue Name'),
                    TextInput::make('venue_address')->label('Address'),
                    TextInput::make('venue_gps_code')->label('GPS Code'),
                    RichEditor::make('description')->columnSpanFull()
                        ->maxLength(8000),
                ])->columns(2),

                Section::make('Event Schedule')->schema([
                    DatePicker::make('start_date')->date()
                        ->native(false)
                        ->placeholder('Tap here to choose date')
                        ->required(),
                    TimePicker::make('start_time')->time()->seconds(false)
                        ->native(true)
                        ->placeholder('Tap here to set time')
                        ->required(),
                    DatePicker::make('end_date')->date()
                        ->native(false)
                        ->placeholder('Tap here to choose date'),
                    TimePicker::make('end_time')->time()->seconds(false)
                        ->placeholder('Tap here to set time')
                        ->required()
                        ->native(true),
                ])->columns(2),

                Section::make('Additional Information')->schema([
                    FileUpload::make('cover_image')
                        ->image()
                        ->imageEditor()
                        ->maxSize(5000)
                        ->directory('event-covers')->disk(config('filesystems.default')),
                    Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'published' => 'Published',
                            'live' => 'Live',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('draft')
                        ->required(),
                    TagsInput::make('tags')
                        ->placeholder('Add tags...')
                        ->hint('Type a tag and press enter'),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->size(60),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('organizer.name')
                    ->label('Organizer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('venue_name')
                    ->label('Venue')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event_type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => EventType::from($state)->label()),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'primary' => 'published',
                        'success' => 'live',
                        'danger' => 'cancelled',
                    ]),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('organizer')
                    ->relationship('organizer', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'live' => 'Live',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('event_type')
                    ->options(
                        collect(EventType::cases())->mapWithKeys(function ($item) {
                            return [$item->value => $item->label()];
                        })
                    ),
            ])->headerActions([
                ExportAction::make()
                    ->label('Export Events'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'view' => Pages\ViewEvent::route('/{record}'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            TicketTypesRelationManager::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }
}
