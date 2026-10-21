<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Event selector --}}
        <x-filament::section>
            <x-slot name="heading">Event Selection</x-slot>
            <x-slot name="description">Choose an RSVP event to view and manage its credit account.</x-slot>

            <div>
                <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                    <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Select Event</span>
                </label>
                <div class="mt-2">
                    <select
                        class="fi-select-input block w-full max-w-lg rounded-lg border border-gray-300 bg-white py-1.5 pe-10 ps-3 text-sm text-gray-950 shadow-sm outline-none transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-primary-500"
                        onchange="window.location.href = '{{ request()->url() }}?event=' + this.value"
                    >
                        <option value="">— Choose an RSVP event —</option>
                        @foreach ($this->getRsvpEvents() as $ev)
                            <option value="{{ $ev->id }}" @selected($ev->id === $this->eventId)>
                                {{ $ev->title }} ({{ $ev->start_date?->format('M j, Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </x-filament::section>

        @if ($this->event && $this->account)
            @php
                $account = $this->account;
                $gauges = [
                    ['label' => 'Attendee Slots',      'used' => $account->used_attendee_slots,  'total' => $account->getTotalAttendeeSlots()],
                    ['label' => 'Confirmation Emails', 'used' => $account->used_email_credits,   'total' => $account->getTotalEmailCredits()],
                    ['label' => 'SMS Credits',         'used' => $account->used_sms_credits,     'total' => $account->getTotalSmsCredits()],
                ];
            @endphp

            {{-- Credit gauges --}}
            <x-filament::section>
                <x-slot name="heading">Credit Balance</x-slot>
                <x-slot name="description">Current usage across attendee slots, emails, and SMS for this event.</x-slot>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach ($gauges as $gauge)
                        @php
                            $remaining = $gauge['total'] - $gauge['used'];
                            $pct       = $gauge['total'] > 0 ? min(100, round(($gauge['used'] / $gauge['total']) * 100)) : 0;
                            $barColor  = $pct >= 90 ? 'bg-danger-500' : ($pct >= 70 ? 'bg-warning-500' : 'bg-primary-500');
                            $badgeColor = $pct >= 90 ? 'danger' : ($pct >= 70 ? 'warning' : 'success');
                        @endphp
                        <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 p-5 space-y-3">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">{{ $gauge['label'] }}</p>
                                <x-filament::badge :color="$badgeColor" size="sm">{{ $pct }}%</x-filament::badge>
                            </div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($remaining) }}
                                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">remaining</span>
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                {{ number_format($gauge['used']) }} used &middot; {{ number_format($gauge['total']) }} total
                            </p>
                            <div class="h-1.5 rounded-full bg-gray-200 dark:bg-white/10 overflow-hidden">
                                <div class="h-full rounded-full {{ $barColor }} transition-all duration-300" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>

            {{-- Purchase history --}}
            <x-filament::section>
                <x-slot name="heading">Purchase History</x-slot>
                <x-slot name="description">All credit pack purchases for this event.</x-slot>

                @php $purchases = $this->getPurchases(); @endphp

                @if ($purchases->isEmpty())
                    <div class="text-center py-8">
                        <x-heroicon-o-credit-card class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-3" />
                        <p class="text-sm text-gray-500 dark:text-gray-400">No purchases yet. Use the <strong>Buy Credits</strong> button to add more slots.</p>
                    </div>
                @else
                    <div class="overflow-x-auto -mx-6 -mb-6">
                        <table class="min-w-full divide-y divide-gray-100 dark:divide-white/10 text-sm">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-white/5">
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Pack</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Qty</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                                @foreach ($purchases as $purchase)
                                    @php
                                        $packs      = config('rsvp_credits.packs');
                                        $packLabel  = $packs[$purchase->pack_key]['label'] ?? $purchase->pack_key;
                                        $statusColor = match($purchase->status) {
                                            'completed' => 'success',
                                            'failed'    => 'danger',
                                            default     => 'warning',
                                        };
                                    @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                        <td class="px-6 py-3 text-gray-600 dark:text-gray-300">{{ $purchase->created_at->format('M j, Y') }}</td>
                                        <td class="px-6 py-3 text-gray-800 dark:text-gray-200 font-medium">{{ $packLabel }}</td>
                                        <td class="px-6 py-3 text-gray-600 dark:text-gray-300">x{{ $purchase->quantity }}</td>
                                        <td class="px-6 py-3 text-gray-800 dark:text-gray-200">GHS {{ number_format($purchase->amount, 2) }}</td>
                                        <td class="px-6 py-3">
                                            <x-filament::badge :color="$statusColor" size="sm">
                                                {{ ucfirst($purchase->status) }}
                                            </x-filament::badge>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-filament::section>

        @elseif ($this->eventId && ! $this->event)
            <x-filament::section>
                <div class="text-center py-6">
                    <x-heroicon-o-exclamation-triangle class="w-10 h-10 text-warning-400 mx-auto mb-3" />
                    <p class="text-sm text-gray-500 dark:text-gray-400">Event not found or it is not an RSVP event.</p>
                </div>
            </x-filament::section>

        @else
            <x-filament::section>
                <div class="text-center py-8">
                    <x-heroicon-o-credit-card class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">No event selected</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Select an RSVP event above to view its credit account and purchase history.</p>
                </div>
            </x-filament::section>
        @endif

    </div>
</x-filament-panels::page>
