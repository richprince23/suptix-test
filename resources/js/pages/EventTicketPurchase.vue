<template>
    <Head :title="props.event?.title ? `Get Tickets - ${props.event.title}` : 'Get Tickets'" />
    <section class="relative h-auto overflow-hidden mb-12">
        <NavBar text-color="foreground" class="fixed z-40 w-full border-b-pink-600/10 bg-black shadow-md shadow-black/10 backdrop-blur" />
    </section>
    <main class="w-full bg-white">
        <div class="mx-auto max-w-7xl bg-white px-4 py-8 sm:px-8 sm:py-16 lg:px-16">
            <div class="mb-6">
                <Button variant="outline" @click="router.visit(route('events.index'))"
                    class="flex cursor-pointer items-center gap-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                    Back
                </Button>
            </div>
            <!-- Event Header -->
            <div class="mb-8 overflow-hidden rounded-lg bg-white shadow-md">
                <div class="flex flex-col lg:flex-row">
                    <!-- Event Image -->
                    <div class="lg:w-2/5">
                        <div class="relative bg-gray-200 h-64 sm:h-80 lg:h-96">
                            <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.title"
                                class="h-full w-full object-cover object-center" />
                            <div v-else class="h-full flex w-full items-center justify-center">
                                <svg class="h-16 w-16 sm:h-24 sm:w-24 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>

                            <!-- Status Badge -->
                            <div class="absolute top-4 right-4">
                                <span class="rounded-md px-3 py-1 text-sm font-medium" :class="statusClasses" >
                                    {{ event.status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Event Info -->
                    <div class="flex flex-col justify-between p-6 lg:w-3/5">
                        <h1 class="mb-4 text-2xl sm:text-3xl font-bold foreground leading-tight">{{ event.title }}</h1>

                        <div class="space-y-4">
                            <!-- Date and Time -->
                            <div class="flex items-center gap-3">
                                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <div>
                                    <div class="font-medium">{{ formatDate(event.start_date) }}</div>
                                    <div class="text-sm foreground/70">
                                        {{ formatTime(event.start_time!) }}
                                        <span v-if="event.end_time"> - {{ formatTime(event.end_time) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Venue -->
                            <div v-if="event.venue_name" class="flex items-center gap-3">
                                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <div>
                                    <div class="font-medium">{{ event.venue_name }}</div>
                                    <div v-if="event.venue_address" class="text-sm foreground/70">
                                        {{ event.venue_address }}
                                    </div>
                                </div>
                            </div>

                            <!-- Organizer -->
                            <div v-if="event.organizer" class="flex items-center gap-3">
                                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <div>
                                    <div class="font-medium">{{ event.organizer.name }}</div>
                                    <div class="text-sm foreground/70">Event Organizer</div>
                                </div>
                            </div>

                            <!-- Event Type -->
                            <div v-if="event.event_type" class="flex items-center gap-3">
                                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                    </path>
                                </svg>
                                <div>
                                    <div class="font-medium">{{ event.event_type }}</div>
                                    <div class="text-sm foreground/70">Event Category</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Purchase Form -->
            <div class="rounded-lg bg-white p-4 sm:p-6 shadow-md">
                <!-- General Error Display -->
                <div v-if="errors.error" class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Purchase Error</h3>
                            <p class="mt-1 text-sm text-red-700">{{ errors.error }}</p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submitPurchase" class="space-y-8">
                    <!-- Honeypot field (hidden from users but visible to bots) -->
                    <input v-model="form.honeypot" type="text" style="display: none;" tabindex="-1" autocomplete="off" />

                    <!-- Step 1: Ticket Selection -->
                    <div>
                        <h2 class="mb-4 text-xl font-semibold">Select Tickets</h2>
                        <div v-if="ticketTypes.length === 0" class="rounded-lg border border-gray-200 bg-gray-50 p-6 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium foreground">No tickets available</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Ticket sales may not have started yet, may have ended, or all tickets may be sold out.
                            </p>
                        </div>
                        <TicketSelector v-else :ticket-types="ticketTypes" @update="handleTicketSelection" />
                    </div>

                    <!-- Step 2: Buyer Information -->
                    <div v-if="hasSelectedTickets" class="border-t pt-6">
                        <h2 class="mb-4 text-lg sm:text-xl font-semibold">Buyer Information</h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <Label for="buyer_email">Email Address *</Label>
                                <Input id="buyer_email" v-model="form.buyer_email" type="email" required
                                    placeholder="Enter your email" :class="{ 'border-red-500': errors.buyer_email }" />
                                <p v-if="errors.buyer_email" class="mt-1 text-sm text-red-600">
                                    {{ errors.buyer_email }}
                                </p>
                            </div>

                            <div>
                                <Label for="buyer_phone">Phone Number</Label>
                                <Input id="buyer_phone" v-model="form.buyer_phone" type="tel" required
                                    placeholder="Enter your phone number"
                                    :class="{ 'border-red-500': errors.buyer_phone }" />
                                <p v-if="errors.buyer_phone" class="mt-1 text-sm text-red-600">
                                    {{ errors.buyer_phone }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Attendee Information -->
                    <div v-if="hasSelectedTickets && form.buyer_email" class="border-t pt-6">
                        <h2 class="mb-4 text-lg sm:text-xl font-semibold">Attendee Information</h2>
                        <AttendeeForm :ticket-types="ticketTypes" :selections="ticketSelections"
                            :buyer-email="form.buyer_email" :errors="errors" @update="handleAttendeeUpdate" />
                    </div>

                    <!-- Order Summary -->
                    <div v-if="hasSelectedTickets" class="border-t pt-6">
                        <h2 class="mb-4 text-lg sm:text-xl font-semibold">Order Summary</h2>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <div class="space-y-2">
                                <div v-for="selection in ticketSelections" :key="selection.ticket_type_id"
                                    class="flex justify-between">
                                    <span>{{ getTicketTypeName(selection.ticket_type_id) }} ×
                                        {{ selection.quantity }}</span>
                                    <span>GHS
                                        {{ (getTicketTypePrice(selection.ticket_type_id) * selection.quantity) }}</span>
                                </div>
                            </div>

                            <!-- Discount Code -->
                            <div class="mt-4 border-t pt-4">
                                <div class="mb-2 flex gap-2">
                                    <Input v-model="discountCode" placeholder="Enter discount code"
                                        :disabled="validationDiscount.loading" class="flex-1" />
                                    <Button type="button" @click="validateDiscountCode"
                                        :disabled="!discountCode || validationDiscount.loading" variant="outline">
                                        {{ validationDiscount.loading ? 'Validating...' : 'Apply' }}
                                    </Button>
                                </div>

                                <div v-if="validationDiscount.error" class="text-sm text-red-600">
                                    {{ validationDiscount.error }}
                                </div>

                                <div v-if="appliedDiscount"
                                    class="flex items-center justify-between text-sm text-green-600">
                                    <span>{{ appliedDiscount.name }} ({{
                                        appliedDiscount.type === 'percentage' ? appliedDiscount.value + '%' : 'GHS ' + appliedDiscount.value
                                    }})</span>
                                    <div class="flex items-center gap-2">
                                        <span>-GHS {{ appliedDiscount.amount }}</span>
                                        <Button type="button" @click="clearDiscount" variant="ghost" size="sm"
                                            class="cursor-pointer p-1 text-red-500 hover:text-red-700">
                                            ✕
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 border-t pt-4">
                                <div v-if="appliedDiscount" class="mb-2 flex justify-between text-sm">
                                    <span>Subtotal:</span>
                                    <span>GHS {{ subtotalAmount }}</span>
                                </div>
                                <div v-if="appliedDiscount" class="mb-2 flex justify-between text-sm text-green-600">
                                    <span>Discount:</span>
                                    <span>-GHS {{ appliedDiscount.amount }}</span>
                                </div>
                                <div class="flex justify-between text-lg font-bold">
                                    <span>Total:</span>
                                    <span>GHS {{ finalAmount }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div v-if="hasSelectedTickets && canSubmit" class="text-center">
                        <Button type="submit" size="lg" :disabled="processing"
                            class="w-full cursor-pointer border bg-primary px-6 py-3 text-white transition-colors hover:from-blue-700 hover:to-purple-700 font-semibold sm:w-auto sm:px-12">
                            {{ processing ? 'Processing...' : 'Purchase Tickets' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</template>

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, reactive, ref } from 'vue';

import AttendeeForm from '@/components/AttendeeForm.vue';
import TicketSelector from '@/components/TicketSelector.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import NavBar from '@/components/NavBar.vue';

const statusClasses = computed(() => {
    switch (props.event.status) {
        case 'published':
            return 'bg-green-100 text-green-800';
        case 'draft':
            return 'bg-gray-100 text-gray-800';
        case 'live':
            return 'bg-red-600 text-white animate-pulse';
        case 'canceled':
            return 'bg-orange-100 text-black-800 line-through';
        default:
            return 'bg-gray-100 text-gray-800';
    }
});

interface Organizer {
    id: number;
    name: string;
}

interface TicketType {
    id: number;
    name: string;
    description?: string;
    price: number;
    quantity: number;
    sales_start?: string;
    sales_end?: string;
}

interface Event {
    id: number;
    title: string;
    description?: string;
    cover_image?: string;
    cover_image_url?: string;
    start_date: string;
    start_time?: string;
    end_date?: string;
    end_time?: string;
    event_type?: string;
    is_rsvp: boolean;
    status: string;
    tags?: string[];
    venue_name?: string;
    venue_address?: string;
    organizer?: Organizer;
    ticketTypes?: TicketType[];
}

interface TicketType {
    id: number;
    name: string;
    description?: string;
    price: number;
    quantity: number;
    sales_start?: string;
    sales_end?: string;
}

interface TicketSelection {
    ticket_type_id: number;
    quantity: number;
}

interface TicketGroup {
    ticket_type_id: number;
    quantity: number;
    attendees: Array<{
        name: string;
        email: string;
    }>;
}

const props = defineProps<{
    event: Event;
    ticketTypes: TicketType[];
    errors?: Record<string, string>;
}>();

const processing = ref(false);
const ticketSelections = ref<TicketSelection[]>([]);
const attendeeGroups = ref<TicketGroup[]>([]);
const discountCode = ref('');
const appliedDiscount = ref<any>(null);
const validationDiscount = reactive({
    loading: false,
    error: '',
});

const form = reactive({
    buyer_email: '',
    buyer_phone: '',
    tickets: [] as TicketGroup[],
    discount_id: null as number | null,
    honeypot: '', // Hidden field for spam protection
});

const errors = computed(() => props.errors || {});

const hasSelectedTickets = computed(() => {
    return ticketSelections.value.length > 0;
});

const subtotalAmount = computed(() => {
    return ticketSelections.value.reduce((total, selection) => {
        const ticketType = props.ticketTypes.find((t) => t.id === selection.ticket_type_id);
        if (ticketType) {
            return total + ticketType.price * selection.quantity;
        }
        return total;
    }, 0);
});

const finalAmount = computed(() => {
    if (appliedDiscount.value) {
        return Math.max(0, subtotalAmount.value - appliedDiscount.value.amount);
    }
    return subtotalAmount.value;
});

const canSubmit = computed(() => {
    return (
        hasSelectedTickets.value &&
        form.buyer_email &&
        attendeeGroups.value.length > 0 &&
        attendeeGroups.value.every((group) => group.attendees.every((attendee) => attendee.name.trim() !== ''))
    );
});

const getTicketTypeName = (ticketTypeId: number): string => {
    const ticketType = props.ticketTypes.find((t) => t.id === ticketTypeId);
    return ticketType?.name || '';
};

const getTicketTypePrice = (ticketTypeId: number): number => {
    const ticketType = props.ticketTypes.find((t) => t.id === ticketTypeId);
    return ticketType?.price || 0;
};

const handleTicketSelection = (selections: TicketSelection[]) => {
    ticketSelections.value = selections;
};

const handleAttendeeUpdate = (groups: TicketGroup[]) => {
    attendeeGroups.value = groups;
    form.tickets = groups;
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

const clearDiscount = () => {
    appliedDiscount.value = null;
    form.discount_id = null;
    discountCode.value = '';
    validationDiscount.error = '';
};

const validateDiscountCode = async () => {
    if (!discountCode.value.trim()) return;

    validationDiscount.loading = true;
    validationDiscount.error = '';

    try {
        const response = await axios.post('/api/discounts/validate', {
            code: discountCode.value.trim(),
            event_id: props.event.id,
            subtotal: subtotalAmount.value,
        });

        const data = response.data;

        if (data.valid) {
            appliedDiscount.value = data.discount;
            form.discount_id = data.discount.id;
            validationDiscount.error = '';
        } else {
            appliedDiscount.value = null;
            form.discount_id = null;
            validationDiscount.error = data.message || 'Invalid discount code';
        }
    } catch (error: any) {
        appliedDiscount.value = null;
        form.discount_id = null;

        if (error.response && error.response.data && error.response.data.message) {
            validationDiscount.error = error.response.data.message;
        } else if (error.response && error.response.data && error.response.data.errors) {
            const errors = error.response.data.errors;
            validationDiscount.error = errors.code?.[0] || errors.event_id?.[0] || errors.subtotal?.[0] || 'Validation error';
        } else {
            validationDiscount.error = 'Failed to validate discount code';
        }
    } finally {
        validationDiscount.loading = false;
    }
};

const submitPurchase = () => {
    processing.value = true;

    router.post(
        route('events.tickets.purchase', props.event.id),
        {
            buyer_email: form.buyer_email,
            buyer_phone: form.buyer_phone,
            tickets: form.tickets,
            discount_id: form.discount_id,
            honeypot: form.honeypot,
        },
        {
            onFinish: () => {
                processing.value = false;
            },
        },
    );
};
</script>
