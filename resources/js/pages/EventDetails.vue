<template>
    <Toaster :close-button="true" />

    <!-- Floating NavBar over hero -->
    <div class="fixed top-0 left-0 right-0 z-50">
        <NavBar text-color="text-white" background-color="bg-transparent"
            class="w-full backdrop-blur-md bg-black/20 border-b border-white/10" />
    </div>

    <!-- Hero -->
    <HeroSection :title="event.title" :cover-image-url="event.cover_image_url" :status="currentStatus"
        :status-classes="modernStatusClasses" :tags="event.tags"
        :formatted-date="event.end_date ? `${formatDate(event.start_date)} – ${formatDate(event.end_date)}` : formatDate(event.start_date)"
        :formatted-time="event.end_date && event.end_date !== event.start_date ? `Starts ${formatTime(displayStartTime!)}` : `${formatTime(displayStartTime!)}${displayEndTime ? ' – ' + formatTime(displayEndTime) : ''}`"
        :location="event.mode !== 'online' ? (event.venue_name || undefined) : (event.platform_name || undefined)"
        :mode="event.mode" :show-ticket-cta="canPurchaseTickets" @get-tickets="scrollToTickets"
        @share="copyLink" />

    <!-- Body -->
    <main class="bg-background">
        <div class="max-w-7xl mx-auto px-6 py-16">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 items-start">
                <!-- Left: main content -->
                <div class="lg:col-span-2 space-y-16">

                    <!-- About -->
                    <AboutSection :description="event.description" />

                    <!-- Ticket section -->
                    <div class="border-t border-border">
                        <div class="max-w-7xl mx-auto px-6 py-16">
                            <div class="mb-12">
                                <p class="text-xs font-semibold uppercase tracking-widest text-foreground/40 mb-3">
                                    [Plan]</p>
                                <h2 class="text-3xl md:text-5xl font-bold text-foreground leading-tight">
                                    Unlock the Experience<br /><span class="text-foreground/40">That Suits You</span>
                                </h2>
                            </div>
                            <div class="max-w-2xl">

                                <!-- Ticket Purchase Form -->
                                <div class="mb-8 lgb p-6 rounded" data-section="tickets">
                                    <!-- General Error Display -->
                                    <div v-if="errors.error"
                                        class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20"
                                                    fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-red-800">Purchase Error</h3>
                                                <p class="mt-1 text-sm text-red-700">{{ errors.error }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Ticket Availability Notice -->
                                    <div v-if="!canPurchaseTickets"
                                        class=" rounded border border-orange-200 bg-orange-50/10 p-6 text-center">
                                        <svg class="mx-auto h-12 w-12 text-orange-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-orange-900">{{ unavailabilityMessage }}
                                        </h3>
                                        <p class="mt-1 text-sm text-orange-700">Please contact the Organizer for any
                                            assistance.</p>
                                        <a v-if="event.organizer?.phone" :href="'tel:' + event.organizer?.phone"
                                            class="mt-4">Call
                                            Organizer</a>
                                    </div>

                                    <div v-else class="space-y-6 form-section">
                                        <!-- Ticket Selection -->
                                        <div class="form-section">
                                            <h2 class="mb-4 text-xl font-semibold">Select Tickets</h2>
                                            <div v-if="availableTicketTypes.length === 0"
                                                class="rounded border border-red-200 bg-red-50 p-6 text-center">
                                                <svg class="mx-auto h-12 w-12 text-red-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <h3 class="mt-2 text-sm font-medium text-red-900">No tickets available
                                                    for this
                                                    event.
                                                </h3>
                                            </div>
                                            <TicketSelector v-else :ticket-types="availableTicketTypes"
                                                @update="handleTicketSelection" :disabled="!canPurchaseTickets" />
                                        </div>

                                        <!-- Quick Checkout -->
                                        <div v-if="hasSelectedTickets" class="border-t pt-6 form-section">
                                            <div class="">
                                                <h3 class="text-lg font-semibold text-foreground mb-4">Quick Checkout
                                                </h3>

                                                <!-- Order Summary -->
                                                <div class="bg-whites rounded-lg p-4s mb-4">
                                                    <h4 class="font-medium text-foreground mb-3">Your Order</h4>
                                                    <div class="space-y-2">
                                                        <div v-for="selection in ticketSelections"
                                                            :key="selection.ticket_type_id"
                                                            class="flex justify-between items-center">
                                                            <div>
                                                                <span
                                                                    class="font-medium">{{ getTicketTypeName(selection.ticket_type_id) }}</span>
                                                                <span class="text-foreground/70 ml-2">×
                                                                    {{ selection.quantity }}</span>
                                                            </div>
                                                            <span class="font-semibold">GHS
                                                                {{ (getTicketTypePrice(selection.ticket_type_id) * selection.quantity) }}</span>
                                                        </div>
                                                    </div>

                                                    <!-- Discount Code -->
                                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                                        <div class="flex gap-2 mb-2">
                                                            <Input v-model="discountCode"
                                                                placeholder="Discount code (optional)"
                                                                :disabled="validationDiscount.loading || !canPurchaseTickets"
                                                                class="flex-1 text-sm rounded pri-borders" />
                                                            <Button type="button" @click="validateDiscountCode"
                                                                :disabled="!discountCode || validationDiscount.loading || !canPurchaseTickets"
                                                                class="rounded pri-borders bg-primary">
                                                                {{ validationDiscount.loading ? 'Applying...' : 'Apply' }}
                                                            </Button>
                                                        </div>

                                                        <div v-if="validationDiscount.error"
                                                            class="text-sm text-red-600">
                                                            {{ validationDiscount.error }}
                                                        </div>

                                                        <div v-if="appliedDiscount"
                                                            class="flex items-center justify-between text-sm text-green-600">
                                                            <span>{{ appliedDiscount.name }} discount applied</span>
                                                            <div class="flex items-center gap-2">
                                                                <span>-GHS {{ appliedDiscount.amount }}</span>
                                                                <Button type="button" @click="clearDiscount"
                                                                    variant="ghost" size="sm"
                                                                    class="cursor-pointer p-1 text-red-500 hover:text-red-700">
                                                                    ✕
                                                                </Button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Total -->
                                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                                        <div v-if="appliedDiscount"
                                                            class="flex justify-between text-sm text-foreground/70 mb-1">
                                                            <span>Subtotal:</span>
                                                            <span>GHS {{ subtotalAmount }}</span>
                                                        </div>
                                                        <div v-if="appliedDiscount"
                                                            class="flex justify-between text-sm text-green-600 mb-2">
                                                            <span>Discount:</span>
                                                            <span>-GHS {{ appliedDiscount.amount }}</span>
                                                        </div>
                                                        <div
                                                            class="flex justify-between items-center text-lg font-bold text-foreground">
                                                            <span>Total:</span>
                                                            <span>GHS {{ finalAmount }}</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Continue to Details Button -->
                                                <div class="w-full text-center">
                                                    <Button @click="proceedToBuyerForm"
                                                        :disabled="processing || !canPurchaseTickets"
                                                        class="rounded cursor-pointer bg-primary cursor-pointer w-full py-5 md:w-1/2 mx-auto disabled:opacity-50 disabled:cursor-not-allowed">
                                                        Continue - GHS {{ finalAmount }}
                                                    </Button>
                                                </div>

                                                <p class="text-xs text-foreground/70 text-center mt-3">
                                                    Next: Enter buyer and attendee information
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Social Links -->
                                <div class="mb-2  bg-whites p-6 rounded">
                                    <h3 class="mb-4 text-xl font-semibold">Share this event</h3>
                                    <div class="flex flex-wrap gap-4">
                                        <a :href="twitterShareUrl"
                                            class=" rounded p-3 bg-card hover:bg-gray-50 transition-colors flex items-center justify-center"
                                            target="_blank" rel="noopener" title="Share on Twitter">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                                            </svg>
                                        </a>
                                        <a :href="facebookShareUrl"
                                            class=" rounded p-3 bg-card hover:bg-gray-50 transition-colors flex items-center justify-center"
                                            target="_blank" rel="noopener" title="Share on Facebook">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                            </svg>
                                        </a>
                                        <a :href="linkedinShareUrl"
                                            class=" rounded p-3 bg-card hover:bg-gray-50 transition-colors flex items-center justify-center"
                                            target="_blank" rel="noopener" title="Share on LinkedIn">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                                            </svg>
                                        </a>

                                        <Button variant="default" @click="copyLink"
                                            class="rounded-full cursor-pointer bg-primary hover:bg-primary/60 flex items-center gap-2 px-6">
                                            <LinkIcon class="h-4 w-4" /> Copy Link
                                        </Button>
                                    </div>
                                </div>
                            </div><!-- /max-w-2xl -->
                        </div><!-- /container -->
                    </div><!-- /ticket section -->

                </div><!-- /left col -->

                <!-- Right: sticky sidebar -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24 space-y-6">

                        <!-- Organizer -->
                        <Link v-if="event.organizer" :href="route('organizers.show', event.organizer.slug)"
                            class="flex items-center gap-3 p-4 border border-border rounded-xl bg-card">
                            <div class="flex-shrink-0">
                                <img v-if="event.organizer.logo_url" :src="event.organizer.logo_url"
                                    class="h-12 w-12 object-cover rounded-lg" alt="organizer_logo" />
                                <div v-else
                                    class="h-12 w-12 bg-primary rounded-lg flex items-center justify-center text-white font-black text-xl">
                                    {{ event.organizer.name.slice(0, 1) }}
                                </div>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-foreground/50">Organized by</p>
                                <p class="font-semibold text-foreground truncate">{{ event.organizer.name }}</p>
                                <a v-if="event.organizer.website" :href="event.organizer.website" target="_blank"
                                    class="text-xs text-primary hover:underline truncate block"
                                    @click.stop>{{ event.organizer.website }}</a>
                            </div>
                        </Link>

                        <!-- Event meta -->
                        <div class="border border-border rounded-xl bg-card p-4 space-y-3">
                            <div class="flex items-start gap-3">
                                <Calendar class="w-4 h-4 text-primary mt-0.5 flex-shrink-0" />
                                <div>
                                    <p class="text-xs text-foreground/50 mb-0.5">Date</p>
                                    <p class="text-sm font-medium text-foreground">
                                        {{ formatDate(event.start_date) }}<span v-if="event.end_date"> –
                                            {{ formatDate(event.end_date) }}</span></p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <Clock3 class="w-4 h-4 text-primary mt-0.5 flex-shrink-0" />
                                <div>
                                    <p class="text-xs text-foreground/50 mb-0.5">Time</p>
                                    <p class="text-sm font-medium text-foreground">
                                        <template v-if="event.end_date && event.end_date !== event.start_date">Starts
                                            {{ formatTime(displayStartTime!) }}</template>
                                        <template v-else>{{ formatTime(displayStartTime!) }}<span v-if="displayEndTime">
                                                – {{ formatTime(displayEndTime) }}</span></template>
                                    </p>
                                </div>
                            </div>
                            <div v-if="event.mode !== 'online' && event.venue_name" class="flex items-start gap-3">
                                <MapPin class="w-4 h-4 text-primary mt-0.5 flex-shrink-0" />
                                <div>
                                    <p class="text-xs text-foreground/50 mb-0.5">Location</p>
                                    <p class="text-sm font-medium text-foreground">{{ event.venue_name }}</p>
                                    <p v-if="event.venue_address" class="text-xs text-foreground/60 mt-0.5">
                                        {{ event.venue_address }}</p>
                                </div>
                            </div>
                            <div v-if="event.mode !== 'in-person' && event.online_link" class="flex items-start gap-3">
                                <LinkIcon class="w-4 h-4 text-primary mt-0.5 flex-shrink-0" />
                                <div>
                                    <p class="text-xs text-foreground/50 mb-0.5">Platform</p>
                                    <p class="text-sm font-medium text-foreground">{{ event.platform_name }}</p>
                                    <a v-if="event.is_public_link" :href="event.online_link" target="_blank"
                                        class="text-xs text-primary hover:underline break-all">{{ event.online_link }}</a>
                                    <p v-else class="text-xs text-foreground/60">Link shared after registration</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <Presentation class="w-4 h-4 text-primary mt-0.5 flex-shrink-0" />
                                <div>
                                    <p class="text-xs text-foreground/50 mb-0.5">Mode</p>
                                    <p class="text-sm font-medium text-foreground uppercase">{{ event.mode }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div v-if="event.tags && event.tags.length" class="flex flex-wrap gap-2">
                            <span v-for="tag in event.tags" :key="tag"
                                class="text-xs bg-primary/10 text-primary px-3 py-1.5 rounded-full font-medium">{{ tag }}</span>
                        </div>

                        <!-- Share -->
                        <div class="border border-border rounded-xl bg-card p-4">
                            <p class="text-xs font-semibold uppercase tracking-widest text-foreground/40 mb-3">Share</p>
                            <div class="flex flex-wrap gap-2">
                                <a :href="twitterShareUrl" target="_blank" rel="noopener"
                                    class="p-2.5 rounded-lg bg-muted hover:bg-muted/70 transition-colors">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                                    </svg>
                                </a>
                                <a :href="facebookShareUrl" target="_blank" rel="noopener"
                                    class="p-2.5 rounded-lg bg-muted hover:bg-muted/70 transition-colors">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                    </svg>
                                </a>
                                <a :href="linkedinShareUrl" target="_blank" rel="noopener"
                                    class="p-2.5 rounded-lg bg-muted hover:bg-muted/70 transition-colors">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                                    </svg>
                                </a>
                                <button @click="copyLink"
                                    class="p-2.5 rounded-lg bg-muted hover:bg-muted/70 transition-colors cursor-pointer">
                                    <LinkIcon class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                    </div><!-- /sticky -->
                </div><!-- /right col -->

            </div><!-- /grid -->
        </div><!-- /container -->



    </main>

    <!-- Footer -->
    <Footer />
</template>

<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';
import HeroSection from '@/components/event/HeroSection.vue';
import AboutSection from '@/components/event/AboutSection.vue';
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import { Calendar, Clock3, LinkIcon, MapPin, Presentation } from 'lucide-vue-next';
import { toast, Toaster } from 'vue-sonner';
import 'vue-sonner/style.css';
import TicketSelector from '@/components/TicketSelector.vue';
import axios from 'axios';


// function handleClick() {
//     // openToast.value = !openToast.value
//     toast("Link Copied!", {
//         description: "Link has been copied to clipboard"
//     })
// }

interface Organizer {
    id: number;
    name: string;
    phone?: string;
    email?: string;
    description?: string;
    address?: string;
    city?: string;
    state?: string;
    logo_url?: string;
    banner_url?: string;
    website?: string;
    slug?: string;
}

interface TicketType {
    id: number;
    name: string;
    description?: string;
    price: number;
    quantity: number;
    available_quantity?: number;
    sales_start?: string;
    sales_end?: string;
}

interface EventFormField {
    id: number;
    label: string;
    field_type: 'text' | 'textarea' | 'select' | 'radio' | 'checkbox';
    options?: { value: string }[] | null;
    placeholder?: string;
    is_required: boolean;
    sort_order: number;
}

interface Event {
    id: number;
    slug: string;
    title: string;
    mode?: string;
    description?: string;
    cover_image?: string;
    cover_image_url?: string;
    start_date: string;
    start_time?: string;
    end_date?: string;
    end_time?: string;
    event_type?: string;
    status: string;
    tags?: string[];
    venue_name?: string;
    venue_address?: string;
    venue_gps_code?: string;
    venue_capacity?: number;
    venue_phone?: string;
    form_fields?: EventFormField[];
    online_link?: string;
    platform_name?: string;
    is_public_link?: boolean;
    organizer?: Organizer;
    ticketTypes?: TicketType[];
}

interface TicketSelection {
    ticket_type_id: number;
    quantity: number;
}

const props = defineProps<{
    event: Event;
    map_key?: string;
    map_id?: string;
    ticketTypes?: TicketType[];
    errors?: Record<string, string>;
}>();

const displayStartTime = computed(() => props.event.start_time);

const displayEndTime = computed(() => props.event.end_time);

// Debug: Log the received event data
// console.log('EventDetails received event:', props.event);
// console.log('Event ticket types:', props.event.ticketTypes || 'No ticketTypes property');

// Purchase form state
const processing = ref(false);
const ticketSelections = ref<TicketSelection[]>([]);
const discountCode = ref('');
const appliedDiscount = ref<any>(null);
const validationDiscount = reactive({
    loading: false,
    error: '',
});
// Purchase form
const purchaseForm = reactive({
    buyer_email: '',
    buyer_phone: '',
    discount_id: null as number | null,
    honeypot: '',
});

const errors = computed(() => props.errors || {});
// Calculate current status based on event timing (same logic as backend)
const currentStatus = computed(() => {
    // Don't auto-update certain statuses
    if (['draft', 'canceled'].includes(props.event.status)) {
        return props.event.status;
    }

    const now = new Date();
    const eventStart = new Date(`${props.event.start_date}T${props.event.start_time || '00:00:00'}`);

    // Special case: If event has no end time, mark as past after 24 hours from start
    if (!props.event.end_time) {
        const eventStartPlus24 = new Date(eventStart.getTime() + (24 * 60 * 60 * 1000));
        if (now >= eventStartPlus24) {
            return 'past'; // Event with no end time is considered completed after 24 hours
        }
        if (now >= eventStart) {
            return 'live'; // Event is live from start until 24 hours later
        }
    } else {
        const eventEnd = new Date(`${props.event.end_date || props.event.start_date}T${props.event.end_time}`);

        // Check if event has ended
        if (now >= eventEnd) {
            return 'past'; // Event has ended
        }

        // Check if event is currently happening
        if (now >= eventStart && now < eventEnd) {
            return 'live'; // Event is currently happening
        }
    }

    // Event is in the future, return original status
    return props.event.status;
});

const modernStatusClasses = computed(() => {
    switch (currentStatus.value) {
        case 'published':
            return 'bg-green-500 border-green-400/30 text-white';
        case 'draft':
            return 'bg-gray-500 border-gray-400/30 text-white';
        case 'live':
            return 'bg-red-500 border-red-400/30 text-white';
        case 'past':
            return 'bg-foreground/70 border-gray-500/30 text-white';
        case 'canceled':
            return 'bg-red-500 border-red-400/30 text-white';
        default:
            return 'bg-gray-500 border-gray-400/30 text-white';
    }
});


const scrollToTickets = () => {
    // Small delay to ensure DOM is updated
    setTimeout(() => {
        const ticketSection = document.querySelector('[data-section="tickets"]') as HTMLElement;
        if (ticketSection) {
            ticketSection.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
                inline: 'nearest'
            });

            // Add a subtle highlight effect
            ticketSection.style.transition = 'box-shadow 0.3s ease';
            ticketSection.style.boxShadow = '0 0 0 2px rgba(59, 130, 246, 0.3)';
            setTimeout(() => {
                ticketSection.style.boxShadow = '';
            }, 2000);
        }
    }, 100);
};

