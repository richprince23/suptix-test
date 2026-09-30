<template>
    <Head title="Ticket Details"> </Head>
    <div class="min-h-screen bg-background">
        <!-- Navigation -->
        <NavBar background-color="bg-background" text-color="text-foreground" />

        <!-- Main Content -->
        <div class="pt-20 pb-16">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 text-center">
                    <h1 class="mb-4 text-3xl font-bold text-foreground md:text-4xl">Ticket Details</h1>
                    <p class="text-lg text-foreground/70">Ticket Number: {{ ticketNumber }}</p>
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
                <div v-else class="overflow-hidden rounded-lg border border-gray-200 bg-background shadow-md">
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
                            <!-- QR Code Section -->
                            <div class="text-center">
                                <h3 class="mb-3 text-lg font-semibold text-foreground">QR Code</h3>
                                <div class="mx-auto max-w-xs rounded-lg bg-card p-4">
                                    <canvas ref="qrCodeContainer" class="mx-auto" width="200" height="200"></canvas>
                                    <p class="mt-2 text-sm text-black">Present this QR code at the event</p>
                                </div>
                            </div>

                            <!-- Attendee Info -->
                            <div>
                                <h3 class="mb-3 text-lg font-semibold text-foreground/90">Attendee Information</h3>
                                <div class="space-y-2 rounded-lg bg-card p-4">
                                    <div class="flex justify-between">
                                        <span class="text-foreground">Name:</span>
                                        <span class="font-medium text-foreground/90">{{ ticket.attendee_name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-foreground">Email:</span>
                                        <span class="font-medium text-foreground/90">{{ ticket.attendee_email }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-foreground">Ticket Type:</span>
                                        <span class="font-medium text-foreground/90">{{ ticket.ticket_type?.name || 'Standard' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Event Info -->
                            <div>
                                <h3 class="mb-3 text-lg font-semibold text-foreground/90">Event Information</h3>
                                <div class="space-y-2 rounded-lg bg-card p-4">
                                    <div class="flex justify-between">
                                        <span class="text-foreground">Event:</span>
                                        <span class="font-medium text-foreground/90">{{ ticket.event.title }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-foreground">Date:</span>
                                        <span class="font-medium text-foreground/90">{{ formatDate(ticket.event.start_date) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-foreground">Time:</span>
                                        <span class="font-medium text-foreground/90">{{ formatTime(ticket.event.start_time) }}</span>
                                    </div>
                                    <div v-if="ticket.event.venue_name" class="flex justify-between">
                                        <span class="text-foreground">Venue:</span>
                                        <span class="font-medium text-foreground/90">{{ ticket.event.venue_name }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Ticket Status Details -->
                            <div>
                                <h3 class="mb-3 text-lg font-semibold text-foreground/90">Ticket Status</h3>
                                <div class="space-y-2 rounded-lg bg-card p-4">
                                    <div class="flex justify-between">
                                        <span class="text-foreground">Order Number:</span>
                                        <span class="font-medium text-foreground/90">{{ ticket.order.order_number }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-foreground">Status:</span>
                                        <span :class="getStatusClass(ticket.status)" class="rounded px-2 py-1 text-sm font-medium">
                                            {{ formatStatus(ticket.status) }}
                                        </span>
                                    </div>
                                    <div v-if="ticket.used_at" class="flex justify-between">
                                        <span class="text-foreground">Used At:</span>
                                        <span class="font-medium text-foreground/90">{{ formatDateTime(ticket.used_at) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Information (custom field responses) -->
                            <div v-if="ticket.custom_field_responses && ticket.custom_field_responses.length > 0">
                                <h3 class="mb-3 text-lg font-semibold text-foreground/90">Additional Information</h3>
                                <div class="space-y-2 rounded-lg bg-card p-4">
                                    <div
                                        v-for="response in ticket.custom_field_responses"
                                        :key="response.label"
                                        class="flex justify-between"
                                    >
                                        <span class="text-foreground">{{ response.label }}:</span>
                                        <span class="font-medium text-foreground/90 text-right max-w-[60%]">{{ response.value }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="border-t border-gray-200 pt-4 space-y-3">
                                <!-- Download PDF Button -->
                                <div>
                                    <button
                                        @click="downloadPDF"
                                        :disabled="isDownloading"
                                        class="w-full rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white transition-colors hover:bg-blue-700 disabled:bg-gray-400"
                                    >
                                        <div v-if="isDownloading" class="flex items-center justify-center">
                                            <Loader2 class="mr-2 h-5 w-5 animate-spin" />
                                            Generating PDF...
                                        </div>
                                        <div v-else class="flex items-center justify-center">
                                            <Download class="mr-2 h-5 w-5" />
                                            Download Ticket PDF
                                        </div>
                                    </button>
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
import { Head } from '@inertiajs/vue3';
import QRCode from 'qrcode';
import axios from 'axios';
import { AlertCircle, CheckCircle, Download, Loader2, XCircle } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

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

interface CustomFieldResponse {
    label: string;
    value: string;
}

interface Ticket {
    id: number;
    ticket_number: string;
    attendee_name: string;
    attendee_email: string;
    price: string;
    status: string;
    used_at: string | null;
    event: Event;
    ticket_type: TicketType | null;
    order: Order;
    custom_field_responses?: CustomFieldResponse[];
}

const props = defineProps<{
    ticket: Ticket | null;
    ticketNumber: string;
}>();

const qrCodeContainer = ref<HTMLCanvasElement>();
const isDownloading = ref(false);

onMounted(() => {
    if (props.ticket && qrCodeContainer.value) {
        generateQRCode();
    }
});

const generateQRCode = async () => {
    if (!props.ticket || !qrCodeContainer.value) return;

    try {
        // Use only the ticket number for QR code
        const qrData = props.ticket.ticket_number;

        // Clear any existing content
        const canvas = qrCodeContainer.value;
        const ctx = canvas.getContext('2d');
        if (ctx) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        await QRCode.toCanvas(canvas, qrData, {
            width: 200,
            margin: 2,
            color: {
                dark: '#000000',
                light: '#FFFFFF'
            }
        });
    } catch (error) {
        console.error('Error generating QR code:', error);
        // Fallback: show QR code as data URL in an img element
        if (qrCodeContainer.value) {
            try {
                const qrDataUrl = await QRCode.toDataURL(props.ticket.ticket_number, {
                    width: 200,
                    margin: 2
                });

                // Replace canvas with img element
                const img = document.createElement('img');
                img.src = qrDataUrl;
                img.width = 200;
                img.height = 200;
                img.className = 'mx-auto';
                qrCodeContainer.value.parentNode?.replaceChild(img, qrCodeContainer.value);
            } catch (fallbackError) {
                console.error('Fallback QR generation also failed:', fallbackError);
            }
        }
    }
};

const downloadPDF = async () => {
    if (!props.ticket || isDownloading.value) return;

    isDownloading.value = true;

    try {
        const response = await axios.get(`/api/tickets/${props.ticket.id}/download-pdf`, {
            responseType: 'blob'
        });

        // Create a blob URL and trigger download
        const blob = new Blob([response.data], { type: 'application/pdf' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `ticket-${props.ticket.ticket_number}.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
    } catch (error: any) {
        console.error('Error downloading PDF:', error);
        alert('Failed to download ticket PDF. Please try again.');
    } finally {
        isDownloading.value = false;
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
</script>

<style scoped>
/* Add any custom styles here */
</style>
