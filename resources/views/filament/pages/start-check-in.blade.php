<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Event Selection Form -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center space-x-3">
                    <x-heroicon-o-calendar-days class="w-6 h-6 text-primary-600" />
                    <span>Event Selection</span>
                </div>
            </x-slot>
            
            <x-slot name="description">
                Choose which event you want to start check-ins for. Only published events from your organization are available.
            </x-slot>

            <div class="space-y-4">
                {{ $this->form }}
            </div>
        </x-filament::section>

        <!-- Event Details Preview -->
        @if($this->getEventDetails())
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center space-x-3">
                        <x-heroicon-o-information-circle class="w-6 h-6 text-blue-600" />
                        <span>Event Details</span>
                    </div>
                </x-slot>

                @php
                    $eventDetails = $this->getEventDetails();
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <x-filament::badge size="lg" color="primary">
                                {{ $eventDetails['title'] }}
                            </x-filament::badge>
                        </div>
                        
                        <div class="space-y-2">
                            <div class="flex items-center space-x-2">
                                <x-heroicon-o-calendar class="w-4 h-4 text-gray-500" />
                                <span class="text-sm font-medium">{{ $eventDetails['date'] }}</span>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <x-heroicon-o-clock class="w-4 h-4 text-gray-500" />
                                <span class="text-sm font-medium">{{ $eventDetails['time'] }}</span>
                            </div>
                            
                            @if($eventDetails['venue'])
                                <div class="flex items-center space-x-2">
                                    <x-heroicon-o-map-pin class="w-4 h-4 text-gray-500" />
                                    <span class="text-sm font-medium">{{ $eventDetails['venue'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-600">Status:</span>
                            <x-filament::badge 
                                :color="$eventDetails['status'] === 'published' ? 'success' : 'warning'"
                            >
                                {{ ucfirst($eventDetails['status']) }}
                            </x-filament::badge>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-600">Event Type:</span>
                            <x-filament::badge 
                                :color="$eventDetails['is_rsvp'] ? 'info' : 'primary'"
                            >
                                {{ $eventDetails['is_rsvp'] ? 'RSVP Event' : 'Ticketed Event' }}
                            </x-filament::badge>
                        </div>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-start space-x-3">
                        <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 mt-0.5" />
                        <div>
                            <h4 class="text-sm font-medium text-blue-800">Ready to Start Check-In</h4>
                            <p class="text-sm text-blue-700 mt-1">
                                Click the "Start Check-In" button in the top right to begin checking in attendees for this event.
                                You'll be redirected to the check-in interface where you can scan tickets or manually enter ticket numbers.
                            </p>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @else
            <!-- Instructions when no event is selected -->
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center space-x-3">
                        <x-heroicon-o-light-bulb class="w-6 h-6 text-yellow-600" />
                        <span>Getting Started</span>
                    </div>
                </x-slot>

                <div class="text-center py-8">
                    <x-heroicon-o-ticket class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Select an Event to Begin</h3>
                    <p class="text-gray-600 mb-6 max-w-md mx-auto">
                        Choose an event from the dropdown above to start the check-in process. 
                        Only published events from your organization will be available.
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-2xl mx-auto">
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <x-heroicon-o-qr-code class="w-8 h-8 text-gray-600 mx-auto mb-2" />
                            <h4 class="font-medium text-gray-900">QR Code Scanning</h4>
                            <p class="text-sm text-gray-600">Scan tickets quickly with mobile devices</p>
                        </div>
                        
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <x-heroicon-o-hashtag class="w-8 h-8 text-gray-600 mx-auto mb-2" />
                            <h4 class="font-medium text-gray-900">Manual Entry</h4>
                            <p class="text-sm text-gray-600">Enter ticket numbers manually</p>
                        </div>
                        
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <x-heroicon-o-user-group class="w-8 h-8 text-gray-600 mx-auto mb-2" />
                            <h4 class="font-medium text-gray-900">Bulk Check-In</h4>
                            <p class="text-sm text-gray-600">Check in multiple tickets at once</p>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>