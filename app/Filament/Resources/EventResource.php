<?php

namespace App\Filament\Resources;

use App\Enums\EventType;
use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers\EventFormFieldsRelationManager;
use App\Filament\Resources\EventResource\RelationManagers\TicketTypesRelationManager;
use App\Filament\Resources\EventResource\Widgets;
use App\Models\Event;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Event Management';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_events') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_events') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_events') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_events') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Click to show/hide event details')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(80),

                        Select::make('event_type')
                            ->label('Event Type')
                            ->options(
                                collect(EventType::cases())->mapWithKeys(fn($item) => [
                                    $item->value => $item->label()
                                ])
                            )
                            ->helperText(function ($get) {
                                $selected = $get('event_type');
                                if ($selected && EventType::tryFrom($selected)) {
                                    return EventType::from($selected)->info();
                                }
                                return 'Select an event type to see the details';
                            })
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->preload(),

                            RichEditor::make('description')->required()->maxLength(8000)->columnSpanFull(),
                        // Mode selector
                        Select::make('mode')
                            ->label('Event Mode')
                            ->options([
                                'in-person' => 'In-Person',
                                'online'    => 'Online',
                                'hybrid'    => 'Hybrid',
                            ])->default('in-person')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive(),
                        // In-person venue (inline fields)
                        Group::make([
                            TextInput::make('venue_name')
                                ->label('Venue Name')
                                ->required(fn($get) => in_array($get('mode'), ['in-person', 'hybrid']))
                                ->placeholder('eg. Accra International Conference Centre'),
                            Group::make([
                                TextInput::make('venue_address')
                                    ->label('Address')
                                    ->placeholder('eg. Liberation Road, Accra'),
                                TextInput::make('venue_gps_code')
                                    ->label('GPS Code')
                                    ->placeholder('eg. GA-123-4567'),
                            ])->columns(2),
                            Group::make([
                                TextInput::make('venue_capacity')
                                    ->label('Capacity')
                                    ->numeric()
                                    ->minValue(1)
                                    ->placeholder('eg. 500'),
                                TextInput::make('venue_phone')
                                    ->label('Venue Phone')
                                    ->tel()
                                    ->placeholder('eg. 0302123456'),
                            ])->columns(2),
                        ])
                            ->visible(fn($get) => in_array($get('mode'), ['in-person', 'hybrid']))
                            ->columnSpanFull(),

                        // Online venue
                        Group::make([
                            TextInput::make('platform_name')->label('Platform Name')->required(),
                            TextInput::make('online_link')->url()->label('Meeting Link')->placeholder('https://example.com/something')->required(),
                            Toggle::make('is_public_link')->label('Publicly Accessible')->hint('Toggle to show or hide link before registration')->columnSpanFull(),
                        ])
                            ->visible(fn($get) => in_array($get('mode'), ['online', 'hybrid']))
                            ->columns(2),

                        Toggle::make('is_multi_day')
                            ->label('Multi-day event')
                            ->hint('Turn on if this event spans more than one day')
                            ->reactive()
                            ->afterStateHydrated(function ($record, $set) {
                                if ($record && $record->end_date && $record->end_date->gt($record->start_date)) {
                                    $set('is_multi_day', true);
                                }
                            })
                            ->dehydrated(false),

                        DatePicker::make('start_date')->required()->native(true),
                        DatePicker::make('end_date')
                            ->nullable()
                            ->native(true)
                            ->visible(fn($get) => $get('is_multi_day'))
                            ->required(fn($get) => $get('is_multi_day'))
                            ->label('End Date')
                            ->afterOrEqual('start_date'),

                        Group::make([
                            TimePicker::make('start_time')
                                ->required()
                                ->displayFormat('H:i A')
                                ->native()
                                ->label(fn($get) => $get('is_multi_day') ? 'Opening Time (Day 1)' : 'Start Time'),
                            TimePicker::make('end_time')
                                ->nullable()
                                ->displayFormat('H:i A')
                                ->native()
                                ->label(fn($get) => $get('is_multi_day') ? 'Closing Time (Last Day)' : 'End Time'),
                        ])->columns(2),

                                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'live' => 'Live',
                                'canceled' => 'Canceled',
                            ])
                            ->default('published')
                            ->required(),

                        FileUpload::make('cover_image')
                            ->image()
                            ->maxFiles(1)
                            ->maxSize(5140)
                            ->disk(config('filesystems.default'))
                            ->directory('event-covers')
                            ->imageEditor()
                            ->imageEditorAspectRatios(['16:9', '4:3', '1:1'])
                            ->visibility('public')
                            ->required(),

                        TagsInput::make('tags')->helperText('Type a tag and press enter. eg. dinner'),
                        Hidden::make('organizer_id')
                            ->default(fn() => Filament::getTenant()->id)
                            ->dehydrated(),
                    ])
                    ->collapsible()
                    ->columns(2)
                    ->collapsed(true),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image'),
                TextColumn::make('title')->wrap()->limit(50),
                TextColumn::make('venue_name')->label('Venue'),
                TextColumn::make('start_date')->date(),
                TextColumn::make('start_time')->time(),
                // TextColumn::make('team.name')->wrap(),
                TextColumn::make('status')->badge()->colors([
                    'success' => fn($state) => $state == 'published' && $state == 'live',
                    'warning' => fn($state) => $state == 'draft',
                    'danger' => fn($state) => $state === 'canceled',
                ]),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Export Events'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label('Export Selected'),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TicketTypesRelationManager::class,
            EventFormFieldsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\EventStatsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
            'view' => Pages\ViewEvent::route('/{record}/view'),
        ];
    }
}
