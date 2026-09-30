<template>
    <div class="overflow-hidden rounded-all  bg-whites">
        <!-- Status Header -->
        <div :class="getStatusHeaderClass(rsvp.status, rsvp.checked_in)" class="px-6 py-4">
            <div class="flex items-center justify-center">
                <CheckCircle v-if="rsvp.checked_in" class="mr-3 h-8 w-8" />
                <UserCheck v-else-if="rsvp.status === 'attending'" class="mr-3 h-8 w-8" />
                <AlertCircle v-else class="mr-3 h-8 w-8" />
                <div class="text-center">
                    <h2 class="text-xl font-bold">{{ getStatusTitle(rsvp.status, rsvp.checked_in) }}</h2>
                    <p class="text-sm opacity-90">{{ getStatusMessage(rsvp.status, rsvp.checked_in) }}</p>
                </div>
            </div>
        </div>

        <!-- RSVP Details -->
        <div class="p-6">
            <div class="space-y-6">
                <!-- Guest Code -->
                <div class="text-center">
                    <p class="text-sm text-gray-600">Guest Code</p>
                    <p class="font-mono text-lg font-bold text-gray-900">{{ rsvp.guest_code }}</p>
                </div>

                <!-- Guest Info -->
                <div>
                    <h3 class="mb-3 text-lg font-semibold text-gray-900">Guest Information</h3>
                    <div class="space-y-2 rounded bg-gray-400/10 p-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Name:</span>
                            <span class="font-medium text-gray-900">{{ rsvp.guest_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Email:</span>
                            <span class="font-medium text-gray-900">{{ rsvp.email }}</span>
                        </div>
                        <div v-if="rsvp.phone" class="flex justify-between">
                            <span class="text-gray-600">Phone:</span>
                            <span class="font-medium text-gray-900">{{ rsvp.phone }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">RSVP Status:</span>
                            <span :class="getRsvpStatusClass(rsvp.status)" class="rounded px-2 py-1 text-sm font-medium">
                                {{ formatRsvpStatus(rsvp.status) }}
                            </span>
                        </div>
                        <div v-if="rsvp.referral_source" class="flex justify-between">
                            <span class="text-gray-600">How they heard:</span>
                            <span class="font-medium text-gray-900">{{ rsvp.referral_source }}</span>
                        </div>
                    </div>
                </div>

                <!-- Event Info -->
                <div>
                    <h3 class="mb-3 text-lg font-semibold text-gray-900">Event Information</h3>
                    <div class="space-y-2 rounded bg-gray-400/10 p-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Event:</span>
                            <span class="font-medium text-gray-900">{{ rsvp.event.title }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Date:</span>
                            <span class="font-medium text-gray-900">{{ formatDate(rsvp.event.start_date) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Time:</span>
                            <span class="font-medium text-gray-900">{{ formatTime(rsvp.event.start_time) }}</span>
                        </div>
                        <div v-if="rsvp.event.venue_name" class="flex justify-between">
                            <span class="text-gray-600">Venue:</span>
                            <span class="font-medium text-gray-900">{{ rsvp.event.venue_name }}</span>
                        </div>
                        <div v-if="rsvp.event.organizer" class="flex justify-between">
                            <span class="text-gray-600">Organizer:</span>
                            <span class="font-medium text-gray-900">{{ rsvp.event.organizer.name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Check-in Status -->
                <div>
                    <h3 class="mb-3 text-lg font-semibold text-gray-900">Check-in Status</h3>
                    <div class="space-y-2 rounded bg-gray-400/10 p-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Checked In:</span>
                            <span :class="rsvp.checked_in ? 'text-green-600' : 'text-gray-600'" class="font-medium">
                                {{ rsvp.checked_in ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        <div v-if="rsvp.checked_in_at" class="flex justify-between">
                            <span class="text-gray-600">Check-in Time:</span>
                            <span class="font-medium text-gray-900">{{ formatDateTime(rsvp.checked_in_at) }}</span>
                        </div>
                        <div v-if="rsvp.checked_in_by" class="flex justify-between">
                            <span class="text-gray-600">Checked in by:</span>
                            <span class="font-medium text-gray-900">{{ rsvp.checked_in_by.name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="rsvp.notes">
                    <h3 class="mb-3 text-lg font-semibold text-gray-900">Notes</h3>
                    <div class="rounded bg-gray-400/10 p-4">
                        <p class="text-gray-700">{{ rsvp.notes }}</p>
                    </div>
                </div>

                <!-- Check-in Button -->
                <div v-if="canCheckIn && !rsvp.checked_in && rsvp.status === 'attending'" class="border-t border-gray-200 pt-4">
                    <button
                        @click="$emit('checkIn', rsvp)"
                        :disabled="isProcessing"
                        class="w-full rounded cursor-pointer bg-green-600 px-6 py-3 font-semibold text-white transition-colors hover:bg-green-700 disabled:bg-gray-400"
                    >
                        <div v-if="isProcessing" class="flex items-center justify-center">
                            <Loader2 class="mr-2 h-5 w-5 animate-spin" />
                            Checking In...
                        </div>
                        <span v-else class="flex items-center justify-center">
                            <UserCheck class="mr-2 h-5 w-5" />
                            Check In Guest
                        </span>
                    </button>
                </div>

                <!-- Already checked in message -->
                <div v-else-if="rsvp.checked_in" class="border-t border-gray-200 pt-4">
                    <div class="rounded-lg bg-blue-50 p-4 text-center">
                        <CheckCircle class="mx-auto mb-2 h-8 w-8 text-blue-600" />
                        <p class="font-semibold text-blue-800">Guest Already Checked In</p>
                        <p class="text-sm text-blue-600">{{ formatDateTime(rsvp.checked_in_at) }}</p>
                        <p v-if="rsvp.checked_in_by" class="text-sm text-blue-600">by {{ rsvp.checked_in_by.name }}</p>
                    </div>
                </div>

                <!-- Cannot check in message -->
                <div v-else-if="rsvp.status !== 'attending'" class="border-t border-gray-200 pt-4">
                    <div class="rounded-lg bg-yellow-50 p-4 text-center">
                        <AlertCircle class="mx-auto mb-2 h-8 w-8 text-yellow-600" />
                        <p class="font-semibold text-yellow-800">Cannot Check In</p>
                        <p class="text-sm text-yellow-600">Guest status is "{{ formatRsvpStatus(rsvp.status) }}"</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { AlertCircle, CheckCircle, Loader2, UserCheck } from 'lucide-vue-next';
import { ref } from 'vue';

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

interface RsvpData {
    id: number;
    guest_code: string;
    guest_name: string;
    email: string;
    phone: string;
    status: string;
    checked_in: boolean;
    checked_in_at: string | null;
    checked_in_by: { name: string } | null;
    notes: string;
    referral_source: string;
    event: Event;
}

defineProps<{
    rsvp: RsvpData;
    canCheckIn?: boolean;
}>();

defineEmits<{
    checkIn: [rsvp: RsvpData];
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

const formatRsvpStatus = (status: string): string => {
    const statusMap = {
        attending: 'Attending',
        maybe: 'Maybe',
        not_attending: 'Not Attending',
    };
    return statusMap[status as keyof typeof statusMap] || status.charAt(0).toUpperCase() + status.slice(1);
};

const getRsvpStatusClass = (status: string): string => {
    const statusClasses = {
        attending: 'bg-green-100 text-green-800',
        maybe: 'bg-yellow-100 text-yellow-800',
        not_attending: 'bg-red-100 text-red-800',
    };
    return statusClasses[status as keyof typeof statusClasses] || 'bg-gray-100 text-gray-800';
};

const getStatusHeaderClass = (status: string, checkedIn: boolean): string => {
    if (checkedIn) {
        return 'bg-blue-600 text-white';
    }

    const statusClasses = {
        attending: 'bg-green-600 text-white',
        maybe: 'bg-yellow-600 text-white',
        not_attending: 'bg-red-600 text-white',
    };
    return statusClasses[status as keyof typeof statusClasses] || 'bg-gray-600 text-white';
};

const getStatusTitle = (status: string, checkedIn: boolean): string => {
    if (checkedIn) {
        return 'Guest Checked In';
    }

    const titles = {
        attending: 'Valid RSVP',
        maybe: 'Maybe Attending',
        not_attending: 'Not Attending',
    };
    return titles[status as keyof typeof titles] || 'RSVP Status Unknown';
};

const getStatusMessage = (status: string, checkedIn: boolean): string => {
    if (checkedIn) {
        return 'This guest has been successfully checked in';
    }

    const messages = {
        attending: 'Guest confirmed attendance and can be checked in',
        maybe: 'Guest response is tentative',
        not_attending: 'Guest declined attendance',
    };
    return messages[status as keyof typeof messages] || 'Unknown RSVP status';
};
</script>
