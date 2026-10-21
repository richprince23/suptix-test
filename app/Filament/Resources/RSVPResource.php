<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RSVPResource\Pages;
use App\Models\Event;
use App\Models\RSVP;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RSVPResource extends Resource
{
    protected static ?string $model = RSVP::class;

    protected static ?string $modelLabel = 'RSVPs';
    protected static ?string $navigationGroup = 'Sales';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'RSVPs';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('organizer_id', Filament::getTenant()->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('event_id')->label('Select Event')
                    ->options(function (): array {
                        return Event::where('organizer_id', Filament::getTenant()->id)
                            ->pluck('title', 'id')
                            ->toArray();
                    })->searchable()->live()->preload()
                    ->required(),
                Forms\Components\Hidden::make('organizer_id')->default(fn () => Filament::getTenant()->id)
                    ->required(),
                Forms\Components\TextInput::make('guest_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(16),
                Forms\Components\Select::make('referral_source')
                    ->label('How did they hear about this event?')
                    ->options([
                        'Facebook' => 'Facebook',
                        'LinkedIn' => 'LinkedIn', 
                        'Instagram' => 'Instagram',
                        'Twitter/X' => 'Twitter/X',
                        'Friend or Family' => 'Friend or Family',
                        'Flyer or Poster' => 'Flyer or Poster',
                        'Email Newsletter' => 'Email Newsletter',
                        'Website' => 'Website',
                        'Other' => 'Other',
                    ])
                    ->searchable(),
                Forms\Components\Textarea::make('notes')
                    ->label('Special Notes')
                    ->maxLength(250)
                    ->rows(3),
                Forms\Components\Select::make('status')->options([
                    'attending' => 'Attending',
                    'maybe' => 'Maybe',
                    'not_attending' => 'Not Attending',
                ])->default('attending')
                    ->required(),
                Forms\Components\Toggle::make('checked_in')
                    ->label('Checked In')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.title')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('guest_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('guest_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('referral_source')
                    ->label('Referral')
                    ->badge()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(50)
                    ->tooltip(function ($record): ?string {
                        return $record->notes ? $record->notes : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->colors([
                        'success' => fn ($record) => $record->status == 'attending',
                        'warning' => fn ($record) => $record->status == 'maybe',
                        'danger' => fn ($record) => $record->status == 'not_attending',
                    ]),
                Tables\Columns\IconColumn::make('checked_in')
                    ->label('Checked In')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event_id')
                    ->label('RSVPed Event')
                    ->options(function () {
                        $organizer = Filament::getTenant();

                        if (!$organizer) {
                            return [];
                        }

                        return Event::where('organizer_id', $organizer->id)
                        ->where('is_rsvp', true)
                            ->orderBy('start_date', 'desc')
                            ->pluck('title', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload(),
            ])->headerActions([
                ExportAction::make()
                    ->label('Export RSVP List'),
            ])
            ->actions([
                ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('message')->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            \Filament\Infolists\Components\Section::make('Event Information')
                ->schema([
                    TextEntry::make('event.title')
                        ->label('Event'),
                    TextEntry::make('event.start_date')
                        ->label('Event Date')
                        ->date(),
                    TextEntry::make('event.start_time')
                        ->label('Event Time')
                        ->time(),
                ])
                ->columns(3),

            \Filament\Infolists\Components\Section::make('Guest Information')
                ->schema([
                    TextEntry::make('guest_name')
                        ->label('Full Name'),
                    TextEntry::make('guest_code')
                        ->label('Guest Code')
                        ->badge()
                        ->color('primary'),
                    TextEntry::make('email')
                        ->label('Email Address')
                        ->copyable(),
                    TextEntry::make('phone')
                        ->label('Phone Number')
                        ->copyable(),
                ])
                ->columns(2),

            \Filament\Infolists\Components\Section::make('Additional Details')
                ->schema([
                    TextEntry::make('referral_source')
                        ->label('How did they hear about this event?')
                        ->badge()
                        ->color('gray'),
                    TextEntry::make('notes')
                        ->label('Special Notes')
                        ->placeholder('No special notes provided'),
                    TextEntry::make('status')
                        ->badge()
                        ->color(fn ($record): string => match ($record->status) {
                            'attending' => 'success',
                            'maybe' => 'warning', 
                            'not_attending' => 'danger',
                            default => 'gray',
                        }),
                ])
                ->columns(2),

            \Filament\Infolists\Components\Section::make('Check-in Status')
                ->schema([
                    \Filament\Infolists\Components\IconEntry::make('checked_in')
                        ->label('Checked In')
                        ->boolean()
                        ->trueIcon('heroicon-o-check-badge')
                        ->falseIcon('heroicon-o-x-mark')
                        ->trueColor('success')
                        ->falseColor('gray'),
                    TextEntry::make('checked_in_at')
                        ->label('Checked In At')
                        ->dateTime()
                        ->placeholder('Not checked in yet'),
                    TextEntry::make('checkedInBy.name')
                        ->label('Checked In By')
                        ->placeholder('N/A'),
                ])
                ->columns(3),

            \Filament\Infolists\Components\Section::make('Custom Responses')
                ->schema([
                    \Filament\Infolists\Components\RepeatableEntry::make('responses')
                        ->schema([
                            TextEntry::make('field.label')
                                ->label('Question'),
                            TextEntry::make('value')
                                ->label('Answer')
                                ->placeholder('No answer provided'),
                        ])
                        ->columns(2)
                        ->label(''),
                ])
                ->visible(fn ($record) => $record->responses()->exists())
                ->collapsible(),

            \Filament\Infolists\Components\Section::make('Timestamps')
                ->schema([
                    TextEntry::make('created_at')
                        ->label('RSVP Date')
                        ->dateTime(),
                    TextEntry::make('updated_at')
                        ->label('Last Updated')
                        ->dateTime(),
                ])
                ->columns(2)
                ->collapsible(),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRSVPS::route('/'),
            'create' => Pages\CreateRSVP::route('/create'),
            'view' => Pages\ViewRSVP::route('/{record}'),
            'edit' => Pages\EditRSVP::route('/{record}/edit'),
        ];
    }
}
