<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Models\EventSession;
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

class EventPeopleRelationManager extends RelationManager
{
    protected static string $relationship = 'people';

    protected static ?string $title = 'People';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(100),

                Select::make('role')
                    ->required()
                    ->options([
                        'host' => 'Host',
                        'speaker' => 'Speaker',
                        'moderator' => 'Moderator',
                        'panelist' => 'Panelist',
                        'performer' => 'Performer',
                        'mc' => 'MC / Emcee',
                        'guest' => 'Guest',
                        'other' => 'Other',
                    ]),

                Select::make('session_id')
                    ->label('Session (optional)')
                    ->nullable()
                    ->placeholder('All sessions')
                    ->options(fn (RelationManager $livewire) => EventSession::where('event_id', $livewire->getOwnerRecord()->id)
                        ->orderBy('session_date')
                        ->orderBy('start_time')
                        ->get()
                        ->mapWithKeys(fn ($s) => [
                            $s->id => collect([
                                $s->session_date?->format('M j'),
                                $s->start_time ? \Carbon\Carbon::parse($s->start_time)->format('g:ia') : null,
                                $s->title,
                            ])->filter()->implode(' · '),
                        ])
                    )
                    ->helperText('Leave blank to show this person on all sessions / days.'),

                Textarea::make('bio')
                    ->nullable()
                    ->maxLength(500)
                    ->rows(3)
                    ->columnSpanFull(),

                FileUpload::make('photo')
                    ->image()
                    ->nullable()
                    ->disk(config('filesystems.default'))
                    ->directory('event-people')
                    ->maxSize(2048)
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('1:1')
                    ->helperText('Square image recommended. Max 2MB.'),

                Toggle::make('is_visible')
                    ->label('Show on event page')
                    ->default(true)
                    ->helperText('Hide to exclude from public event page'),

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
            ->recordTitleAttribute('name')
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('photo_url')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&size=64'),

                TextColumn::make('name')
                    ->searchable()
                    ->description(fn ($record) => $record->bio ? str($record->bio)->limit(60) : null),

                TextColumn::make('role')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                TextColumn::make('session.title')
                    ->label('Session')
                    ->placeholder('All sessions')
                    ->limit(30),

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