// Purchasability validation
const canPurchaseTickets = computed(() => {
    // Check if event status allows purchases
    if (!['published', 'live'].includes(currentStatus.value)) {
        return false;
    }

    // Check if event has available tickets
    const hasAvailableTickets = availableTicketTypes.value.some(ticketType => {
        const availableQty = ticketType.available_quantity ?? ticketType.quantity;
        return availableQty > 0;
    });

    if (!hasAvailableTickets) {
        return false;
    }

    // Check if ticket sales are within the allowed time window
    const now = new Date();

    // Check if any ticket type has sales window restrictions
    const hasValidSalesWindow = availableTicketTypes.value.some(ticketType => {
        if (ticketType.sales_start && new Date(ticketType.sales_start) > now) {
            return false; // Sales haven't started yet
        }
        if (ticketType.sales_end && new Date(ticketType.sales_end) < now) {
            return false; // Sales have ended
        }
        return true;
    });

    return hasValidSalesWindow;
});

// Message for when tickets are not available
const unavailabilityMessage = computed(() => {
    if (currentStatus.value === 'past') {
        return 'This event has already ended.';
    } else if (currentStatus.value === 'canceled') {
        return 'This event has been canceled.';
    } else if (currentStatus.value === 'draft') {
        return 'This event is not yet available.';
    } else {
        // Check ticket-specific issues
        const hasTickets = availableTicketTypes.value.length > 0;
        if (!hasTickets) {
            return 'No tickets are available for this event.';
        }

        const hasAvailableQuantity = availableTicketTypes.value.some(ticketType => {
            const availableQty = ticketType.available_quantity ?? ticketType.quantity;
            return availableQty > 0;
        });
        if (!hasAvailableQuantity) {
            return 'All tickets are sold out.';
        }

        const now = new Date();
        const allSalesNotStarted = availableTicketTypes.value.every(ticketType =>
            ticketType.sales_start && new Date(ticketType.sales_start) > now
        );
        if (allSalesNotStarted) {
            return 'Ticket sales have not started yet.';
        }

        const allSalesEnded = availableTicketTypes.value.every(ticketType =>
            ticketType.sales_end && new Date(ticketType.sales_end) < now
        );
        if (allSalesEnded) {
            return 'Ticket sales have ended.';
        }

        return 'Tickets are not currently available.';
    }

    return 'This event is not available for booking.';
});


