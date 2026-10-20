<template>
    <Head title="Verify Ticket"> </Head>
    <div class="min-h-screen bg-white">
        <!-- Navigation -->
        <NavBar class="bg-black" />

        <!-- Main Content -->
        <div class="pt-20 pb-16">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 text-center">
                    <h1 class="mb-4 text-3xl font-bold text-gray-900 md:text-4xl">Ticket Verification</h1>
                    <p class="text-lg text-gray-600">Ticket Number: {{ ticketNumber }}</p>
                </div>

                <!-- Search Form -->
                <div v-if="!ticket" class="mb-8 rounded-lg border bg-white p-6 shadow-md">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Search Another Ticket</h2>
                    <form @submit.prevent="searchTicket" class="space-y-4">
                        <div>
                            <label for="searchTicketNumber" class="mb-2 block text-sm font-medium text-gray-700">
                                Ticket Number
                            </label>
                            <div class="relative">
                                <input
                                    id="searchTicketNumber"
                                    v-model="searchTicketNumber"
                                    type="text"
                                    placeholder="Enter ticket number (e.g., TKT-ABC123XYZ0)"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-lg transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                    :disabled="isSearching"
                                    required
                                />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                    <Search class="h-5 w-5 text-gray-400" />
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Ticket numbers start with "TKT-" followed by 10 characters</p>
                        </div>

                        <button
                            type="submit"
                            :disabled="isSearching || !searchTicketNumber.trim()"
                            class="w-full rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white transition-colors hover:bg-blue-700 disabled:bg-gray-400"
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
                </div>

                <!-- Ticket Not Found -->
                <div v-if="!ticket" class="rounded-lg border border-red-200 bg-red-50 p-8 text-center">
                    <AlertCircle class="mx-auto mb-4 h-16 w-16 text-red-500" />
                    <h2 class="mb-2 text-xl font-semibold text-red-800">Ticket Not Found</h2>
                    <p class="text-red-600">
                        The ticket number <span class="font-mono">{{ ticketNumber }}</span> could not be found in our system.
                    </p>
                </div>

                <!-- Valid Ticket -->
                <div v-else class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-md">
                    <!-- Status Header -->
                    <div :class="getStatusHeaderClass(ticket.status)" class="px-6 py-4">
                        <div class="flex items-center justify-center">
                            <CheckCircle v-if="ticket.status === 'active'" class="mr-3 h-8 w-8" />
                            <XCircle v-else-if="ticket.status === 'used'" class="mr-3 h-8 w-8" />
                            <AlertCircle v-else class="mr-3 h-8 w-8" />
                            <div class="text-center">
                                <h2 class="text-xl font-bold">{{ getStatusTitle(ticket.status) }}</h2>
                                <p class="text-sm opacity-90">{{ getStatusMessage(ticket.status) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Ticket Details -->
                    <div class="p-6">
                        <div class="space-y-6">
                            <!-- Attendee Info -->
                            <div>
                                <h3 class="mb-3 text-lg font-semibold text-gray-900">Attendee Information</h3>
                                <div class="space-y-2 rounded-lg bg-gray-50 p-4">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Name:</span>
                                        <span class="font-medium text-gray-900">{{ ticket.attendee_name }}</span>
                                    </div>
                                    <div v-if="ticket.attendee_email" class="flex justify-between">
                                        <span class="text-gray-600">Email:</span>
                                        <span class="font-medium text-gray-900">{{ ticket.attendee_email }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Ticket Type:</span>
                                        <span class="font-medium text-gray-900">{{ ticket.ticket_type?.name || 'Standard' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Event Info -->
                            <div>
                                <h3 class="mb-3 text-lg font-semibold text-gray-900">Event Information</h3>
                                <div class="space-y-2 rounded-lg bg-gray-50 p-4">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Event:</span>
                                        <span class="font-medium text-gray-900">{{ ticket.event.title }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Date:</span>
                                        <span class="font-medium text-gray-900">{{ formatDate(ticket.event.start_date) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Time:</span>
                                        <span class="font-medium text-gray-900">{{ formatTime(ticket.event.start_time) }}</span>
                                    </div>
                                    <div v-if="ticket.event.venue_name" class="flex justify-between">
                                        <span class="text-gray-600">Venue:</span>
                                        <span class="font-medium text-gray-900">{{ ticket.event.venue_name }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Ticket Status Details -->
                            <div>
                                <h3 class="mb-3 text-lg font-semibold text-gray-900">Ticket Status</h3>
                                <div class="space-y-2 rounded-lg bg-gray-50 p-4">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Order Number:</span>
                                        <span class="font-medium text-gray-900">{{ ticket.order.order_number }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Status:</span>
                                        <span :class="getStatusClass(ticket.status)" class="rounded px-2 py-1 text-sm font-medium">
                                            {{ formatStatus(ticket.status) }}
                                        </span>
                                    </div>
                                    <div v-if="ticket.used_at" class="flex justify-between">
                                        <span class="text-gray-600">Used At:</span>
                                        <span class="font-medium text-gray-900">{{ formatDateTime(ticket.used_at) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="border-t border-gray-200 pt-4 space-y-3">
                                <!-- Mark as Used Button (for authenticated organizers only) -->
                                <div v-if="ticket.status === 'active' && canMarkAsUsed">
                                    <button
                                        @click="markAsUsed"
                                        :disabled="isMarkingUsed"
                                        class="w-full rounded-lg bg-green-600 px-6 py-3 font-semibold text-white transition-colors hover:bg-green-700 disabled:bg-gray-400"
                                    >
                                        <div v-if="isMarkingUsed" class="flex items-center justify-center">
                                            <Loader2 class="mr-2 h-5 w-5 animate-spin" />
                                            Processing...
                                        </div>
                                        <span v-else>Mark as Used</span>
                                    </button>
                                </div>

                                <!-- Search Another Ticket Button -->
                                <div>
                                    <form @submit.prevent="searchTicket" class="space-y-3">
                                        <input
                                            v-model="searchTicketNumber"
                                            type="text"
                                            placeholder="Enter another ticket number"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                            :disabled="isSearching"
                                        />
                                        <button
                                            type="submit"
                                            :disabled="isSearching || !searchTicketNumber.trim()"
                                            class="w-full rounded-lg bg-blue-600 px-6 py-2 text-sm font-semibold text-white transition-colors hover:bg-blue-700 disabled:bg-gray-400"
                                        >
                                            <div v-if="isSearching" class="flex items-center justify-center">
                                                <Loader2 class="mr-2 h-4 w-4 animate-spin" />
                                                Searching...
                                            </div>
                                            <div v-else class="flex items-center justify-center">
                                                <Search class="mr-2 h-4 w-4" />
                                                Search Another Ticket
                                            </div>
                                        </button>
                                    </form>
                                </div>
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
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { AlertCircle, CheckCircle, Loader2, Search, XCircle } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface TicketType {
    name: string;
    description: string;
}

interface Organizer {
    name: string;
}

interface Event {
    title: string;
    start_date: string;
    start_time: string;
    venue_name: string | null;
    organizer: Organizer | null;
}

interface Order {
    order_number: string;
    status: string;
}

interface Ticket {
    id: number;
    ticket_number: string;
    attendee_name: string;
    attendee_email?: string;
    price: string;
    status: string;
    used_at: string | null;
    event: Event;
    ticket_type: TicketType | null;
    order: Order;
}

const props = defineProps<{
    ticket: Ticket | null;
    ticketNumber: string;
    canMarkAsUsed: boolean;
}>();

// Local copy so the status can change after check-in without mutating the prop
const ticket = ref<Ticket | null>(props.ticket);
watch(() => props.ticket, (value) => {
    ticket.value = value;
});

const isMarkingUsed = ref(false);
const searchTicketNumber = ref(props.ticketNumber || '');
const isSearching = ref(false);

const searchTicket = () => {
    if (!searchTicketNumber.value.trim() || isSearching.value) return;

    isSearching.value = true;

    // Navigate to the verification page with the new ticket number
    router.visit(`/verify-ticket/${searchTicketNumber.value.trim()}`, {
        onFinish: () => {
            isSearching.value = false;
        }
    });
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

const formatDateTime = (dateTimeString: string): string => {
    return new Date(dateTimeString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
};

const formatStatus = (status: string): string => {
    return status.charAt(0).toUpperCase() + status.slice(1);
};

const getStatusClass = (status: string): string => {
    const statusClasses = {
        active: 'bg-green-100 text-green-800',
        used: 'bg-blue-100 text-blue-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return statusClasses[status as keyof typeof statusClasses] || 'bg-gray-100 text-gray-800';
};

const getStatusHeaderClass = (status: string): string => {
    const statusClasses = {
        active: 'bg-green-600 text-white',
        used: 'bg-blue-600 text-white',
        cancelled: 'bg-red-600 text-white',
    };
    return statusClasses[status as keyof typeof statusClasses] || 'bg-gray-600 text-white';
};

const getStatusTitle = (status: string): string => {
    const titles = {
        active: 'Valid Ticket',
        used: 'Ticket Already Used',
        cancelled: 'Ticket Cancelled',
    };
    return titles[status as keyof typeof titles] || 'Unknown Status';
};

const getStatusMessage = (status: string): string => {
    const messages = {
        active: 'This ticket is valid and ready for entry',
        used: 'This ticket has already been used for entry',
        cancelled: 'This ticket has been cancelled and is not valid',
    };
    return messages[status as keyof typeof messages] || 'Unknown ticket status';
};

const markAsUsed = async () => {
    if (!ticket.value || isMarkingUsed.value) return;

    isMarkingUsed.value = true;

    try {
        const response = await axios.patch(`/api/tickets/${ticket.value.id}/mark-used`);

        if (response.data.success) {
            // Update the ticket status locally
            if (ticket.value) {
                ticket.value.status = 'used';
                ticket.value.used_at = response.data.ticket.used_at;
            }
        }
    } catch (error: any) {
        console.error('Error marking ticket as used:', error);
        alert('Failed to mark ticket as used. Please try again.');
    } finally {
        isMarkingUsed.value = false;
    }
};
</script>

<style scoped>
/* Add any custom styles here */
</style>
