<template>
    <Head title="Attendee Check-In" />
    <div class="min-h-screen bg-background">
        <!-- Navigation -->
        <NavBar background-color="bg-white" text-color="primary" class="bg-black" />

        <!-- Main Content -->
        <div class="pt-20 pb-16">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 text-center">
                    <h1 class="bg-card mb-4 text-3xl font-bold text-foreground md:text-4xl">Attendee Check-In</h1>
                    <p class="text-lg text-foreground/80">Check in attendees using ticket number or order number</p>
                </div>

                <!-- Selected Event Info -->
                <div v-if="selectedEvent" class="mb-8 rounded-all  bg-whites p-6">
                    <div class="flex flex-col-reverse md:flex-row items-start md:items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-foreground mb-2">{{ selectedEvent.title }}</h2>
                            <div class="space-y-1 text-sm text-foreground/80">
                                <div class="flex items-center">
                                    <Calendar class="mr-2 h-4 w-4" />
                                    <span>{{ formatDate(selectedEvent.start_date) }} at {{ formatTime(selectedEvent.start_time) }}</span>
                                </div>
                                <div v-if="selectedEvent.venue_name" class="flex items-center">
                                    <MapPin class="mr-2 h-4 w-4" />
                                    <span>{{ selectedEvent.venue_name }}</span>
                                </div>
                                <div v-if="selectedEvent.organizer" class="flex items-center">
                                    <Building class="mr-2 h-4 w-4" />
                                    <span>{{ selectedEvent.organizer.name }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="inline-flex items-center text-xs sm:text-base px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Ticketed Event
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Check-in Type Selector -->
                <div class="mb-8 rounded-all  bg-whites p-6">
                    <h2 class="mb-4 text-lg font-semibold text-foreground">Select Check-In Method</h2>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <button
                            @click="setCheckInType('ticket')"
                            :class="[
                                'rounded border-2 p-4 text-left transition-all cursor-pointer',
                                checkInType === 'ticket'
                                    ? 'border-primary bg-primary/20 text-blue-700'
                                    : 'border-gray-200 bg-white/40 text-foreground/85 hover:border-gray-300'
                            ]"
                        >
                            <div class="flex items-center">
                                <Ticket class="mr-3 h-6 w-6" />
                                <div>
                                    <h3 class="font-semibold">Ticket Number</h3>
                                    <p class="text-sm opacity-75">Check in individual ticket holders</p>
                                </div>
                            </div>
                        </button>

                        <button
                            @click="setCheckInType('order')"
                            :class="[
                                'rounded border-2 p-4 text-left transition-all cursor-pointer',
                                checkInType === 'order'
                                    ? 'border-primary bg-blue-50 text-blue-700'
                                    : 'border-gray-200 bg-white/40 text-foreground/85 hover:border-gray-300'
                            ]"
                        >
                            <div class="flex items-center">
                                <ShoppingBag class="mr-3 h-6 w-6" />
                                <div>
                                    <h3 class="font-semibold">Order Number</h3>
                                    <p class="text-sm opacity-75">Check in bulk ticket orders</p>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Search Form -->
                <div class="mb-8 rounded-all  bg-whites p-6">
                    <h2 class="mb-4 text-lg font-semibold text-foreground">
                        {{ getSearchFormTitle() }}
                    </h2>

                    <!-- Ticket Number Search -->
                    <form v-if="checkInType === 'ticket'" @submit.prevent="searchTicket" class="space-y-4">
                        <div>
                            <label for="ticketNumber" class="mb-2 block text-sm font-medium text-foreground/85">
                                Ticket Number
                            </label>
                            <div class="relative">
                                <input
                                    id="ticketNumber"
                                    v-model="searchValue"
                                    type="text"
                                    placeholder="Enter ticket number (e.g., TKT-ABC123XYZ0)"
                                    class="w-full rounded  px-4 py-3 text-lg transition-colors focus:border-primary focus:ring-2 focus:ring-blue-200"
                                    :disabled="isSearching"
                                    required
                                />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                    <Ticket class="h-5 w-5 text-gray-400" />
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-foreground/75">Ticket numbers start with "TKT-" followed by 12 characters</p>
                        </div>
                        <button
                            type="submit"
                            :disabled="isSearching || !searchValue.trim()"
                            class="w-full rounded bg-primary cursor-pointer px-6 py-3 font-semibold text-white transition-colors hover:bg-blue-700 disabled:bg-gray-400"
                        >
                            <div v-if="isSearching" class="flex items-center justify-center">
                                <Loader2 class="mr-2 h-5 w-5 animate-spin" />
                                Searching...
                            </div>
                            <div v-else class="flex items-center justify-center">
                                <Search class="mr-2 h-5 w-5" />
                                Search Ticket
                            </div>
                        </button>
                    </form>

                    <!-- Order Number Search -->
                    <form v-else-if="checkInType === 'order'" @submit.prevent="searchOrder" class="space-y-4">
                        <div>
                            <label for="orderNumber" class="mb-2 block text-sm font-medium text-foreground/85">
                                Order Number
                            </label>
                            <div class="relative">
                                <input
                                    id="orderNumber"
                                    v-model="searchValue"
                                    type="text"
                                    placeholder="Enter order number (e.g., ORD-ABC1234567)"
                                    class="w-full rounded  px-4 py-3 text-lg transition-colors focus:border-primary focus:ring-2 focus:ring-blue-200"
                                    :disabled="isSearching"
                                    required
                                />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                    <ShoppingBag class="h-5 w-5 text-gray-400" />
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-foreground/75">Order numbers start with "ORD-" followed by 10 characters</p>
                        </div>
                        <button
                            type="submit"
                            :disabled="isSearching || !searchValue.trim()"
                            class="w-full rounded bg-green-600 px-6 py-3 font-semibold text-white transition-colors hover:bg-blue-700 disabled:bg-gray-400"
                        >
                            <div v-if="isSearching" class="flex items-center justify-center">
                                <Loader2 class="mr-2 h-5 w-5 animate-spin" />
                                Searching...
                            </div>
                            <div v-else class="flex items-center justify-center">
                                <Search class="mr-2 h-5 w-5" />
                                Search Order
                            </div>
                        </button>
                    </form>

                </div>

                <!-- Error Message -->
                <div v-if="errorMessage" class="mb-8 rounded border border-red-200 bg-red-50 p-6">
                    <div class="flex items-center">
                        <AlertCircle class="mr-3 h-6 w-6 text-red-500" />
                        <div>
                            <h3 class="text-lg font-semibold text-red-800">Error</h3>
                            <p class="text-red-600">{{ errorMessage }}</p>
                        </div>
                    </div>
                </div>

                <!-- Results -->
                <div v-if="searchResults">
                    <!-- Single Ticket Result -->
                    <div v-if="searchResults.type === 'ticket'" class="rounded bg-whites ">
                        <TicketCard
                            :ticket="searchResults.data"
                            :can-check-in="true"
                            @check-in="checkInTicket"
                        />
                    </div>

                    <!-- Order Results -->
                    <div v-else-if="searchResults.type === 'order'" class="space-y-6">
                        <div class="rounded-all  bg-whites p-6">
                            <h3 class="mb-4 text-xl font-semibold text-foreground">Order: {{ searchResults.data.order_number }}</h3>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <span class="text-foreground/80">Total Tickets:</span>
                                    <span class="ml-2 font-medium">{{ searchResults.data.tickets.length }}</span>
                                </div>
                                <div>
                                    <span class="text-foreground/80">Event:</span>
                                    <span class="ml-2 font-medium">{{ searchResults.data.event.title }}</span>
                                </div>
                                <div>
                                    <span class="text-foreground/80">Buyer:</span>
                                    <span class="ml-2 font-medium">{{ searchResults.data.buyer_email }}</span>
                                </div>
                                <div>
                                    <span class="text-foreground/80">Status:</span>
                                    <span class="ml-2 font-medium">{{ searchResults.data.status }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-foreground">Tickets in this Order</h4>
                            <div v-for="ticket in searchResults.data.tickets" :key="ticket.id" class="rounded-all bg-whites">
                                <TicketCard
                                    :ticket="ticket"
                                    :can-check-in="true"
                                    @check-in="checkInTicket"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import TicketCard from '@/components/TicketCard.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { AlertCircle, Calendar, Loader2, MapPin, Building, Search, ShoppingBag, Ticket } from 'lucide-vue-next';
import { ref } from 'vue';

// Configure axios to include CSRF token for web requests
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;

type CheckInType = 'ticket' | 'order';

interface TicketType {
    id: number;
    name: string;
    description: string;
}

interface Organizer {
    id: number;
    name: string;
}

interface Event {
    id: number;
    title: string;
    start_date: string;
    start_time: string;
    venue_name: string | null;
    organizer: Organizer | null;
}

interface Order {
    id: number;
    order_number: string;
    buyer_email: string;
    status: string;
    event: Event;
    tickets: TicketData[];
}

interface TicketData {
    id: number;
    ticket_number: string;
    attendee_name: string;
    attendee_email: string;
    price: string;
    status: string;
    used_at: string | null;
    event: Event;
    ticket_type: TicketType | null;
    order: { order_number: string; status: string };
    custom_field_responses?: { label: string; value: string }[];
}

interface SearchResults {
    type: 'ticket' | 'order';
    data: TicketData | Order;
}

interface SelectedEvent {
    id: number;
    title: string;
    start_date: string;
    start_time: string;
    venue_name: string | null;
    organizer: {
        id: number;
        name: string;
    } | null;
}

// Props
const { selectedEvent } = defineProps<{
    selectedEvent?: SelectedEvent | null;
}>();

// Authentication is handled by route middleware

const checkInType = ref<CheckInType>('ticket');
const searchValue = ref('');
const isSearching = ref(false);
const errorMessage = ref('');
const searchResults = ref<SearchResults | null>(null);

const setCheckInType = (type: CheckInType) => {
    checkInType.value = type;
    searchValue.value = '';
    errorMessage.value = '';
    searchResults.value = null;
};

const getSearchFormTitle = (): string => {
    const titles = {
        ticket: 'Search by Ticket Number',
        order: 'Search by Order Number',
    };
    return titles[checkInType.value];
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatTime = (timeString: string): string => {
    return new Date(`2000-01-01T${timeString}`).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
};

const searchTicket = async () => {
    if (!searchValue.value.trim() || isSearching.value) return;

    isSearching.value = true;
    errorMessage.value = '';
    searchResults.value = null;

    try {
        const response = await axios.post('/api/tickets/verify-by-number', {
            ticket_number: searchValue.value.trim(),
        });

        if (response.data.success) {
            searchResults.value = {
                type: 'ticket',
                data: response.data.ticket,
            };
        }
    } catch (error: any) {
        errorMessage.value = error.response?.data?.message || 'Failed to find ticket. Please check the ticket number and try again.';
    } finally {
        isSearching.value = false;
    }
};

const searchOrder = async () => {
    if (!searchValue.value.trim() || isSearching.value) return;

    isSearching.value = true;
    errorMessage.value = '';
    searchResults.value = null;

    try {
        const response = await axios.get(`/api/orders/${searchValue.value.trim()}`);

        if (response.data.success) {
            searchResults.value = {
                type: 'order',
                data: response.data.order,
            };
        }
    } catch (error: any) {
        errorMessage.value = error.response?.data?.message || 'Failed to find order. Please check the order number and try again.';
    } finally {
        isSearching.value = false;
    }
};

const checkInTicket = async (ticket: TicketData) => {
    try {
        const response = await axios.put(`/api/tickets/${ticket.id}/mark-used`);

        if (response.data.success) {
            // Update the ticket status in the results
            ticket.status = 'used';
            ticket.used_at = response.data.ticket.used_at;
        }
    } catch (error: any) {
        alert(error.response?.data?.message || 'Failed to check in ticket. Please try again.');
    }
};

</script>
