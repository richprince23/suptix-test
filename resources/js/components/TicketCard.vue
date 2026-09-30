<template>
    <div class="overflow-hidden rounded   bg-whites">
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
                <!-- Ticket Number -->
                <div class="text-center">
                    <p class="text-sm text-gray-600">Ticket Number</p>
                    <p class="font-mono text-lg font-bold text-gray-900">{{ ticket.ticket_number }}</p>
                </div>

                <!-- Attendee Info -->
                <div>
                    <h3 class="mb-3 text-lg font-semibold text-gray-900">Attendee Information</h3>
                    <div class="space-y-2 rounded bg-gray-400/10 p-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Name:</span>
                            <span class="font-medium text-gray-900">{{ ticket.attendee_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Email:</span>
                            <span class="font-medium text-gray-900">{{ ticket.attendee_email }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Ticket Type:</span>
                            <span class="font-medium text-gray-900">{{ ticket.ticket_type?.name || 'Standard' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Price:</span>
                            <span class="font-medium text-gray-900">GHS {{ ticket.price }}</span>
                        </div>
                    </div>

                    <!-- Custom field responses -->
                    <template v-if="ticket.custom_field_responses && ticket.custom_field_responses.length > 0">
                        <h3 class="mb-3 mt-4 text-lg font-semibold text-gray-900">Additional Information</h3>
                        <div class="space-y-2 rounded bg-gray-400/10 p-4">
                            <div
                                v-for="response in ticket.custom_field_responses"
                                :key="response.label"
                                class="flex justify-between gap-4"
                            >
                                <span class="text-gray-600 shrink-0">{{ response.label }}:</span>
                                <span class="font-medium text-gray-900 text-right">{{ response.value }}</span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Event Info -->
                <div>
                    <h3 class="mb-3 text-lg font-semibold text-gray-900">Event Information</h3>
                    <div class="space-y-2 rounded bg-gray-400/10 p-4">
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
                        <div v-if="ticket.event.organizer" class="flex justify-between">
                            <span class="text-gray-600">Organizer:</span>
                            <span class="font-medium text-gray-900">{{ ticket.event.organizer.name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Ticket Status Details -->
                <div>
                    <h3 class="mb-3 text-lg font-semibold text-gray-900">Ticket Status</h3>
                    <div class="space-y-2 rounded bg-gray-400/10 p-4">
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

                <!-- Check-in Button -->
                <div v-if="canCheckIn && ticket.status === 'active'" class="border-t border-blue-200 pt-4">
                    <button
                        @click="$emit('checkIn', ticket)"
                        :disabled="isProcessing"
                        class="w-full rounded cursor-pointer bg-green-600 px-6 py-3 font-semibold text-white transition-colors hover:bg-green-700 disabled:bg-gray-400"
                    >
                        <div v-if="isProcessing" class="flex items-center justify-center">
                            <Loader2 class="mr-2 h-5 w-5 animate-spin" />
                            Checking In...
                        </div>
                        <span v-else class="flex items-center justify-center">
                            <UserCheck class="mr-2 h-5 w-5" />
                            Check In Attendee
                        </span>
                    </button>
                </div>

                <!-- Already checked in message -->
                <div v-else-if="ticket.status === 'used'" class="border-t border-gray-200 pt-4">
                    <div class="rounded bg-blue-400/10 p-4 text-center">
                        <CheckCircle class="mx-auto mb-2 h-8 w-8 text-blue-600" />
                        <p class="font-semibold text-blue-800">Attendee Already Checked In</p>
                        <p class="text-sm text-blue-600">{{ formatDateTime(ticket.used_at) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { AlertCircle, CheckCircle, Loader2, UserCheck, XCircle } from 'lucide-vue-next';
import { ref } from 'vue';

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

defineProps<{
    ticket: TicketData;
    canCheckIn?: boolean;
}>();

defineEmits<{
    checkIn: [ticket: TicketData];
}>();

const isProcessing = ref(false);

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
        active: 'This ticket is valid and ready for check-in',
        used: 'This ticket has already been used for entry',
        cancelled: 'This ticket has been cancelled and is not valid',
    };
    return messages[status as keyof typeof messages] || 'Unknown ticket status';
};
</script>
