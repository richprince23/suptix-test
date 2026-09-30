<template>
    <Head :title="props.order?.event?.title ? `Booking Confirmed - ${props.order.event.title}` : 'Booking Confirmed'" />
    <main class="bg-background">
        <div class="mx-auto max-w-4xl px-8 py-16 sm:px-12 lg:px-16">
            <!-- Header -->
            <div class="mb-8 text-center">
                <div v-if="order.status === 'confirmed'" class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div v-else class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100">
                    <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>

                <h1 v-if="order.status === 'confirmed'" class="mb-2 text-3xl font-bold text-green-800">Purchase Successful!</h1>
                <h1 v-else class="mb-2 text-3xl font-bold text-yellow-800">Order Created!</h1>

                <p v-if="order.status === 'confirmed'" class="text-gray-600">Your tickets have been confirmed. Check your email for confirmation.</p>
                <p v-else class="text-gray-600">Your tickets have been reserved. Complete payment to secure your tickets.</p>
            </div>

            <!-- Order Details -->
            <div class="mb-6 rounded-lg bg-card p-6 shadow-md">
                <div class="mb-6 border-b pb-4">
                    <h2 class="mb-2 text-xl font-semibold">Order Details</h2>
                    <div class="flex flex-col justify-between gap-4 text-sm md:flex-row">
                        <div class="first">
                            <div><span class="font-medium">Order Number:</span> {{ order.order_number }}</div>
                            <div><span class="font-medium">Order Date:</span> {{ formatDate(order.created_at) }}</div>
                        </div>
                        <div class="second">
                            <div><span class="font-medium">Email: &nbsp;</span> {{ order.buyer_email }}</div>
                            <div v-if="order.buyer_phone"><span class="font-medium">Phone: &nbsp;</span> {{ order.buyer_phone }}</div>
                        </div>
                    </div>
                </div>

                <!-- Event Information -->
                <div class="mb-6 border-b pb-4">
                    <h3 class="mb-3 text-lg font-semibold">Event Information</h3>
                    <div class="flex items-start gap-4">
                        <img
                            v-if="order.event.cover_image_url"
                            :src="order.event.cover_image_url"
                            :alt="order.event.title"
                            class="h-20 w-20 rounded-lg object-cover"
                        />
                        <div>
                            <h4 class="text-lg font-semibold">{{ order.event.title }}</h4>
                            <p class="text-gray-600">{{ order.event.venue_name }}</p>
                            <p class="text-gray-600">{{ formatDate(order.event.start_date) }} at {{ formatTime(order.event.start_time) }}</p>
                        </div>
                    </div>
                    <Button
                        v-if="order.status === 'confirmed'"
                        @click="downloadTickets"
                        :disabled="isDownloadingAll"
                        variant="link"
                        size="lg"
                        class="my-4 cursor-pointer bg-primary px-4 py-2 text-white transition-colors hover:from-blue-700 hover:to-purple-700 disabled:opacity-50"
                    >
                        {{ isDownloadingAll ? 'Generating PDFs...' : 'Download All Tickets' }}
                    </Button>
                </div>

                <!-- Ticket Details -->
                <div class="mb-6">
                    <h3 class="mb-3 text-lg font-semibold">Your Tickets</h3>
                    <div class="space-y-4">
                        <div v-for="(ticket, index) in tickets" :key="ticket.id" class="rounded-lg border bg-background p-4">
                            <div class="mb-3 flex items-start justify-between">
                                <div>
                                    <h4 class="font-semibold">{{ ticket.ticket_type.name }}</h4>
                                    <!-- <p class="text-sm text-gray-600">{{ ticket.ticket_number }}</p> -->
                                    <p class="text-sm text-gray-600">**************</p>
                                </div>
                                <div class="flex flex-col items-end gap-2 text-right">
                                    <div>
                                        <div class="font-semibold">GHS {{ ticket.price }}</div>
                                        <div class="text-sm text-gray-600">{{ ticket.status }}</div>
                                    </div>
                                    <Button
                                        v-if="order.status === 'confirmed'"
                                        @click="downloadTicketPDF(ticket, index + 1)"
                                        :disabled="isDownloading[ticket.id]"
                                        variant="outline"
                                        size="sm"
                                        class="text-xs"
                                    >
                                        {{ isDownloading[ticket.id] ? 'Generating...' : 'Download PDF' }}
                                    </Button>
                                    <span v-else class="text-xs font-medium text-gray-500">Payment Required</span>
                                </div>
                            </div>

                            <div class="text-sm">
                                <div class="font-medium">Attendee: {{ ticket.attendee_name }}</div>
                                <div v-if="ticket.attendee_email" class="text-gray-600">Email: {{ ticket.attendee_email }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="border-t pt-4">
                    <h3 class="mb-3 text-lg font-semibold">Order Summary</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Total Tickets:</span>
                            <span>{{ tickets.length }}</span>
                        </div>

                        <!-- Show subtotal and discount if discount was applied -->
                        <template v-if="order.discount && order.discount_amount && order.discount_amount > 0">
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span>GHS {{ order.subtotal_amount }}</span>
                            </div>
                            <div class="flex justify-between text-green-600">
                                <span>Discount ({{ order.discount.code }}):</span>
                                <span>-GHS {{ order.discount_amount }}</span>
                            </div>
                            <div class="text-sm text-gray-600 italic">
                                {{ order.discount.name }}
                                ({{ order.discount.type === 'percentage' ? order.discount.value + '%' : 'GHS ' + order.discount.value + ' off' }})
                            </div>
                        </template>

                        <div class="flex justify-between border-t pt-2 text-lg font-bold">
                            <span>Total Amount:</span>
                            <span>GHS {{ order.total_amount }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Section for Pending Orders -->
            <div v-if="order.status === 'pending'" class="mb-6 rounded-lg border border-indigo-200 bg-background p-6">
                <h3 class="mb-4 text-lg font-semibold text-foreground">Complete Your Payment</h3>
                <p class="mb-6 text-foreground/80">Your tickets are reserved for 15 minutes. Complete payment now to secure your tickets.</p>

                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <div class="text-left">
                        <p class="text-sm text-foreground">Amount to Pay:</p>
                        <p class="text-2xl font-bold text-primary">GH₵{{ order.total_amount }}</p>
                    </div>

                    <div class="flex gap-3">
                        <Button
                            @click="initializePayment"
                            :disabled="isProcessingPayment"
                            class="transform rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-3 font-semibold text-white transition-all hover:scale-105 hover:from-indigo-700 hover:to-purple-700 disabled:transform-none disabled:opacity-50"
                        >
                            <span v-if="isProcessingPayment" class="flex items-center">
                                <svg
                                    class="mr-3 -ml-1 h-5 w-5 animate-spin text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                Processing...
                            </span>
                            <span v-else>Pay with Paystack</span>
                        </Button>
                    </div>
                </div>

                <div v-if="paymentError" class="mt-4 rounded-lg border border-red-300 bg-red-100 p-3">
                    <p class="text-sm text-red-700">{{ paymentError }}</p>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="rounded-lg bg-card p-6 shadow-xs">
                <h3 class="mb-3 text-lg font-semibold text-foreground">Next Steps</h3>
                <ul class="space-y-2 text-sm text-foreground">
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500">•</span>
                        <span>You will receive a confirmation email with your tickets and QR codes</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500">•</span>
                        <span
                            >Save this order number for your records: <strong>{{ order.order_number }}</strong></span
                        >
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500">•</span>
                        <span>Present your tickets (digital or printed) at the event entrance</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500">•</span>
                        <span>Arrive early to allow time for check-in and entry</span>
                    </li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="mt-8 space-y-4 text-center">
                <template v-if="order.status === 'confirmed'">
                    <Button
                        @click="downloadTickets"
                        :disabled="isDownloadingAll"
                        variant="link"
                        size="lg"
                        class="mr-4 cursor-pointer bg-primary px-4 py-2 text-white transition-colors hover:from-blue-700 hover:to-purple-700 disabled:opacity-50"
                    >
                        {{ isDownloadingAll ? 'Generating PDFs...' : 'Download All Tickets' }}
                    </Button>
                </template>
                <Button @click="goToEvents" variant="outline" class="cursor-pointer" size="lg"> Browse More Events </Button>
            </div>
        </div>
    </main>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';
import { reactive, ref } from 'vue';

interface Event {
    id: number;
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
    attendee_email?: string | null;
    price: number;
    status: string;
    ticket_type: TicketType;
}

interface Discount {
    id: number;
    code: string;
    name: string;
    type: string;
    value: number;
}

interface Order {
    id: number;
    order_number: string;
    buyer_email: string;
    buyer_phone?: string;
    subtotal_amount?: number;
    discount_id?: number;
    discount_amount?: number;
    total_amount: number;
    status: string;
    created_at: string;
    event: Event;
    discount?: Discount;
}

const props = defineProps<{
    order: Order;
    tickets: Ticket[];
}>();

const isDownloading = reactive<Record<number, boolean>>({});
const isDownloadingAll = ref(false);
const isProcessingPayment = ref(false);
const paymentError = ref('');

const downloadTicketPDF = async (ticket: Ticket, ticketNumber: number) => {
    if (isDownloading[ticket.id]) return;

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
        const QRCode = await import('qrcode');
        const qrCodeDataUrl = await QRCode.toDataURL(ticket.ticket_number, {
            width: 250,
            margin: 2,
        });

        // Create ticket HTML content with CSS-compatible colors
        tempDiv.innerHTML = `
            <div style="border: 2px solid rgb(229, 231, 235); border-radius: 0px; padding: 32px; background: rgb(248, 250, 252);">
                <div style="text-align: center; margin-bottom: 32px;">
                    <h1 style="font-size: 28px; font-weight: bold; color: rgb(31, 41, 55); margin: 0 0 8px 0;">Event Ticket</h1>
                    <p style="font-size: 18px; color: rgb(107, 114, 128); margin: 0;">${props.order.event.title}</p>
                </div>

                <div style="margin-bottom: 32px;">
                    <div style="margin-bottom: 32px;">
                        <h3 style="font-size: 16px; font-weight: bold; color: rgb(55, 65, 81); margin: 0 0 16px 0;">Event Details</h3>
                        <div style="display: flex; flex-wrap: wrap; gap: 16px;">
                            <div style="flex: 1; min-width: 200px;">
                                <p style="margin: 8px 0; color: rgb(75, 85, 99);"><strong>Date:</strong> ${formatDate(props.order.event.start_date)}</p>
                                <p style="margin: 8px 0; color: rgb(75, 85, 99);"><strong>Time:</strong> ${formatTime(props.order.event.start_time)}</p>
                            </div>
                            <div style="flex: 1; min-width: 200px;">
                                ${props.order.event.venue_name ? `<p style="margin: 8px 0; color: rgb(75, 85, 99);"><strong>Venue:</strong> ${props.order.event.venue_name}</p>` : ''}
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
                            <p style="margin: 4px 0; color: rgb(75, 85, 99);"><strong>Order:</strong> ${props.order.order_number}</p>
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

const downloadTickets = async () => {
    if (isDownloadingAll.value) return;

    isDownloadingAll.value = true;
    try {
        // Create a single PDF with all tickets
        const pdf = new jsPDF('p', 'mm', 'a4');
        let isFirstPage = true;

        // Generate QR code module once
        const QRCode = await import('qrcode');

        for (let i = 0; i < props.tickets.length; i++) {
            const ticket = props.tickets[i];

            // Add new page for each ticket (except the first one)
            if (!isFirstPage) {
                pdf.addPage();
            }
            isFirstPage = false;

            // Create temporary div for this ticket
            const tempDiv = document.createElement('div');
            tempDiv.style.position = 'absolute';
            tempDiv.style.left = '-9999px';
            tempDiv.style.width = '800px';
            tempDiv.style.backgroundColor = 'white';
            tempDiv.style.padding = '40px';
            tempDiv.style.fontFamily = 'Arial, sans-serif';

            // Generate QR code for this ticket using ticket number directly
            const qrCodeDataUrl = await QRCode.toDataURL(ticket.ticket_number, {
                width: 150,
                margin: 1,
            });

            // Create ticket HTML content
            tempDiv.innerHTML = `
                <div style="border: 2px solid rgb(229, 231, 235); border-radius: 0px; padding: 32px; background: rgb(248, 250, 252);">
                    <div style="text-align: center; margin-bottom: 32px;">
                        <h1 style="font-size: 28px; font-weight: bold; color: rgb(31, 41, 55); margin: 0 0 8px 0;">Event Ticket</h1>
                        <p style="font-size: 18px; color: rgb(107, 114, 128); margin: 0;">${props.order.event.title}</p>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px;">
                        <div style="flex: 1;">
                            <h3 style="font-size: 16px; font-weight: bold; color: rgb(55, 65, 81); margin: 0 0 16px 0;">Event Details</h3>
                            <p style="margin: 8px 0; color: rgb(75, 85, 99);"><strong>Date:</strong> ${formatDate(props.order.event.start_date)}</p>
                            <p style="margin: 8px 0; color: rgb(75, 85, 99);"><strong>Time:</strong> ${formatTime(props.order.event.start_time)}</p>
                            ${props.order.event.venue_name ? `<p style="margin: 8px 0; color: rgb(75, 85, 99);"><strong>Venue:</strong> ${props.order.event.venue_name}</p>` : ''}
                        </div>

                        <div style="text-align: center; margin-left: 32px;">
                            <img src="${qrCodeDataUrl}" alt="QR Code" style="width: 150px; height: 150px; border: 1px solid rgb(209, 213, 219); border-radius: 0px;">
                            <p style="font-size: 12px; color: rgb(107, 114, 128); margin: 8px 0 0 0;">Scan at entrance</p>
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
                                <p style="margin: 4px 0; color: rgb(75, 85, 99);"><strong>Ticket #:</strong> ${i + 1}</p>
                                <p style="margin: 4px 0; color: rgb(75, 85, 99);"><strong>Order:</strong> ${props.order.order_number}</p>
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

            // Convert to canvas
            const canvas = await html2canvas(tempDiv, {
                scale: 2,
                useCORS: true,
                allowTaint: false,
                backgroundColor: '#ffffff',
                logging: false,
                ignoreElements: (element) => {
                    return element.tagName === 'STYLE' || element.tagName === 'SCRIPT';
                },
            });

            document.body.removeChild(tempDiv);

            // Add image to PDF
            const imgData = canvas.toDataURL('image/png');
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

            pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
        }

        // Save the combined PDF
        pdf.save(`tickets-${props.order.order_number}.pdf`);
    } catch (error) {
        console.error('Error generating PDF:', error);
        alert('Failed to generate PDF. Please try again.');
    } finally {
        isDownloadingAll.value = false;
    }
};

const initializePayment = async () => {
    if (isProcessingPayment.value) return;

    isProcessingPayment.value = true;
    paymentError.value = '';

    try {
        const response = await axios.post(route('payment.initialize'), {
            order_id: props.order.id,
        });

        if (response.data.success) {
            // Redirect to Paystack payment page
            window.location.href = response.data.data.authorization_url;
        } else {
            paymentError.value = response.data.message || 'Failed to initialize payment';
        }
    } catch (error: any) {
        console.error('Payment initialization error:', error);

        if (error.response?.status === 419) {
            paymentError.value = 'Session expired. Please refresh the page and try again.';
        } else if (error.response?.data?.message) {
            paymentError.value = error.response.data.message;
        } else {
            paymentError.value = 'An error occurred while initializing payment. Please try again.';
        }
    } finally {
        isProcessingPayment.value = false;
    }
};

const goToEvents = () => {
    router.visit(route('events.index'));
};
</script>
