<template>
    <div class="min-h-screen bg-background to-gray-100 py-12">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <!-- Success Header -->
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                    <CheckCircleIcon class="h-8 w-8 text-green-600" />
                </div>
                <h1 class="mb-2 text-3xl font-bold text-foreground">Payment Successful!</h1>
                <p class="text-foreground/70">Your tickets have been purchased successfully.</p>

                <!-- Auto-redirect notification -->
                <div v-if="!redirectCancelled" class="mt-4 rounded-lg bg-blue-50 border border-blue-200 p-4">
                    <p class="text-primary">
                        <span class="font-medium">Redirecting to Order Details in {{ countdown }} seconds...</span>
                        <button
                            @click="cancelRedirect"
                            class="ml-2 text-primary font-bold cursor-pointer underline hover:text-blue-800"
                        >
                            Cancel
                        </button>
                    </p>
                    <div class="mt-2">
                        <div class="w-full bg-blue-200 rounded-full h-2">
                            <div
                                class="bg-blue-600 h-2 rounded-full transition-all duration-1000 ease-linear"
                                :style="{ width: progressWidth + '%' }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Details Card -->
            <div class="mb-8 overflow-hidden bg-card rounded-all">
                <div class="px-6 py-8">
                    <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <h3 class="mb-4 text-lg font-semibold text-foreground">Order Information</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-foreground/60">Order Number</p>
                                    <p class="font-medium text-foreground">{{ order.order_number }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-foreground/60">Purchase Date</p>
                                    <p class="font-medium text-foreground">{{ formatDate(order.purchased_at) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-foreground/60">Email</p>
                                    <p class="font-medium text-foreground">{{ order.buyer_email }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="mb-4 text-lg font-semibold text-foreground">Event Details</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-foreground/60">Event</p>
                                    <p class="font-medium text-foreground">{{ order.event.name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-foreground/60">Date & Time</p>
                                    <p class="font-medium text-foreground">{{ formatDate(order.event.start_date) }} at {{ order.event.start_time }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tickets -->
                    <div class="border-t pt-8">
                        <h3 class="mb-4 text-lg font-semibold text-foreground">Your Tickets</h3>
                        <div class="space-y-4">
                            <div v-for="ticket in order.tickets" :key="ticket.id" class="rounded flex items-center justify-between bg-gray-50/10 p-4">
                                <div>
                                    <p class="font-medium text-foreground">{{ ticket.name }}</p>
                                    <p class="text-sm text-foreground/60">Quantity: {{ ticket.quantity }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-foreground">GH₵{{ ticket.total_price }}</p>
                                    <p class="text-sm text-foreground/60">GH₵{{ ticket.unit_price}} each</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Total -->
                    <div class="mt-6 border-t pt-6">
                        <div class="flex items-center justify-between">
                            <p class="text-lg font-semibold text-foreground">Total Paid</p>
                            <p class="text-2xl font-bold text-green-600">GH₵{{ order.total_amount }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="mb-8 rounded  bg-card p-6">
                <h3 class="mb-4 text-lg font-semibold text-foreground">Next Steps</h3>
                <ul class="space-y-2 text-foreground/95">
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>A confirmation email has been sent to {{ order.buyer_email }}</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Keep your order number ({{ order.order_number }}) for reference</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Present your tickets at the event entrance</span>
                    </li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="flex flex-col justify-center gap-4 sm:flex-row">
                <Link
                    :href="route('orders.show', order.order_number)"
                    class="rounded-full inline-flex items-center justify-center text-base py-3 px-6 bg-primary cursor-pointer"
                >
                    View Order Details
                </Link>
                <Link
                    :href="route('events.show', order.event.slug || order.event.id)"
                    class="rounded-full  inline-flex items-center justify-center border border-border bg-white px-6 py-3 text-base font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                >
                    View Event Details
                </Link>
                <Link
                    :href="route('events.index')"
                    class="rounded-full sec-border inline-flex items-center justify-center border border-gray-300 bg-white px-6 py-3 text-base font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                >
                    Browse More Events
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { CheckCircleIcon, XIcon } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

interface Ticket {
    id: number;
    name: string;
    quantity: number;
    unit_price: number;
    total_price: number;
}

interface Event {
    id: number;
    slug: string;
    name: string;
    code: string;
    start_date: string;
    start_time: string;
}

interface Order {
    id: number;
    order_number: string;
    total_amount: number;
    buyer_email: string;
    buyer_phone: string;
    purchased_at: string;
    event: Event;
    tickets: Ticket[];
}

const props = defineProps<{
    order: Order;
    seo?: any;
}>();

// Auto-redirect functionality
const countdown = ref(5); // 5 seconds countdown
const redirectTimer = ref<NodeJS.Timeout | null>(null);
const redirectCancelled = ref(false);

// Progress bar width calculation
const progressWidth = computed(() => {
    return ((5 - countdown.value) / 5) * 100;
});

// Start the countdown and auto-redirect
const startRedirect = () => {
    redirectTimer.value = setInterval(() => {
        if (countdown.value > 1) {
            countdown.value--;
        } else {
            // Redirect to order details
            router.visit(route('orders.show', props.order.order_number));
        }
    }, 1000);
};

// Cancel the auto-redirect
const cancelRedirect = () => {
    if (redirectTimer.value) {
        clearInterval(redirectTimer.value);
        redirectTimer.value = null;
    }
    redirectCancelled.value = true;
};

// Start auto-redirect on component mount
onMounted(() => {
    startRedirect();
});

// Cleanup timer on unmount
onUnmounted(() => {
    if (redirectTimer.value) {
        clearInterval(redirectTimer.value);
    }
});

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}
</script>
