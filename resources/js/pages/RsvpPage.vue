<template>
    <Head :title="props.event?.title ? `RSVP - ${props.event.title}` : 'RSVP'" />
    <main class="w-full bg-white">
        <div class="mx-auto h-screen max-w-7xl bg-white px-8 py-16 sm:px-12 md:h-full lg:px-16">
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
            <!-- event header -->
            <div class="mb-8 overflow-hidden bg-white shadow-md">
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
                                <span class="rounded-md px-3 py-1 text-sm font-medium" :class="statusClasses">
                                    {{ event.status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Event Info -->
                    <div class="flex flex-col justify-between p-6 lg:w-3/5">
                        <h1 class="mb-4 text-2xl sm:text-3xl font-bold text-gray-900 leading-tight">{{ event.title }}
                        </h1>

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
                                    <div class="text-sm text-gray-600">
                                        <template v-if="event.end_date && event.end_date !== event.start_date">
                                            Starts {{ formatTime(event.start_time!) }}
                                        </template>
                                        <template v-else>
                                            {{ formatTime(event.start_time!) }}
                                            <span v-if="event.end_time"> - {{ formatTime(event.end_time) }}</span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div v-if="event.end_date" class="flex items-center gap-3">
                                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <div>
                                    <div class="font-medium">{{ formatDate(event.end_date) }}</div>
                                    <div class="text-sm text-gray-600">
                                        <span v-if="event.end_time"> {{ formatTime(event.end_time) }}</span>
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
                                    <div v-if="event.venue_address" class="text-sm text-gray-600">
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
                                    <div class="text-sm text-gray-600">Event Organizer</div>
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
                                    <div class="text-sm text-gray-600">Event Category</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- rsvps form -->
            <div class="mb-20 overflow-hidden bg-white shadow-md">
                <!-- Success message displayed after form submission -->
                <div v-if="success" class="p-6">
                    <div class="border border-green-500 p-6 text-center bg-green-500/5 rounded-lg">
                        <svg class="mx-auto h-16 w-16 text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-xl font-bold text-green-700 mb-2">RSVP Confirmed!</h3>
                        <p class="text-green-700 font-medium">{{ success }}</p>
                    </div>
                </div>

                <form v-else @submit.prevent="sendRsvp" class="p-4" id="rsvp-form">
                    <div v-if="errors.venue_full" class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Booked Out</h3>
                                <p class="mt-1 text-sm text-red-700">{{ errors.venue_full }}</p>
                            </div>
                        </div>
                    </div>
                    <h2 class="mb-4 text-lg sm:text-xl font-semibold border-b">Guest Information</h2>
                    <Toaster />
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <Label for="guest_name">Guest Name *</Label>
                            <Input id="guest_name" v-model="form.guest_name" type="text" required
                                placeholder="Enter your fullname" :class="{ 'border-red-500': errors.guest_name }" />
                            <p v-if="errors.guest_name" class="mt-1 text-sm text-red-600">
                                {{ errors.guest_name }}
                            </p>
                        </div>
                        <div>
                            <Label for="email">Email Address *</Label>
                            <Input id="email" v-model="form.email" type="email" required placeholder="Enter your email"
                                :class="{ 'border-red-500': errors.email }" />
                            <p v-if="errors.email" class="mt-1 text-sm text-red-600">
                                {{ errors.email }}
                            </p>
                        </div>

                        <div>
                            <Label for="phone">Phone Number (International format)</Label>
                            <Input id="phone" v-model="form.phone" type="tel" placeholder="eg. +233240123456"
                                :class="{ 'border-red-500': errors.phone }" />
                            <p v-if="errors.phone" class="mt-1 text-sm text-red-600">
                                {{ errors.phone }}
                            </p>
                        </div>
                        <div>
                            <Label for="referral_source" class="">How did you hear about
                                this event?</Label>
                            <Select v-model="form.referal_source">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select a source..." />
                                </SelectTrigger>

                                <SelectContent class="bg-white shadow-md">
                                    <SelectGroup>
                                        <SelectLabel>Referral Sources</SelectLabel>
                                        <SelectItem v-for="option in referralOptions" :key="option" :value="option">
                                            {{ option }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <p v-if="errors.referral_source" class="mt-1 text-sm text-red-600">
                                {{ errors.referral_source }}
                            </p>
                        </div>
                        <div class="">
                            <Label for="referral_source">Anything else you'd like us to know?</Label>
                            <Textarea placeholder="Anything we should know? Drop a note." maxlength="250"
                                v-model="form.notes" />
                        </div>

                        <!-- Honeypot field - hidden from users -->
                        <div style="position: absolute; left: -9999px; opacity: 0; pointer-events: none;">
                            <label for="website">Website (leave blank)</label>
                            <input
                                v-model="form.honeypot"
                                type="text"
                                id="website"
                                name="website"
                                tabindex="-1"
                                autocomplete="off"
                            />
                        </div>

                    </div>
                    <div v-if="canSubmit" class="text-center my-6">
                        <Button type="submit" size="lg" :disabled="processing"
                            class="w-full cursor-pointer border bg-primary px-6 py-3 text-white transition-colors hover:from-blue-700 hover:to-purple-700 font-semibold sm:w-auto sm:px-12">
                            {{ processing ? 'Processing...' : 'Secure Your Spot' }}
                        </Button>
                    </div>
                </form>
            </div>

            <div class=" bg-gray-50 p-6">
                <h2 class="mb-4 text-xl font-semibold">Important Information</h2>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500">•</span>
                        <span>Please arrive 15-30 minutes before the event starts</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500">•</span>
                        <span>Tickets are non-refundable unless the event is cancelled</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500">•</span>
                        <span>Valid ID may be required for entry</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500">•</span>
                        <span>Photography and recording policies may apply</span>
                    </li>
                </ul>
            </div>
        </div>
    </main>

</template>
<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, reactive, ref } from 'vue';
import { toast, Toaster } from 'vue-sonner';
import 'vue-sonner/style.css';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectGroup, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from "@/components/ui/textarea"




const statusClasses = computed(() => {
    switch (props.event.status) {
        case 'published':
            return 'bg-green-100 text-green-800';
        case 'draft':
            return 'bg-gray-100 text-gray-800';
        case 'live':
            return 'bg-red-100 text-green-800 animate-pulse';
        case 'canceled':
            return 'bg-orange-100 text-black-800 line-through';
        default:
            return 'bg-gray-100 text-gray-800';
    }
});

const processing = ref(false);
const errors = computed(() => props.errors || {});
const success = ref('')
const canSubmit = computed(() => {
    return (form.email && form.guest_name);
});

// const referralSource = ref('')
const referralOptions = [
    'LinkedIn',
    'Facebook',
    'Instagram',
    'Twitter/X',
    'Friend or Family',
    'Flyer or Poster',
    'Email Newsletter',
    'Community',
    'Website',
    'Other'
]

interface Organizer {
    id: number;
    name: string;
}

interface Event {
    id: number;
    slug: string;
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
}


const props = defineProps<{
    event: Event;
    errors?: Record<string, string>;
    success?: string;
}>();

const form = reactive({
    guest_name: '',
    email: '',
    phone: '',
    referal_source: '',
    notes: '',
    honeypot: ''
});

const canBuyTickets = computed(() => {
    return ['published', 'live'].includes(props.event.status);
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
    });
};

const goBack = () => {
    router.visit(route('events.index'));
};

const sendRsvp = () => {
    processing.value = true;
    success.value = "";
    router.post(
        route('events.rsvp.book', props.event.slug || props.event.id),
        {
            guest_name: form.guest_name,
            email: form.email,
            phone: form.phone,
            referral_source: form.referal_source,
            notes: form.notes,
            honeypot: form.honeypot,
        }, {
        onFinish: () => {
            processing.value = false;
        }, onSuccess: () => {
            success.value = "Your RSVP has been sent successfully!";
            toast.success('RSVP Sent!', {
                position: 'bottom-center',
                description: 'Your RSVP has been sent successfully!',
                closeButton: true
            });

            // Reset form data
            Object.assign(form, {
                guest_name: '',
                email: '',
                phone: '',
                referal_source: '',
                notes: '',
                honeypot: ''
            });

            const myForm = document.getElementById('rsvp-form');
            if (myForm && typeof (myForm as HTMLFormElement).reset === 'function') {
                (myForm as HTMLFormElement).reset();
            }
        },

    }
    );
}
</script>

<style></style>
