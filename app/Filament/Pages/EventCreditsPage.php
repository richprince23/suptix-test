<?php

namespace App\Filament\Pages;

use App\Models\Event;
use App\Models\EventCreditAccount;
use App\Models\EventCreditPurchase;
use App\Services\EventCreditService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EventCreditsPage extends Page
{
    protected static string $view = 'filament.pages.event-credits';

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Event Management';

    protected static ?string $navigationLabel = 'Event Credits';

    protected static ?int $navigationSort = 20;

    public ?int $eventId = null;

    public ?Event $event = null;

    public ?EventCreditAccount $account = null;

    public function mount(): void
    {
        $this->eventId = (int) request('event');

        if ($this->eventId) {
            $organizer = Filament::getTenant();
            $this->event = Event::where('id', $this->eventId)
                ->where('organizer_id', $organizer->id)
                ->where('is_rsvp', true)
                ->first();

            if ($this->event) {
                $this->account = app(EventCreditService::class)->ensureAccountExists($this->event);
            }
        }
    }

    public function getTitle(): string | Htmlable
    {
        return $this->event ? 'Credits — ' . $this->event->title : 'Event Credits';
    }

    public function getRsvpEvents(): \Illuminate\Database\Eloquent\Collection
    {
        return Filament::getTenant()
            ->events()
            ->where('is_rsvp', true)
            ->orderByDesc('start_date')
            ->get(['id', 'title', 'start_date']);
    }

    public function getPurchases(): \Illuminate\Database\Eloquent\Collection
    {
        if (! $this->event) {
            return collect();
        }

        return EventCreditPurchase::where('event_id', $this->event->id)
            ->orderByDesc('created_at')
            ->get();
    }

    public function getPacks(): array
    {
        return config('rsvp_credits.packs', []);
    }

    protected function getHeaderActions(): array
    {
        if (! $this->event || ! $this->account) {
            return [];
        }

        return [
            Action::make('purchase_credits')
                ->label('Buy Credits')
                ->icon('heroicon-o-shopping-cart')
                ->color('primary')
                ->form([
                    Forms\Components\Select::make('pack_key')
                        ->label('Credit Pack')
                        ->options(
                            collect($this->getPacks())
                                ->mapWithKeys(fn ($pack, $key) => [
                                    $key => $pack['label'] . ' — GHS ' . number_format($pack['price_ghs'], 2),
                                ])
                        )
                        ->required()
                        ->live()
                        ->searchable(),
                    Forms\Components\TextInput::make('quantity')
                        ->label('Quantity')
                        ->numeric()
                        ->default(1)
                        ->minValue(config('rsvp_credits.min_quantity', 1))
                        ->maxValue(config('rsvp_credits.max_quantity', 20))
                        ->required()
                        ->helperText('e.g. qty 2 of "100 Extra Attendees" = 200 slots'),
                ])
                ->action(function (array $data) {
                    $this->initiatePurchase($data['pack_key'], (int) $data['quantity']);
                }),
        ];
    }

    public function initiatePurchase(string $packKey, int $quantity): void
    {
        $packs = config('rsvp_credits.packs');

        if (! isset($packs[$packKey])) {
            Notification::make()->danger()->title('Invalid pack selected.')->send();
            return;
        }

        $pack = $packs[$packKey];
        $total = round($pack['price_ghs'] * $quantity, 2);
        $reference = 'CRDT-' . strtoupper(Str::random(12));

        $purchase = EventCreditPurchase::create([
            'event_id'               => $this->event->id,
            'organizer_id'           => $this->event->organizer_id,
            'paystack_reference'     => $reference,
            'amount'                 => $total,
            'pack_key'               => $packKey,
            'quantity'               => $quantity,
            'attendee_slots_granted' => $pack['slots'] * $quantity,
            'email_credits_granted'  => $pack['emails'] * $quantity,
            'sms_credits_granted'    => $pack['sms'] * $quantity,
            'status'                 => 'pending',
            'metadata'               => [
                'type'     => 'rsvp_credit_purchase',
                'pack'     => $pack,
                'quantity' => $quantity,
            ],
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.paystack.secret_key'),
            'Content-Type'  => 'application/json',
        ])->post('https://api.paystack.co/transaction/initialize', [
            'email'        => auth()->user()->email,
            'amount'       => (int) ($total * 100),
            'currency'     => 'GHS',
            'reference'    => $reference,
            'callback_url' => route('credits.verify', $this->event),
            'metadata'     => [
                'type'        => 'rsvp_credit_purchase',
                'event_id'    => $this->event->id,
                'purchase_id' => $purchase->id,
                'pack_key'    => $packKey,
                'quantity'    => $quantity,
            ],
        ]);

        $data = $response->json();

        if (! $response->successful() || ! ($data['status'] ?? false)) {
            $purchase->update(['status' => 'failed']);
            Log::error('Credit purchase Paystack init failed', ['event_id' => $this->event->id, 'response' => $data]);
            Notification::make()->danger()->title('Payment initialization failed. Please try again.')->send();
            return;
        }

        $this->redirect($data['data']['authorization_url']);
    }
}
