<template>
    <div class="min-h-screen bg-background">
        <!-- Navigation -->
        <NavBar text-color="text-black"
        background-color="bg-transparent" class="fixed z-20 w-full border-b-pink-600/10 shadow-mdss shadow-black/10 backdrop-blur" />

        <!-- Main Content -->
        <div class="pt-20 py-16">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
                <!-- Header -->
                <div class="mb-12 text-center">
                    <h1 class="mb-4 text-4xl font-bold foreground md:text-5xl">Find Your Order</h1>
                    <p class="mx-auto max-w-2xl text-xl foreground/70">Enter your order number and email/phone for secure access to your tickets</p>
                </div>

                <!-- Search Form -->
                <div class="rounded-0 mb-8 overflow-hidden rounded-all">
                    <div v-if="formCollapsed" class="flex items-center justify-between p-4 border border-gray-200 rounded">
                        <div class="text-sm text-foreground/70">
                            Searching for <span class="font-medium text-foreground">{{ orderNumber }}</span>
                        </div>
                        <button type="button" @click="formCollapsed = false" class="text-sm text-primary font-medium hover:underline">
                            Edit Search
                        </button>
                    </div>
                    <div v-else class="p-8">
                        <form @submit.prevent="searchOrder" class="space-y-6">
                            <div>
                                <label for="orderNumber" class="mb-3 block text-sm font-semibold text-foreground"> Order Number </label>
                                <div class="relative">
                                    <Input
                                        id="orderNumber"
                                        v-model="orderNumber"
                                        type="text"
                                        placeholder="Enter your order number (e.g., ORD-ABC123XYZ0)"
                                        class="rounded  w-full px-4 py-5 text-lg transition-colors"
                                        :disabled="isLoading"
                                        required
                                    />
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                        <Search class="h-5 w-5 text-gray-400" />
                                    </div>
                                </div>
                                <p class="mt-2 text-sm text-foreground/70">Order numbers start with "ORD-" followed by 10 characters</p>
                            </div>

                            <!-- Verification Section -->
                            <div class="space-y-4">
                                <div class="border-t border-gray-200 pt-6">
                                    <h3 class="mb-4 text-sm font-semibold foreground">Verification Required</h3>
                                    <p class="mb-4 text-sm text-foreground/70">For security, please provide your contact information used when placing the order.</p>
                                </div>

                                <!-- Verification Method Toggle -->
                                <div class="flex flex-col md:flex-row items-center justify-between w-full sm:w-maxs space-x-1 rounded-full p-0">
                                    <button
                                        type="button"
                                        @click="setVerificationMethod('email')"
                                        :class="[
                                            'rounded flex items-center w-full px-4 py-2 text-sm font-medium transition-all duration-200',
                                            verificationMethod === 'email'
                                                ? ' bg-primary text-primary-foreground shadow-sm'
                                                : 'bg-card hover:text-foreground'
                                        ]"
                                    >
                                        <Mail class="mr-2 h-4 w-4" />
                                        Email Address
                                    </button>
                                    <button
                                        type="button"
                                        @click="setVerificationMethod('phone')"
                                        :class="[
                                            'rounded flex items-center w-full px-4 py-2 text-sm font-medium transition-all duration-200',
                                            verificationMethod === 'phone'
                                                 ? ' bg-primary text-primary-foreground shadow-sm'
                                                : 'bg-card hover:text-foreground'
                                        ]"
                                    >
                                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        Phone Number
                                    </button>
                                </div>

                                <!-- Dynamic Verification Field -->
                                <div class="transition-all duration-300">
                                    <div v-if="verificationMethod === 'email'" class="space-y-2">
                                        <label for="verificationEmail" class="block text-sm font-medium foreground/80">
                                            Email Address
                                        </label>
                                        <Input
                                            id="verificationEmail"
                                            v-model="verificationEmail"
                                            type="email"
                                            placeholder="Enter the email address"
                                            class="rounded  w-full px-4 py-5 transition-colors"
                                            :disabled="isLoading"
                                            required
                                        />
                                        <p class="text-xs text-gray-500">Enter the email address you used when placing the order</p>
                                    </div>

                                    <div v-if="verificationMethod === 'phone'" class="space-y-2">
                                        <label for="verificationPhone" class="block text-sm font-medium foreground/80">
                                            Phone Number
                                        </label>
                                        <Input
                                            id="verificationPhone"
                                            v-model="verificationPhone"
                                            type="tel"
                                            placeholder="Enter the phone number"
                                            class="w-full px-4 py-3 transition-colors"
                                            :disabled="isLoading"
                                            required
                                        />
                                        <p class="text-xs text-gray-500">Enter the phone number you used when placing the order</p>
                                    </div>
                                </div>
                            </div>

                            <Button
                                type="submit"
                                :disabled="isLoading || !orderNumber.trim() || (verificationMethod === 'email' && !verificationEmail.trim()) || (verificationMethod === 'phone' && !verificationPhone.trim())"
                                class="rounded h-12 w-full transform cursor-pointer bg-primary px-4 px-6 py-2 py-4 font-semibold text-white transition-all transition-colors duration-300 hover:from-blue-700 hover:to-purple-700 disabled:cursor-not-allowed disabled:from-gray-400 disabled:to-gray-600"
                            >
                                <div v-if="isLoading" class="flex items-center justify-center">
                                    <Loader2 class="mr-2 h-5 w-5 animate-spin" />
                                    Searching...
                                </div>
                                <div v-else class="flex items-center justify-center">
                                    <Search class="mr-2 h-5 w-5" />
                                    Search Order
                                </div>
                            </Button>
                        </form>
                    </div>
                </div>


                <!-- Error Message -->
                <div v-if="errorMessage" class="rounded-all mb-8 border border-red-200 bg-red-50 p-4">
                    <div class="flex items-center">
                        <AlertCircle class="mr-3 h-5 w-5 text-red-600" />
                        <p class="font-medium text-red-800">{{ errorMessage }}</p>
                    </div>
                </div>

                <!-- Order Results -->
                <div v-if="orderData" class="space-y-8">
                    <!-- Order Summary -->
                    <div class="rounded-all overflow-hidden bg-whites">
                        <div class="bg-primary px-8 py-6">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between text-white">
                                <div>
                                    <h2 class="text-2xl font-bold">Order Details</h2>
                                    <p class="text-blue-100">{{ orderData.order_number }}</p>
                                </div>
                                <div class="text-left sm:text-right">
                                    <div class="text-3xl font-bold">GHS {{ orderData.total_amount }}</div>
                                    <span :class="getStatusClass(orderData.status)" class="rounded-full px-3 py-1 text-sm font-medium">
                                        {{ formatStatus(orderData.status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="p-8">
                            <div class="grid gap-8 md:grid-cols-2">
                                <!-- Order Info -->
                                <div class="space-y-4">
                                    <h3 class="mb-4 text-lg font-semibold foreground">Order Information</h3>
                                    <div class="space-y-3">
                                        <div class="flex justify-between">
                                            <span class="foreground/70">Order Number:</span>
                                            <span class="font-medium foreground">{{ orderData.order_number }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="foreground/70">Order Date:</span>
                                            <span class="font-medium foreground">{{ formatDate(orderData.created_at) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="foreground/70">Total Tickets:</span>
                                            <span class="font-medium foreground">{{ orderData.total_tickets }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="foreground/70">Payment Method:</span>
                                            <span class="font-medium foreground">{{ orderData.payment_method || 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Buyer Info -->
                                <div class="space-y-4">
                                    <h3 class="mb-4 text-lg font-semibold foreground">Buyer Information</h3>
                                    <div class="space-y-3">
                                        <div class="flex justify-between">
                                            <span class="foreground/70">Email:</span>
                                            <span class="font-medium foreground">{{ orderData.buyer_email }}</span>
                                        </div>
                                        <div v-if="orderData.buyer_phone" class="flex justify-between">
                                            <span class="foreground/70">Phone:</span>
                                            <span class="font-medium foreground">{{ orderData.buyer_phone }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Event Details -->
                    <div class="rounded-all overflow-hidden border border-gray-200 bg-background">
                        <div class="flex flex-col">
                            <!-- Event Image -->
                            <!-- <div class="md:w-1/3">
                                <div class="relative h-64 overflow-hidden bg-gradient-to-br from-blue-400 to-purple-600 md:h-full">
                                    <img
                                        v-if="orderData.event.cover_image_url"
                                        :src="orderData.event.cover_image_url"
                                        :alt="orderData.event.title"
                                        class="h-full w-full object-cover"
                                    />
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/30">
                                        <div class="p-4 text-center text-white">
                                            <h3 class="mb-2 text-xl font-bold">{{ orderData.event.title }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div> -->

                            <!-- Event Info -->
                            <div class="p-8">
                                <h2 class="mb-4 text-2xl font-bold foreground line-clamp-3">{{ orderData.event.title }}</h2>
                                <!-- <p class="mb-6 foreground/70">{{ orderData.event.description }}</p> -->

                                <div class="grid gap-6 md:grid-cols-2">
                                    <div class="space-y-3">
                                        <div class="flex items-center foreground/70">
                                            <Calendar class="mr-3 h-5 w-5" />
                                            <div>
                                                <div class="font-medium foreground">{{ formatDate(orderData.event.start_date) }}</div>
                                                <div class="text-sm">
                                                    {{ formatTime(orderData.event.start_time) }} - {{ formatTime(orderData.event.end_time) }}
                                                </div>
                                            </div>
                                        </div>

                                        <div v-if="orderData.event.venue_name" class="flex items-center foreground/70">
                                            <MapPin class="mr-3 h-5 w-5" />
                                            <div>
                                                <div class="font-medium foreground">{{ orderData.event.venue_name }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="orderData.event.organizer" class="space-y-3">
                                        <div class="flex items-center foreground/70">
                                            <User class="mr-3 h-5 w-5" />
                                            <div>
                                                <div class="font-medium foreground">{{ orderData.event.organizer.name }}</div>
                                                <div class="text-sm">Organizer</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tickets List -->
                    <div class="rounded-all overflow-hidden border border-gray-200 bg-whites">
                        <div class="border-b border-gray-200 bg-black-500/10 px-8 py-6">
                            <h2 class="text-2xl font-bold foreground">Your Tickets</h2>
                            <p class="mt-1 foreground/70">{{ orderData.tickets.length }} ticket(s) in this order</p>
                        </div>

                        <div class="sm:p-8">
                            <div class="space-y-6">
                                <div
                                    v-for="(ticket, index) in orderData.tickets"
                                    :key="ticket.id"
                                    class="rounded overflow-hidden border border-gray-200 transition-shadow hover:shadow-xs"
                                >
                                    <div class="border-b border-gray-200 px-6 py-4">
                                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
                                            <div>
                                                <h3 class="text-lg font-semibold foreground">Ticket #{{ index + 1 }}</h3>
                                                <p class="text-sm text-gray-500">{{ ticket.ticket_number }}</p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <button
                                                    @click="downloadTicketPDF(ticket, index + 1)"
                                                    :disabled="isDownloading[ticket.id]"
                                                    class="rounded bg-blue-600 p-2 text-white transition-colors hover:bg-blue-700 disabled:bg-gray-400"
                                                    title="Download Ticket PDF"
                                                >
                                                    <Download v-if="!isDownloading[ticket.id]" class="h-4 w-4" />
                                                    <Loader2 v-else class="h-4 w-4 animate-spin" />
                                                </button>
                                                <div class="text-right">
                                                    <div class="text-xl font-bold foreground">GHS {{ ticket.price }}</div>
                                                    <span
                                                        :class="getTicketStatusClass(ticket.status)"
                                                        class="rounded px-3 py-1 text-xs font-medium"
                                                    >
                                                        {{ formatStatus(ticket.status) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-6">
                                        <div class="grid gap-6 md:grid-cols-2">
                                            <div class="space-y-3">
                                                <div class="flex flex-col sm:flex-row justify-between">
                                                    <span class="foreground/70">Attendee Name:</span>
                                                    <span class="font-medium foreground">{{ ticket.attendee_name }}</span>
                                                </div>
                                                <div class="flex flex-col sm:flex-row justify-between">
                                                    <span class="foreground/70">Attendee Email:</span>
                                                    <span class="font-medium foreground">{{ ticket.attendee_email }}</span>
                                                </div>
                                                <div v-if="ticket.ticket_type" class="flex flex-col sm:flex-row justify-between">
                                                    <span class="foreground/70">Ticket Type:</span>
                                                    <span class="font-medium foreground">{{ ticket.ticket_type.name }}</span>
                                                </div>
                                            </div>

                                            <div class="space-y-3">
                                                <div class="flex flex-col sm:flex-row justify-between">
                                                    <span class="foreground/70">Status:</span>
                                                    <span class="font-medium foreground">{{ formatStatus(ticket.status) }}</span>
                                                </div>
                                                <div v-if="ticket.used_at" class="flex flex-col sm:flex-row justify-between">
                                                    <span class="foreground/70">Used At:</span>
                                                    <span class="font-medium foreground">{{ formatDateTime(ticket.used_at) }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- QR Code -->
                                        <div class="mt-6 flex justify-center">
                                            <QRCodeComponent :ticket-number="ticket.ticket_number" :ticket-id="ticket.id" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Share Tickets Section -->
                    <div class="rounded-all overflow-hidden  bg-whites">
                        <div class="p-8 text-center">
                            <h3 class="mb-4 text-xl font-semibold foreground">Share Tickets</h3>
                            <p class="mb-6 foreground/70">Send individual tickets to each attendee's email address</p>

                            <button
                                @click="shareTicketsViaEmail"
                                :disabled="isSharing"
                                class="rounded-all transform bg-primary cursor-pointer px-8 py-3 font-semibold text-white transition-all duration-300 disabled:cursor-not-allowed disabled:from-gray-400 disabled:to-gray-500 disabled:hover:scale-100"
                            >
                                <div v-if="isSharing" class="flex items-center justify-center">
                                    <Loader2 class="mr-2 h-5 w-5 animate-spin" />
                                    Sending Tickets...
                                </div>
                                <div v-else class="flex items-center justify-center">
                                    <Mail class="mr-2 h-5 w-5" />
                                    Email Tickets to Attendees
                                </div>
                            </button>

                            <div v-if="shareMessage" class="mt-4">
                                <div :class="shareSuccess ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium">
                                    {{ shareMessage }}
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
import QRCodeComponent from '@/components/QRCodeComponent.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import axios from 'axios';
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';
import { AlertCircle, Calendar, Download, Loader2, Mail, MapPin, Search, User } from 'lucide-vue-next';
import { reactive, ref, onMounted } from 'vue';

// Define props
interface Props {
    initialOrderNumber?: string;
    seo?: any;
}

const props = withDefaults(defineProps<Props>(), {
    initialOrderNumber: '',
    seo: () => ({}),
});

interface TicketType {
    name: string;
    description: string;
    price: string;
}

interface Ticket {
    id: number;
    ticket_number: string;
    attendee_name: string;
    attendee_email: string;
    price: string;
    status: string;
    used_at: string | null;
    ticket_type: TicketType | null;
}

interface Organizer {
    name: string;
    email: string;
    phone: string;
}

interface Event {
    id: number;
    title: string;
    description: string;
    start_date: string;
    start_time: string;
    end_date: string;
    end_time: string;
    cover_image_url: string | null;
    venue_name: string | null;
    organizer: Organizer | null;
}

interface OrderData {
    id: number;
    order_number: string;
    buyer_email: string;
    buyer_phone: string | null;
    total_amount: string;
    status: string;
    payment_method: string | null;
    purchased_at: string | null;
    created_at: string;
    total_tickets: number;
    event: Event;
    tickets: Ticket[];
}

const orderNumber = ref(props.initialOrderNumber || '');
const verificationEmail = ref('');
const verificationPhone = ref('');
const verificationMethod = ref<'email' | 'phone'>('email');
const isLoading = ref(false);
const errorMessage = ref('');
const orderData = ref<OrderData | null>(null);
const formCollapsed = ref(false);

// PDF Download state
const isDownloading = reactive<Record<number, boolean>>({});

// Email sharing state
const isSharing = ref(false);
const shareMessage = ref('');
const shareSuccess = ref(false);

// Auto-search on mount if initial order number is provided
// Note: Auto-search is disabled due to security verification requirement
onMounted(() => {
    // Auto-search disabled - users must provide verification details
    // if (props.initialOrderNumber && props.initialOrderNumber.trim()) {
    //     searchOrder();
    // }
});

// Toggle between email and phone verification methods
const setVerificationMethod = (method: 'email' | 'phone') => {
    verificationMethod.value = method;
    // Clear the other field when switching
    if (method === 'email') {
        verificationPhone.value = '';
    } else {
        verificationEmail.value = '';
    }
    // Clear any previous error messages
    errorMessage.value = '';
};

const searchOrder = async () => {
    const trimmedOrderNumber = orderNumber.value.trim();
    const trimmedEmail = verificationEmail.value.trim();
    const trimmedPhone = verificationPhone.value.trim();

    if (!trimmedOrderNumber) return;

    // Basic validation before making API call
    if (trimmedOrderNumber.length < 3) {
        errorMessage.value = 'Order number must be at least 3 characters long.';
        return;
    }

    // Ensure the selected verification method has data
    if (verificationMethod.value === 'email' && !trimmedEmail) {
        errorMessage.value = 'Please enter your email address for verification.';
        return;
    }
    if (verificationMethod.value === 'phone' && !trimmedPhone) {
        errorMessage.value = 'Please enter your phone number for verification.';
        return;
    }

    isLoading.value = true;
    errorMessage.value = '';
    orderData.value = null;

    try {
        const requestData: any = {
            order_number: trimmedOrderNumber,
        };

        // Add only the selected verification method
        if (verificationMethod.value === 'email' && trimmedEmail) {
            requestData.verification_email = trimmedEmail;
        } else if (verificationMethod.value === 'phone' && trimmedPhone) {
            requestData.verification_phone = trimmedPhone;
        }

        const response = await axios.post('/api/orders/search', requestData, {
            timeout: 10000, // 10 second timeout
        });

        if (response.data.success) {
            orderData.value = response.data.order;
            formCollapsed.value = true;
        }
    } catch (error: any) {
        if (error.response?.status === 404) {
            errorMessage.value = error.response.data.message;
        } else if (error.response?.status === 400) {
            errorMessage.value = error.response.data.message;
        } else if (error.response?.status === 422) {
            // Validation errors
            const errors = error.response.data.errors;
            if (errors) {
                const errorMessages = Object.values(errors).flat();
                errorMessage.value = errorMessages.join(' ');
            } else {
                errorMessage.value = error.response.data.message || 'Validation error occurred.';
            }
        } else if (error.code === 'ECONNABORTED') {
            errorMessage.value = 'Request timed out. Please try again.';
        } else {
            errorMessage.value = 'An error occurred while searching for your order. Please try again.';
        }
    } finally {
        isLoading.value = false;
    }
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
        pending: 'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
        refunded: 'bg-gray-100 text-gray-800',
    };
    return statusClasses[status as keyof typeof statusClasses] || 'bg-gray-100 text-gray-800';
};

const getTicketStatusClass = (status: string): string => {
    const statusClasses = {
        active: 'bg-green-100 text-green-800',
        used: 'bg-blue-100 text-blue-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return statusClasses[status as keyof typeof statusClasses] || 'bg-gray-100 text-gray-800';
};

// PDF Download function
const downloadTicketPDF = async (ticket: Ticket, ticketNumber: number) => {
    if (isDownloading[ticket.id] || !orderData.value) return;

    isDownloading[ticket.id] = true;

    try {
        // Create a temporary div for the ticket content
        const tempDiv = document.createElement('div');
        tempDiv.style.position = 'absolute';
        tempDiv.style.left = '-9999px';
        tempDiv.style.width = '800px';
        tempDiv.style.backgroundColor = 'white';
        tempDiv.style.padding = '40px';
        tempDiv.style.fontFamily = 'Arial, sans-serif';

        // Generate QR code for PDF using ticket number directly
        //// @ts-ignore
        const QRCode = (await import('qrcode')) as any;
        const qrCodeDataUrl = await QRCode.toDataURL(ticket.ticket_number, {
            width: 250,
            margin: 2,
        });

        // Create ticket HTML content with CSS-compatible colors
        tempDiv.innerHTML = `
            <div style="border: 2px solid rgb(229, 231, 235); border-radius: 0px; padding: 32px; background: rgb(248, 250, 252);">
                <div style="text-align: center; margin-bottom: 32px;">
                    <h1 style="font-size: 28px; font-weight: bold; color: rgb(31, 41, 55); margin: 0 0 8px 0;">Event Ticket</h1>
                    <p style="font-size: 18px; color: rgb(107, 114, 128); margin: 0;">${orderData.value.event.title}</p>
                </div>

                <div style="margin-bottom: 32px;">
                    <div style="margin-bottom: 32px;">
                        <h3 style="font-size: 16px; font-weight: bold; color: rgb(55, 65, 81); margin: 0 0 16px 0;">Event Details</h3>
                        <div style="display: flex; flex-wrap: wrap; gap: 16px;">
                            <div style="flex: 1; min-width: 200px;">
                                <p style="margin: 8px 0; color: rgb(75, 85, 99);"><strong>Date:</strong> ${formatDate(orderData.value.event.start_date)}</p>
                                <p style="margin: 8px 0; color: rgb(75, 85, 99);"><strong>Time:</strong> ${formatTime(orderData.value.event.start_time)}</p>
                            </div>
                            <div style="flex: 1; min-width: 200px;">
                                ${orderData.value.event.venue_name ? `<p style="margin: 8px 0; color: rgb(75, 85, 99);"><strong>Venue:</strong> ${orderData.value.event.venue_name}</p>` : ''}
                            </div>
                        </div>
                    </div>

                    <!-- Centered QR Code Section -->
                    <div style="text-align: center; padding: 24px; background: rgb(255, 255, 255); border-radius: 8px; border: 1px solid rgb(229, 231, 235);">
                        <h3 style="font-size: 18px; font-weight: bold; color: rgb(31, 41, 55); margin: 0 0 16px 0;">Digital Ticket</h3>
                        <img src="${qrCodeDataUrl}" alt="QR Code" style="width: 250px; height: 250px; border: 2px solid rgb(229, 231, 235); border-radius: 8px; background: white; padding: 8px;">
                        <p style="margin: 16px 0 0 0; font-size: 14px; color: rgb(107, 114, 128); font-weight: 500;">Scan this QR code at event entrance</p>
                        <p style="margin: 4px 0 0 0; font-size: 12px; color: rgb(156, 163, 175);">Keep this ticket accessible on your device</p>
                    </div>
                </div>

                <div style="border-top: 1px solid rgb(229, 231, 235); padding-top: 24px; margin-bottom: 24px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 16px;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: bold; color: rgb(55, 65, 81); margin: 0 0 8px 0;">Attendee Information</h3>
                            <p style="margin: 4px 0; color: rgb(75, 85, 99);"><strong>Name:</strong> ${ticket.attendee_name}</p>
                            <p style="margin: 4px 0; color: rgb(75, 85, 99);"><strong>Email:</strong> ${ticket.attendee_email}</p>
                            <p style="margin: 4px 0; color: rgb(75, 85, 99);"><strong>Ticket Type:</strong> ${ticket.ticket_type?.name || 'Standard'}</p>
                        </div>
                        <div style="text-align: right;">
                            <h3 style="font-size: 16px; font-weight: bold; color: rgb(55, 65, 81); margin: 0 0 8px 0;">Ticket Information</h3>
                            <p style="margin: 4px 0; color: rgb(75, 85, 99);"><strong>Ticket #:</strong> ${ticketNumber}</p>
                            <p style="margin: 4px 0; color: rgb(75, 85, 99);"><strong>Order:</strong> ${orderData.value.order_number}</p>
                            <p style="margin: 4px 0; color: rgb(75, 85, 99);"><strong>Price:</strong> GHS ${ticket.price}</p>
                        </div>
                    </div>
                </div>

                <div style="background: rgb(249, 250, 251); padding: 16px; border-radius: 0px; border: 1px solid rgb(229, 231, 235);">
                    <p style="font-size: 14px; color: rgb(107, 114, 128); margin: 0; text-align: center;"><strong>Ticket ID:</strong> ${ticket.ticket_number}</p>
                </div>
            </div>
        `;

        document.body.appendChild(tempDiv);

        // Convert to canvas and then PDF
        const canvas = await html2canvas(tempDiv, {
            scale: 2,
            useCORS: true,
            allowTaint: false,
            backgroundColor: '#ffffff',
            logging: false,
            ignoreElements: (element) => {
                // Skip elements that might have unsupported CSS
                return element.tagName === 'STYLE' || element.tagName === 'SCRIPT';
            },
        });

        document.body.removeChild(tempDiv);

        // Create PDF
        const pdf = new jsPDF('p', 'mm', 'a4');
        const imgData = canvas.toDataURL('image/png');
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

        pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
        pdf.save(`ticket-${ticket.ticket_number}.pdf`);
    } catch (error) {
        console.error('Error generating PDF:', error);
        alert('Failed to generate PDF. Please try again.');
    } finally {
        isDownloading[ticket.id] = false;
    }
};

// Email sharing function
const shareTicketsViaEmail = async () => {
    if (isSharing.value || !orderData.value) return;

    isSharing.value = true;
    shareMessage.value = '';
    shareSuccess.value = false;

    try {
        const response = await axios.post(
            '/api/orders/share-tickets',
            {
                order_id: orderData.value.id,
                order_number: orderData.value.order_number,
            },
            {
                timeout: 30000, // 30 second timeout for email sending
            },
        );

        if (response.data.success) {
            shareSuccess.value = true;
            shareMessage.value = `Successfully sent ${orderData.value.tickets.length} ticket(s) to attendees!`;
        }
    } catch (error: any) {
        shareSuccess.value = false;
        if (error.response?.status === 404) {
            shareMessage.value = 'Order not found.';
        } else if (error.code === 'ECONNABORTED') {
            shareMessage.value = 'Request timed out. Emails may still be sending.';
        } else {
            shareMessage.value = error.response?.data?.message || 'Failed to send tickets. Please try again.';
        }
    } finally {
        isSharing.value = false;

        // Clear message after 5 seconds
        setTimeout(() => {
            shareMessage.value = '';
        }, 5000);
    }
};
</script>

<style scoped>
/* Add any custom styles here */
</style>
