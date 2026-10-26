<template>
    <SeoHead v-if="props.seo?.title" :seo="props.seo" />
    <div class="min-h-screen bg-background py-12 overflow-hidden">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
                    <ReceiptIcon class="h-8 w-8 text-primary" />
                </div>
                <h1 class="mb-2 text-3xl font-bold foreground">Order Details</h1>
                <p class="text-foreground/70">Review your order information and tickets</p>
            </div>

            <!-- Order Status Badge -->
            <div class="mb-6 flex justify-center">
                <span
                    :class="{
                        'bg-green-100 text-green-800': order.status === 'confirmed',
                        'bg-yellow-100 text-yellow-800': order.status === 'pending',
                        'bg-red-100 text-red-800': order.status === 'cancelled',
                        'bg-gray-100 text-gray-800': order.status === 'refunded',
                    }"
                    class="inline-flex items-center rounded px-3 py-1 text-sm font-medium"
                >
                    <div
                        :class="{
                            'bg-green-400': order.status === 'confirmed',
                            'bg-yellow-400': order.status === 'pending',
                            'bg-red-400': order.status === 'cancelled',
                            'bg-gray-400': order.status === 'refunded',
                        }"
                        class="mr-2 h-2 w-2 rounded-full"
                    ></div>
                    {{ order?.status?.charAt(0).toUpperCase() + order.status.slice(1) }}
                </span>
            </div>

            <!-- Order Information Card -->
            <div class="mb-8 overflow-hidden bg-whites rounded-all">
                <div class="px-6 py-8">
                    <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <h3 class="mb-4 text-lg font-semibold foreground">Order Information</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-foreground/60">Order Number</p>
                                    <p class="font-medium foreground">{{ order.order_number }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-foreground/60">Order Date</p>
                                    <p class="font-medium foreground">{{ formatDate(order.created_at) }}</p>
                                </div>
                                <div v-if="order.purchased_at">
                                    <p class="text-sm text-foreground/60">Purchase Date</p>
                                    <p class="font-medium foreground">{{ formatDate(order.purchased_at) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-foreground/60">Payment Method</p>
                                    <p class="font-medium foreground">{{ order.payment_method || 'Not specified' }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="mb-4 text-lg font-semibold foreground">Buyer Information</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-foreground/60">Email</p>
                                    <p class="font-medium foreground">{{ order.buyer_email }}</p>
                                </div>
                                <div v-if="order.buyer_phone">
                                    <p class="text-sm text-foreground/60">Phone</p>
                                    <p class="font-medium foreground">{{ order.buyer_phone }}</p>
                                </div>
                                <div v-if="order.payment_reference">
                                    <p class="text-sm text-foreground/60">Payment Reference</p>
                                    <p class="font-medium foreground">{{ order.payment_reference }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Event Information -->
                    <div class="border-t pt-8">
                        <h3 class="mb-4 text-lg font-semibold foreground">Event Details</h3>
                        <div class="flex flex-col md:flex-row items-start gap-4">
                            <img
                                v-if="order.event.cover_image_url"
                                :src="order.event.cover_image_url"
                                :alt="order.event.title"
                                class="h-20 w-20 rounded-0 object-cover"
                            />
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold foreground">{{ order.event.title }}</h4>
                                <p v-if="order.event.venue_name" class="text-foreground/70">{{ order.event.venue_name }}</p>
                                <p class="text-foreground/70">
                                    {{ formatDate(order.event.start_date) }} at
                                    {{ formatTime(order.event.start_time) }}
                                </p>
                                <!-- <p v-if="order.event.description" class="mt-2 text-sm line-clamp-2 overflow-hidden text-foreground/70">
                                    {{ order.event.description }}
                                </p> -->
                            </div>
                            <Link
                                :href="route('events.show', order.event.id)"
                                class="rounded-full bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/80 focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:outline-none"
                            >
                                View Event
                            </Link>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Tickets Section -->
            <div class="mb-8 overflow-hidden bg-whites rounded-all">
                <div class="px-6 py-8">
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-foreground">Your Tickets</h3>
                        <span class="rounded bg-card border px-3 py-1 text-sm font-medium text-foreground">
                            {{ tickets.length }} ticket{{ tickets.length !== 1 ? 's' : '' }}
                        </span>
                    </div>

                    <div class="space-y-4">
                        <div v-for="ticket in tickets" :key="ticket.id" class="rounded  bg-card p-4">
                            <div class="flex flex-col md:flex-row items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <h4 class="font-semibold foreground">{{ ticket.ticket_type.name }}</h4>
                                        <span
                                            :class="{
                                                'bg-green-100 text-green-800': ticket.status === 'active',
                                                'bg-yellow-100 text-yellow-800': ticket.status === 'pending',
                                                'bg-red-100 text-red-800': ticket.status === 'used',
                                                'bg-gray-100 text-gray-800': ticket.status === 'cancelled',
                                            }"
                                            class="rounded inline-flex items-center  px-2 py-1 text-xs font-medium"
                                        >
                                            {{ ticket.status.charAt(0).toUpperCase() + ticket.status.slice(1) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-foreground/70">{{ ticket.ticket_number }}</p>
                                    <div class="mt-2">
                                        <p class="font-medium foreground">{{ ticket.attendee_name }}</p>
                                        <p v-if="ticket.attendee_email" class="text-sm text-foreground/70">
                                            {{ ticket.attendee_email }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-left md:text-right">
                                    <p class="text-lg font-semibold foreground">GH₵{{ ticket.price }}</p>
                                    <div class="mt-2 flex gap-2">
                                        <button
                                            v-if="order.status === 'confirmed' && ticket.status === 'active'"
                                            @click="downloadTicketPDF(ticket)"
                                            :disabled="isDownloading[ticket.id]"
                                            class="rounded-full bg-primary px-3 py-1 text-xs font-medium text-primary-foreground hover:bg-primary/80 disabled:opacity-50"
                                        >
                                            {{ isDownloading[ticket.id] ? '...' : 'Download' }}
                                        </button>
                                        <button
                                            v-if="order.status === 'confirmed' && ticket.status === 'active'"
                                            @click="showQRCode(ticket)"
                                            class="rounded-full border bg-background/70 px-3 py-1 text-xs font-medium text-foreground hover:bg-primary hover:text-primary-foreground"
                                        >
                                            QR Code
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Custom field responses per ticket -->
                            <div v-if="ticket.custom_field_responses && ticket.custom_field_responses.length > 0" class="mt-4 pt-4 border-t">
                                <h5 class="text-sm font-semibold mb-2 text-foreground/80">Additional Information</h5>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <div
                                        v-for="response in ticket.custom_field_responses"
                                        :key="response.label"
                                        class="flex justify-between text-sm"
                                    >
                                        <span class="text-foreground/60 shrink-0 mr-2">{{ response.label }}:</span>
                                        <span class="font-medium text-foreground text-right">{{ response.value }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="mb-8 overflow-hidden rounded-all">
                <div class="px-6 py-8">
                    <h3 class="mb-4 text-lg font-semibold text-foreground">Order Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-foreground/70">Subtotal</span>
                            <span class="font-medium">GH₵{{ order.subtotal_amount }}</span>
                        </div>
                        <div v-if="order.discount_amount && order.discount_amount > 0" class="flex justify-between text-green-600">
                            <span>Discount ({{ order.discount?.name }})</span>
                            <span>-GH₵{{ order.discount_amount }}</span>
                        </div>
                        <div class="border-t pt-3">
                            <div class="flex justify-between">
                                <span class="text-lg font-semibold">Total</span>
                                <span class="text-xl font-bold text-green-600">GH₵{{ order.total_amount }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col justify-center gap-4 sm:flex-row">
                <button
                    v-if="order.status === 'confirmed'"
                    @click="downloadAllTickets"
                    :disabled="isDownloadingAll"
                    class="rounded-full bg-primary cursor-pointer inline-flex items-center justify-center  px-6 py-3 text-base font-medium text-primary-foreground transition-colors hover:bg-primary/80 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
                >
                    {{ isDownloadingAll ? 'Generating PDFs...' : 'Download All Tickets' }}
                </button>
                <button
                    v-if="order.status === 'pending'"
                    @click="goToPayment"
                    class="rounded-all inline-flex items-center justify-center  bg-green-600 px-6 py-3 text-base font-medium text-foreground transition-colors hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:outline-none"
                >
                    Complete Payment
                </button>
                <Link
                    :href="route('events.show', order.event.slug || order.event.id)"
                    class="rounded-full inline-flex items-center justify-center  bg-secondary px-6 py-3 text-base font-medium text-foreground/80 transition-colors hover:bg-primary/60 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                >
                    View Event Details
                </Link>
                <Link
                    :href="route('events.index')"
                    class="rounded-full inline-flex items-center justify-center  bg-secondary px-6 py-3 text-base font-medium text-foreground/80 transition-colors hover:bg-primary/50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                >
                    Browse More Events
                </Link>
            </div>
        </div>

        <!-- QR Code Modal -->
        <div v-if="showingQRCode" class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black">
            <div class="mx-4 max-w-md rounded-0 bg-white p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-secondary">Ticket QR Code</h3>
                    <button @click="showingQRCode = null" class="text-gray-400 hover:text-foreground/70">
                        <XIcon class="h-6 w-6" />
                    </button>
                </div>
                <div class="text-center">
                    <div ref="qrCodeContainer" class="mb-4 flex justify-center"></div>
                    <p class="text-sm text-foreground/70">{{ showingQRCode?.ticket_number }}</p>
                    <p class="mt-2 text-xs text-foreground/60">Show this QR code at the event entrance</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';
import { ReceiptIcon, XIcon } from 'lucide-vue-next';
import { nextTick, ref } from 'vue';
import SeoHead from '@/components/SeoHead.vue';

interface Event {
    id: number;
    slug: string;
    title: string;
    description?: string;
    cover_image_url?: string;
    start_date: string;
    start_time: string;
    venue_name?: string;
}

interface TicketType {
    id: number;
    name: string;
    description?: string;
    price: number;
}

interface Ticket {
    id: number;
    ticket_number: string;
    attendee_name: string;
    attendee_email?: string;
    price: number;
    status: string;
    ticket_type: TicketType;
}

interface Discount {
    id: number;
    name: string;
    type: string;
    value: number;
}

interface Order {
    id: number;
    order_number: string;
    buyer_email: string;
    buyer_phone?: string;
    subtotal_amount: number;
    discount_amount?: number;
    total_amount: number;
    status: string;
    payment_method?: string;
    payment_reference?: string;
    created_at: string;
    purchased_at?: string;
    event: Event;
    discount?: Discount;
}

const props = defineProps<{
    order: Order;
    tickets: Ticket[];
    seo?: any;
}>();

const isDownloading = ref<Record<number, boolean>>({});
const isDownloadingAll = ref(false);
const showingQRCode = ref<Ticket | null>(null);
const qrCodeContainer = ref<HTMLElement>();

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

const downloadTicketPDF = async (ticket: Ticket) => {
    if (isDownloading.value[ticket.id]) return;

    isDownloading.value[ticket.id] = true;

    try {
        console.log('Starting optimized PDF generation for ticket:', ticket.ticket_number);

        // Validate required data
        if (!ticket.ticket_type?.name) {
            throw new Error('Ticket type information is missing');
        }
        if (!props.order.event?.title) {
            throw new Error('Event information is missing');
        }

        // Create a compact temporary div for the ticket content
        const tempDiv = document.createElement('div');
        tempDiv.style.position = 'absolute';
        tempDiv.style.left = '-9999px';
        tempDiv.style.width = '560px'; // Reduced from 800px
        tempDiv.style.backgroundColor = 'white';
        tempDiv.style.padding = '16px'; // Reduced from 40px
        tempDiv.style.fontFamily = 'Arial, sans-serif';

        // Generate optimized QR code for PDF
        let qrCodeDataUrl: string;
        try {
            const QRCode = await import('qrcode');
            qrCodeDataUrl = await QRCode.toDataURL(ticket.ticket_number, {
                width: 128, // Reduced from 250px
                margin: 1, // Reduced margin
                errorCorrectionLevel: 'M',
                type: 'image/png',
                color: {
                    dark: '#000000',
                    light: '#FFFFFF',
                },
            });
        } catch (qrError) {
            console.error('QR code generation failed:', qrError);
            // Create a smaller placeholder image if QR generation fails
            const canvas = document.createElement('canvas');
            canvas.width = 128;
            canvas.height = 128;
            const ctx = canvas.getContext('2d');
            if (ctx) {
                ctx.fillStyle = '#f3f4f6';
                ctx.fillRect(0, 0, 128, 128);
                ctx.fillStyle = '#374151';
                ctx.font = '12px Arial';
                ctx.textAlign = 'center';
                ctx.fillText('QR Code', 64, 60);
                ctx.fillText('Generation Failed', 64, 76);
            }
            qrCodeDataUrl = canvas.toDataURL('image/png');
        }

        // Create compact ticket HTML content with safe property access
        const ticketTypeName = ticket.ticket_type?.name || 'Standard';
        const attendeeName = ticket.attendee_name || 'Unknown';
        const eventTitle = props.order.event?.title || 'Event';
        const eventDate = props.order.event?.start_date ? formatDate(props.order.event.start_date) : 'TBA';
        const eventTime = props.order.event?.start_time ? formatTime(props.order.event.start_time) : 'TBA';
        const ticketPrice = typeof ticket.price === 'number' ? ticket.price : '0.00';

        tempDiv.innerHTML = `
            <div style="border: 1px solid rgb(229, 231, 235); padding: 16px; background: rgb(248, 250, 252);">
                <div style="text-align: center; margin-bottom: 16px;">
                    <h1 style="font-size: 20px; color: rgb(31, 41, 55); margin: 0 0 4px 0;">${eventTitle}</h1>
                    <h3 style="font-size: 14px; font-weight: bold; color: rgb(107, 114, 128); margin: 0;">Event Ticket</h3>
                </div>

                <div style="display: flex; gap: 16px; align-items: center;">
                    <div style="flex: 1;">
                        <div style="margin-bottom: 8px;">
                            <strong style="color: rgb(31, 41, 55); font-size: 12px;">Ticket Type:</strong><br>
                            <span style="color: rgb(107, 114, 128); font-size: 12px;">${ticketTypeName}</span>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <strong style="color: rgb(31, 41, 55); font-size: 12px;">Attendee:</strong><br>
                            <span style="color: rgb(107, 114, 128); font-size: 12px;">${attendeeName}</span>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <strong style="color: rgb(31, 41, 55); font-size: 12px;">Date & Time:</strong><br>
                            <span style="color: rgb(107, 114, 128); font-size: 12px;">${eventDate} at ${eventTime}</span>
                        </div>
                        <div>
                            <strong style="color: rgb(31, 41, 55); font-size: 12px;">Price:</strong>
                            <span style="color: rgb(107, 114, 128); font-size: 12px;">GHS ${ticket.price}</span>
                        </div>
                    </div>

                    <!-- Compact QR Code Section -->
                    <div style="text-align: center; padding: 12px; background: rgb(255, 255, 255); border: 1px solid rgb(229, 231, 235);">
                        <img src="${qrCodeDataUrl}" alt="QR Code" style="width: 128px; height: 128px; display: block;">
                        <p style="margin: 8px 0 0 0; font-size: 10px; color: rgb(107, 114, 128);">Scan at entrance</p>
                    </div>
                </div>

                <div style="background: rgb(249, 250, 251); padding: 8px; margin-top: 16px; border: 1px solid rgb(229, 231, 235);">
                    <p style="font-size: 10px; color: rgb(107, 114, 128); margin: 0; text-align: center;"><strong>ID:</strong> ${ticket.ticket_number}</p>
                </div>
            </div>
        `;

        document.body.appendChild(tempDiv);

        // Convert to canvas with optimized settings
        const canvas = await html2canvas(tempDiv, {
            scale: 3,
            useCORS: true,
            allowTaint: false,
            backgroundColor: '#ffffff',
            logging: false,
            width: 560,
            height: tempDiv.scrollHeight,
            onclone: (clonedDoc) => {
                clonedDoc.querySelectorAll('link[rel="stylesheet"], style').forEach((el) => el.remove());
            },
        });

        document.body.removeChild(tempDiv);

        // Create PDF with custom compact size matching bulk download
        const pdf = new jsPDF('l', 'mm', [148, 105]);

        const imgData = canvas.toDataURL('image/png');

        // Calculate dimensions to fit content exactly
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = pdf.internal.pageSize.getHeight();

        // Scale image to fit page while maintaining aspect ratio
        const canvasAspect = canvas.width / canvas.height;
        const pdfAspect = pdfWidth / pdfHeight;

        let imgWidth, imgHeight;
        if (canvasAspect > pdfAspect) {
            // Canvas is wider - fit to width
            imgWidth = pdfWidth;
            imgHeight = pdfWidth / canvasAspect;
        } else {
            // Canvas is taller - fit to height
            imgHeight = pdfHeight;
            imgWidth = pdfHeight * canvasAspect;
        }

        // Center the image on the page
        const x = (pdfWidth - imgWidth) / 2;
        const y = (pdfHeight - imgHeight) / 2;

        pdf.addImage(imgData, 'PNG', x, y, imgWidth, imgHeight);
        pdf.save(`ticket-${ticket.ticket_number}.pdf`);

        console.log('Optimized PDF saved successfully');
        console.log(ticket.price)
    } catch (error) {
        console.error('Error generating PDF:', error);
        const errorMessage = error instanceof Error ? error.message : 'Unknown error occurred';
        alert(`Failed to generate PDF: ${errorMessage}`);
    } finally {
        isDownloading.value[ticket.id] = false;
    }
};

const downloadAllTickets = async () => {
    if (isDownloadingAll.value) return;

    isDownloadingAll.value = true;
    try {
        console.log('Starting bulk PDF generation for', props.tickets.length, 'tickets');

        // Create PDF with custom compact size (148x105mm - roughly half of A6)
        const pdf = new jsPDF('l', 'mm', [148, 105]);
        let isFirstPage = true;

        const QRCode = await import('qrcode');

        for (const ticket of props.tickets) {
            console.log('Processing ticket:', ticket.ticket_number);

            // Validate ticket data
            if (!ticket.ticket_type?.name) {
                console.warn('Skipping ticket with missing type:', ticket.ticket_number);
                continue;
            }

            if (!isFirstPage) {
                pdf.addPage();
            }
            isFirstPage = false;

            // Create a compact temporary div for the ticket content
            const tempDiv = document.createElement('div');
            tempDiv.style.position = 'absolute';
            tempDiv.style.left = '-9999px';
            tempDiv.style.width = '560px'; // Reduced from 800px
            tempDiv.style.backgroundColor = 'white';
            tempDiv.style.padding = '16px'; // Reduced from 40px
            tempDiv.style.fontFamily = 'Arial, sans-serif';

            // Generate optimized QR code for PDF
            const qrCodeDataUrl = await QRCode.toDataURL(ticket.ticket_number, {
                width: 128, // Reduced from 250px
                margin: 1, // Reduced margin
                errorCorrectionLevel: 'M',
                type: 'image/png',
                color: {
                    dark: '#000000',
                    light: '#FFFFFF',
                },
            });

            // Create ticket HTML content with safe property access
            const ticketTypeName = ticket.ticket_type?.name || 'Standard';
            const attendeeName = ticket.attendee_name || 'Unknown';
            const eventTitle = props.order.event?.title || 'Event';
            const eventDate = props.order.event?.start_date ? formatDate(props.order.event.start_date) : 'TBA';
            const eventTime = props.order.event?.start_time ? formatTime(props.order.event.start_time) : 'TBA';
            const ticketPrice = typeof ticket.price === 'number' ? ticket.price : '0.00';

            tempDiv.innerHTML = `
                <div style="border: 1px solid rgb(229, 231, 235); padding: 16px; background: rgb(248, 250, 252);">
                    <div style="text-align: center; margin-bottom: 16px;">
                        <h1 style="font-size: 20px; color: rgb(31, 41, 55); margin: 0 0 4px 0;">${eventTitle}</h1>
                        <h3 style="font-size: 14px; font-weight: bold; color: rgb(107, 114, 128); margin: 0;">Event Ticket</h3>
                    </div>

                    <div style="display: flex; gap: 16px; align-items: center;">
                        <div style="flex: 1;">
                            <div style="margin-bottom: 8px;">
                                <strong style="color: rgb(31, 41, 55); font-size: 12px;">Ticket Type:</strong><br>
                                <span style="color: rgb(107, 114, 128); font-size: 12px;">${ticketTypeName}</span>
                            </div>
                            <div style="margin-bottom: 8px;">
                                <strong style="color: rgb(31, 41, 55); font-size: 12px;">Attendee:</strong><br>
                                <span style="color: rgb(107, 114, 128); font-size: 12px;">${attendeeName}</span>
                            </div>
                            <div style="margin-bottom: 8px;">
                                <strong style="color: rgb(31, 41, 55); font-size: 12px;">Date & Time:</strong><br>
                                <span style="color: rgb(107, 114, 128); font-size: 12px;">${eventDate} at ${eventTime}</span>
                            </div>
                            <div>
                                <strong style="color: rgb(31, 41, 55); font-size: 12px;">Price:</strong>
                                <span style="color: rgb(107, 114, 128); font-size: 12px;">GHS ${ticket.price}</span>
                            </div>
                        </div>

                        <!-- Compact QR Code Section -->
                        <div style="text-align: center; padding: 12px; background: rgb(255, 255, 255); border: 1px solid rgb(229, 231, 235);">
                            <img src="${qrCodeDataUrl}" alt="QR Code" style="width: 128px; height: 128px; display: block;">
                            <p style="margin: 8px 0 0 0; font-size: 10px; color: rgb(107, 114, 128);">Scan at entrance</p>
                        </div>
                    </div>

                    <div style="background: rgb(249, 250, 251); padding: 8px; margin-top: 16px; border: 1px solid rgb(229, 231, 235);">
                        <p style="font-size: 10px; color: rgb(107, 114, 128); margin: 0; text-align: center;"><strong>ID:</strong> ${ticket.ticket_number}</p>
                    </div>
                </div>
            `;

            document.body.appendChild(tempDiv);

            // Convert to canvas with optimized settings
            const canvas = await html2canvas(tempDiv, {
                scale: 3,
                useCORS: true,
                allowTaint: false,
                backgroundColor: '#ffffff',
                logging: false,
                width: 560,
                height: tempDiv.scrollHeight,
                onclone: (clonedDoc) => {
                    clonedDoc.querySelectorAll('link[rel="stylesheet"], style').forEach((el) => el.remove());
                },
            });

            document.body.removeChild(tempDiv);

            const imgData = canvas.toDataURL('image/png');

            // Calculate dimensions to fit content exactly
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = pdf.internal.pageSize.getHeight();

            // Scale image to fit page while maintaining aspect ratio
            const canvasAspect = canvas.width / canvas.height;
            const pdfAspect = pdfWidth / pdfHeight;

            let imgWidth, imgHeight;
            if (canvasAspect > pdfAspect) {
                // Canvas is wider - fit to width
                imgWidth = pdfWidth;
                imgHeight = pdfWidth / canvasAspect;
            } else {
                // Canvas is taller - fit to height
                imgHeight = pdfHeight;
                imgWidth = pdfHeight * canvasAspect;
            }

            // Center the image on the page
            const x = (pdfWidth - imgWidth) / 2;
            const y = (pdfHeight - imgHeight) / 2;

            pdf.addImage(imgData, 'PNG', x, y, imgWidth, imgHeight);
        }

        console.log('Saving optimized bulk PDF...');
        pdf.save(`tickets-${props.order.order_number}.pdf`);
        console.log('Optimized PDF saved successfully');
    } catch (error) {
        console.error('Error generating bulk PDF:', error);
        const errorMessage = error instanceof Error ? error.message : 'Unknown error occurred';
        alert(`Failed to generate PDF: ${errorMessage}`);
    } finally {
        isDownloadingAll.value = false;
    }
};

const showQRCode = async (ticket: Ticket) => {
    showingQRCode.value = ticket;

    await nextTick();

    if (qrCodeContainer.value) {
        // Clear previous QR code
        qrCodeContainer.value.innerHTML = '';

        // Generate new QR code using ticket number directly
        const QRCode = await import('qrcode');

        const canvas = await QRCode.toCanvas(ticket.ticket_number, {
            width: 200,
            margin: 2,
        });

        qrCodeContainer.value.appendChild(canvas);
    }
};

const goToPayment = () => {
    // Navigate to ticket confirmation page where payment can be completed
    window.location.href = route('orders.confirmation', props.order.order_number);
};
</script>
