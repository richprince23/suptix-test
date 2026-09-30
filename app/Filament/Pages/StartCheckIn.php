<?php

namespace App\Filament\Pages;

use App\Models\Event;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class StartCheckIn extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    protected static ?string $navigationGroup = 'Event Management';

    protected static string $view = 'filament.pages.start-check-in';

    protected static ?string $title = 'Start Check-In';

    protected static ?string $navigationLabel = 'Start Check-In';

    protected static ?int $navigationSort = 10;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('event_id')
                    ->label('Select Event')
                    ->placeholder('Choose an event to start check-in')
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        $organizer = Filament::getTenant();

                        if (!$organizer) {
                            return [];
                        }

                        return Event::where('organizer_id', $organizer->id)
                            ->whereIn('status', ['published', 'live'])
                            ->orderBy('start_date', 'desc')
                            ->orderBy('start_time', 'desc')
                            ->get()
                            ->mapWithKeys(function ($event) {
                                return [
                                    $event->id => $event->title . ' - ' .
                                        $event->start_date->format('M j, Y') . ' at ' .
                                        \Carbon\Carbon::parse($event->start_time)->format('g:i A')
                                ];
                            });
                    })
                    ->required()
                    ->preload()
                    ->live()
                    ->helperText('Select an event to begin the check-in process. Only published events are shown.')
                    ->hint('Make sure you have the right permissions for this event'),
            ])
            ->statePath('data');
    }

    public function getTitle(): string|Htmlable
    {
        return 'Start Check-In Session';
    }

    public function getHeading(): string|Htmlable
    {
        return 'Start Check-In Session';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Select an event and start checking in attendees';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('startCheckIn')
                ->label('Start Check-In')
                ->icon('heroicon-o-play')
                ->color('success')
                ->size('lg')
                ->disabled(fn () => empty($this->data['event_id']))
                ->action(function () {
                    $eventId = $this->data['event_id'];

                    if (!$eventId) {
                        Notification::make()
                            ->title('No Event Selected')
                            ->body('Please select an event before starting check-in.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $event = Event::find($eventId);

                    if (!$event) {
                        Notification::make()
                            ->title('Event Not Found')
                            ->body('The selected event could not be found.')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Verify organizer has access to this event
                    $organizer = Filament::getTenant();
                    if ($event->organizer_id !== $organizer->id) {
                        Notification::make()
                            ->title('Access Denied')
                            ->body('You do not have permission to manage check-ins for this event.')
                            ->danger()
                            ->send();
                        return;
                    }

                    Notification::make()
                        ->title('Check-In Started')
                        ->body("Starting check-in session for: {$event->title}")
                        ->success()
                        ->send();

                    // Redirect to the check-in page with event parameter
                    $this->redirect(route('attendee-checkin', ['event' => $event->id]));
                }),
        ];
    }

    public function getEventDetails(): ?array
    {
        if (empty($this->data['event_id'])) {
            return null;
        }

        $event = Event::with('organizer')
            ->find($this->data['event_id']);

        if (!$event) {
            return null;
        }

        return [
            'title' => $event->title,
            'date' => $event->start_date->format('l, F j, Y'),
            'time' => \Carbon\Carbon::parse($event->start_time)->format('g:i A'),
            'venue' => $event->venue_name,
            'status' => $event->status,
            'is_rsvp' => $event->is_rsvp,
        ];
    }
}