const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        weekday: 'long',
    });
};

const formatTime = (timeString: string): string => {
    return new Date(`2000-01-01T${timeString}`).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
        hourCycle: 'h12',
        timeZone: 'GMT',
        timeZoneName: 'shortOffset'
    });
};


// Computed properties for purchase form
const availableTicketTypes = computed(() => {
    return props.ticketTypes || props.event.ticketTypes || [];
});

const hasSelectedTickets = computed(() => {
    return ticketSelections.value.length > 0;
});

const subtotalAmount = computed(() => {
    return ticketSelections.value.reduce((total, selection) => {
        const ticketType = availableTicketTypes.value.find((t) => t.id === selection.ticket_type_id);
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

// Ticket purchase handlers
const getTicketTypeName = (ticketTypeId: number): string => {
    const ticketType = availableTicketTypes.value.find((t) => t.id === ticketTypeId);
    return ticketType?.name || '';
};

const getTicketTypePrice = (ticketTypeId: number): number => {
    const ticketType = availableTicketTypes.value.find((t) => t.id === ticketTypeId);
    return ticketType?.price || 0;
};

const handleTicketSelection = (selections: TicketSelection[]) => {
    ticketSelections.value = selections;
};

const clearDiscount = () => {
    appliedDiscount.value = null;
    purchaseForm.discount_id = null;
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
            purchaseForm.discount_id = data.discount.id;
            validationDiscount.error = '';
        } else {
            appliedDiscount.value = null;
            purchaseForm.discount_id = null;
            validationDiscount.error = data.message || 'Invalid discount code';
        }
    } catch (error: any) {
        appliedDiscount.value = null;
        purchaseForm.discount_id = null;

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

const proceedToBuyerForm = () => {
    router.visit(route('events.tickets.buyer-form', props.event.id), {
        method: 'post',
        data: {
            ticket_selections: ticketSelections.value,
            discount_id: appliedDiscount.value?.id || null,
        }
    });
};



const eventUrl = computed(() => {
    return window.location.href;
});

const shareText = computed(() => {
    return `Check out this event: ${props.event.title}`;
});

const twitterShareUrl = computed(() => {
    const text = encodeURIComponent(shareText.value);
    const url = encodeURIComponent(eventUrl.value);
    return `https://twitter.com/intent/tweet?text=${text}&url=${url}`;
});

const facebookShareUrl = computed(() => {
    const url = encodeURIComponent(eventUrl.value);
    return `https://www.facebook.com/sharer/sharer.php?u=${url}`;
});

const linkedinShareUrl = computed(() => {
    const url = encodeURIComponent(eventUrl.value);
    const title = encodeURIComponent(props.event.title);
    const summary = encodeURIComponent(props.event.description || shareText.value);
    return `https://www.linkedin.com/sharing/share-offsite/?url=${url}&title=${title}&summary=${summary}`;
});

const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(eventUrl.value);
        toast.success('Link Copied!', {
            position: 'top-center',
            description: 'Link has been copied to clipboard',
        });
    } catch (err) {
        console.error('Failed to copy link:', err);
        toast.error('Failed to copy link', {
            position: 'top-center',
            description: 'Please try again',
        });
    }
};

// const pageTitle = ref('My Dynamic Page');
// const title = useTitle(pageTitle);

// You can update pageTitle based on component logic or route changes
// watch(someReactiveVariable, (newValue) => {
//   pageTitle.value = `New Title: ${newValue}`;
// });
</script>

<style scoped>
/* Smooth scrolling for the entire page */
html {
    scroll-behavior: smooth;
}

/* Fade animation for map */
.fade-map-enter-active,
.fade-map-leave-active {
    transition: opacity 0.4s ease, transform 0.4s ease;
}

.fade-map-enter-from,
.fade-map-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

.fade-map-enter-to,
.fade-map-leave-from {
    opacity: 1;
    transform: translateY(0);
}

/* Smooth transitions for form sections */
.form-section {
    transition: all 0.3s ease;
}

/* Purchase form container with smooth appearance */
[data-section="tickets"] {
    scroll-margin-top: 2rem;
    transition: all 0.3s ease;
}

/* Highlight effect when scrolled to */
[data-section="tickets"]:target {
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
    border-radius: 1rem;
}

/* Smooth transitions for disabled states */
input:disabled,
button:disabled {
    transition: opacity 0.2s ease, cursor 0.2s ease;
}
</style>
