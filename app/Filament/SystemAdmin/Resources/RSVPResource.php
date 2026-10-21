<?php

namespace App\Filament\SystemAdmin\Resources;

use App\Filament\SystemAdmin\Resources\RSVPResource\Pages;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\RSVP;
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
    protected static ?string $navigationGroup = 'Event Management';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'RSVPs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('event_id')
                    ->label('Select Event')
                    ->relationship('event', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('organizer_id')
                    ->label('Organizer')
                    ->relationship('organizer', 'name')
                    ->searchable()
                    ->preload()
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
                Forms\Components\TextInput::make('guest_code')
                    ->label('Guest Code')
                    ->disabled(),
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
                Forms\Components\Select::make('status')
                    ->options([
                        'attending' => 'Attending',
                        'maybe' => 'Maybe',
                        'not_attending' => 'Not Attending',
                    ])
                    ->default('attending')
                    ->required(),
                Forms\Components\Toggle::make('checked_in')
                    ->label('Checked In'),
                Forms\Components\DateTimePicker::make('checked_in_at')
                    ->label('Checked In At')
                    ->hidden(fn (Forms\Get $get) => !$get('checked_in')),
                Forms\Components\Select::make('checked_in_by')
                    ->label('Checked In By')
                    ->relationship('checkedInBy', 'name')
                    ->searchable()
                    ->hidden(fn (Forms\Get $get) => !$get('checked_in')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.title')
                    ->label('Event')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('organizer.name')
                    ->label('Organizer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('guest_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('guest_code')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
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
                Tables\Columns\TextColumn::make('status')
                    ->badge()
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
                SelectFilter::make('organizer_id')
                    ->label('Organizer')
                    ->relationship('organizer', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('event_id')
                    ->label('Event')
                    ->relationship('event', 'title')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->options([
                        'attending' => 'Attending',
                        'maybe' => 'Maybe',
                        'not_attending' => 'Not Attending',
                    ]),
                SelectFilter::make('checked_in')
                    ->label('Check-in Status')
                    ->options([
                        '1' => 'Checked In',
                        '0' => 'Not Checked In',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['value'] === '1',
                                fn (Builder $query): Builder => $query->where('checked_in', true),
                            )
                            ->when(
                                $data['value'] === '0',
                                fn (Builder $query): Builder => $query->where('checked_in', false),
                            );
                    }),
                SelectFilter::make('referral_source')
                    ->label('Referral Source')
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
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Export RSVPs'),
            ])
            ->actions([
                ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->label('Export Selected'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            \Filament\Infolists\Components\Section::make('Event Information')
                ->schema([
                    TextEntry::make('event.title')
                        ->label('Event'),
                    TextEntry::make('organizer.name')
                        ->label('Organizer'),
                    TextEntry::make('event.start_date')
                        ->label('Event Date')
                        ->date(),
                    TextEntry::make('event.start_time')
                        ->label('Event Time')
                        ->time(),
                ])
                ->columns(2),

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