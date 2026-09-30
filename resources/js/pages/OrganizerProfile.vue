<template>
    <SeoHead :seo="seo" />
    <Toaster :close-button="true" />
    <NavBar text-color="text-foreground" background-color="bg-transparent"
        class="fixed z-20 w-full backdrop-blur" />
    <div class="w-full max-w-7xl mx-auto min-h-screen bg-background text-foreground px-0 sm:px-12 lg:px-16">
        <!-- <NavBar text-color="primary"/> -->

        <!-- Hero Section with Organizer Banner -->
        <!-- <section class="relative min-h-[400px] md:min-h-[500px]">

            <div class="absolute inset-0 container mx-auto pt-16">
                <img :src="organizer.banner_url || '/suptix-default.jpg'"
                     :alt="organizer.name + ' banner'"
                     class="w-full h-full object-cover opacity-30 rounded-full" />
                <div class="absolute inset-0 bg-gradient-to-b from-primary/50 to-foreground rounded-xl"></div>
            </div>

            <div class="relative container mx-auto px-4 py-16 md:py-24">
                <div class="flex flex-col lg:flex-row items-start lg:items-center gap-8">

                    <div class="flex-shrink-0 mx-auto lg:mx-0">
                        <div v-if="organizer.logo_url"
                            class="w-32 h-32 md:w-40 md:h-40 rounded-2xl bg-white shadow-xl overflow-hidden border-4 border-white/90">
                            <img :src="organizer.logo_url" :alt="organizer.name + ' logo'"
                                class="w-full h-full object-cover" />
                        </div>
                        <div v-else
                            class="w-32 h-32 md:w-40 md:h-40 rounded-2xl bg-primary text-primary-foreground flex items-center justify-center text-4xl md:text-5xl font-bold shadow-xl border-4 border-white/90">
                            {{ organizer.name.charAt(0).toUpperCase() }}
                        </div>
                    </div>


                    <div class="flex-1 space-y-6 text-center lg:text-left">
                        <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white drop-shadow-lg">
                            {{ organizer.name }}
                        </h1>

                        <div class="flex flex-col sm:flex-row flex-wrap gap-4 sm:gap-6 justify-center lg:justify-start text-white/90">
                            <div v-if="organizer.city" class="flex items-center gap-2 justify-center lg:justify-start">
                                <MapPin class="w-4 h-4 flex-shrink-0" />
                                <span class="text-sm md:text-base">{{ organizer.city }}, {{ organizer.country }}</span>
                            </div>
                            <div v-if="organizer.website" class="flex items-center gap-2 justify-center lg:justify-start">
                                <Globe class="w-4 h-4 flex-shrink-0" />
                                <a :href="organizer.website" target="_blank"
                                   class="text-primary-foreground hover:text-primary transition-colors underline text-sm md:text-base">
                                   {{ organizer.website }}
                                </a>
                            </div>
                            <div v-if="organizer.email" class="flex items-center gap-2 justify-center lg:justify-start">
                                <Mail class="w-4 h-4 flex-shrink-0" />
                                <a :href="'mailto:' + organizer.email"
                                   class="text-primary-foreground hover:text-primary transition-colors underline text-sm md:text-base">
                                   {{ organizer.email }}
                                </a>
                            </div>
                        </div>

                        <div v-if="organizer.description"
                             class="text-white/90 text-base md:text-lg leading-relaxed max-w-4xl mx-auto lg:mx-0"
                             v-html="organizer.description">
                        </div>


                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 pt-4">
                            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center hover:bg-white/20 transition-colors">
                                <div class="text-2xl md:text-3xl font-bold text-primary">{{ stats.total_events }}</div>
                                <div class="text-xs md:text-sm text-white/80">Total Events</div>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center hover:bg-white/20 transition-colors">
                                <div class="text-2xl md:text-3xl font-bold text-green-400">{{ stats.upcoming_events }}</div>
                                <div class="text-xs md:text-sm text-white/80">Upcoming</div>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center hover:bg-white/20 transition-colors">
                                <div class="text-2xl md:text-3xl font-bold text-orange-400">{{ stats.past_events }}</div>
                                <div class="text-xs md:text-sm text-white/80">Past Events</div>
                            </div>
                        </div>
                    </div>

                    <div class="flex-shrink-0 mx-auto lg:mx-0">
                        <Button @click="showSubscribeModal = true"
                                class="bg-white text-primary hover:bg-white/90 font-semibold px-6 py-3 rounded-full cursor-pointer shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                            <Bell class="w-4 h-4 mr-2" />
                            Subscribe for Updates
                        </Button>
                    </div>
                </div>
            </div>
        </section> -->

        <section class="max-w-7xl mx-auto">
            <div class="relative">
                <div class="banner-box h-[300px] overflow-hidden sm:rounded-xl sm:border-5">
                    <img :src="organizer.banner_url || getRandomBanner" :alt="organizer.name + ' banner'"
                        class="w-full h-full object-cover " />
                </div>
                <div v-if="organizer.logo_url"
                    class="absolute left-8 -bottom-8 sm:-bottom-12 w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 rounded-2xl bg-white overflow-hidden border-5 border-border transition-all duration-500">
                    <img :src="organizer.logo_url" :alt="organizer.name + ' logo'"
                        class="w-full h-full object-cover" />
                </div>
                <div v-else
                    class="absolute left-8 -bottom-8 sm:-bottom-12 w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 rounded-2xl bg-white overflow-hidden border-5 border-border transition-all duration-500">
                    {{ organizer.name.charAt(0).toUpperCase() }}
                </div>
            </div>
            <article class="mt-12 py-8 px-4 sm:px-8 ">
                <h2 class="font-bold text-2xl md:text-3xl lg:text-4xl xl:text-5xl">{{ organizer.name }}</h2>
                <!-- address and info -->
                <div
                    class="my-4 flex flex-col items-start sm:items-center sm:flex-row flex-wrap gap-4 sm:gap-6 justify-center lg:justify-start text-foreground">
                    <div v-if="organizer.city" class="flex items-center gap-2 justify-center lg:justify-start">
                        <MapPin class="w-4 h-4 flex-shrink-0" />
                        <span class="text-sm md:text-base"> {{ organizer.city }}, {{ toTitleCase(organizer.state?? '') }}, {{ organizer.country?.toUpperCase() }}</span>
                    </div>
                    <div v-if="organizer.website" class="flex items-center gap-2 justify-center lg:justify-start">
                        <Globe class="w-4 h-4 flex-shrink-0" />
                        <a :href="organizer.website" target="_blank"
                            class="text-foreground hover:text-primary transition-colors underline text-sm md:text-base">
                            {{ organizer.website }}
                        </a>
                    </div>
                    <div v-if="organizer.email" class="flex items-center gap-2 justify-center lg:justify-start">
                        <Mail class="w-4 h-4 flex-shrink-0" />
                        <a :href="'mailto:' + organizer.email"
                            class="text-foreground hover:text-primary transition-colors underline text-sm md:text-base">
                            {{ organizer.email }}
                        </a>
                    </div>
                    <div v-if="organizer.phone" class="flex items-center gap-2 justify-center lg:justify-start">
                        <PhoneCall class="w-4 h-4 flex-shrink-0" />
                        <a :href="'tel:' + organizer.phone"
                            class="text-foreground hover:text-primary transition-colors underline text-sm md:text-base">
                            {{ organizer.phone }}
                        </a>
                    </div>
                </div>
                <!-- description -->
                 <div class="text-forground/90">
                    <article v-html="organizer.description"></article>
                 </div>
            </article>
            <!-- <div class="py-24"></div> -->
        </section>

        <hr>

        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Events -->
                <div class="lg:col-span-2 space-y-6 transition-all duration-500">
                    <!-- View Toggle Controls -->
                    <div class="rounded flex items-center justify-between bg-card p-2 border-b">
                        <h2 class="px-2 text-xl font-bold">Events</h2>
                        <div
                            class="hidden md:flex items-center gap-1 rounded-xl inset-shadow-border p-1 inset-shadow-sm transition-all duration-500">
                            <Button @click="viewMode = 'grid'" :variant="viewMode === 'grid' ? 'default' : 'outline'"
                                size="sm" class="rounded-full" :class="viewMode == 'grid' ? 'shadow-lg' : ''">
                                <Grid3X3 class="w-4 h-4" />
                            </Button>
                            <Button @click="viewMode = 'timeline'"
                                :variant="viewMode === 'timeline' ? 'default' : 'outline'" size="sm"
                                class="rounded-full" :class="viewMode == 'timeline' ? 'shadow-lg' : ''">
                                <Clock class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>

                    <!-- Grid View -->
                    <div v-if="viewMode === 'grid'" class="grid md:grid-cols-2 gap-6 transition-all duration-500">
                        <div v-for="event in events" :key="event.id"
                            class="group bg-card border border-border rounded-xl overflow-hidden hover:border-primary/50 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-lg">
                            <!-- Event Image -->
                            <div class="relative h-48 overflow-hidden">
                                <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.title"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                                <div v-else
                                    class="w-full h-full bg-gradient-to-br from-primary/20 to-primary/40 flex items-center justify-center">
                                    <div class="text-4xl font-bold text-primary">{{ event.title.charAt(0) }}</div>
                                </div>
                                <!-- Status Badge -->
                                <div class="absolute top-4 right-4">
                                    <span v-if="event.is_upcoming"
                                        class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-medium">
                                        Upcoming
                                    </span>
                                    <span v-else
                                        class="bg-gray-500 text-white px-3 py-1 rounded-full text-xs font-medium">
                                        Past
                                    </span>
                                </div>
                            </div>

                            <!-- Event Details -->
                            <div class="p-6">
                                <h3
                                    class="text-lg font-bold text-foreground mb-2 group-hover:text-primary transition-colors">
                                    {{ event.title }}
                                </h3>

                                <div class="space-y-2 mb-4 text-sm text-muted-foreground">
                                    <div class="flex items-center gap-2">
                                        <Calendar class="w-4 h-4" />
                                        <span>{{ event.formatted_date }}</span>
                                        <span v-if="event.formatted_time">at {{ event.formatted_time }}</span>
                                    </div>
                                    <div v-if="event.venue_name" class="flex items-center gap-2">
                                        <MapPin class="w-4 h-4" />
                                        <span>{{ event.venue_name }}</span>
                                    </div>
                                    <div v-if="event.ticket_types.length" class="flex items-center gap-2">
                                        <Ticket class="w-4 h-4" />
                                        <span>{{ event.ticket_types.length }} ticket
                                            type{{ event.ticket_types.length > 1 ? 's' : '' }}</span>
                                    </div>
                                </div>

                                <a :href="route('events.show', event.slug || event.id)"
                                    class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm font-medium hover:bg-primary/90 transition-colors">
                                    View Event
                                    <ArrowRight class="w-4 h-4 ml-2" />
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline View -->
                    <div v-else-if="viewMode === 'timeline'" class="space-y-6 transition-all duration-500">
                        <div v-for="(event, index) in events" :key="event.id" class="relative">
                            <!-- Timeline Line -->
                            <div v-if="index < events.length - 1"
                                class="absolute left-1 -bottom-36 md:-bottom-20 w-0.5 h-64 md:h-30 bg-border z-0"></div>

                            <div class="flex flex-row items-center gap-6 relative z-10">
                                <!-- Timeline Dot -->
                                <div class="flex-shrink-0 mt-0">
                                    <div class="w-3 h-3 rounded-full border-2 border-primary"
                                        :class="event.is_upcoming ? 'bg-primary' : 'bg-muted'"></div>
                                </div>

                                <!-- Event Card -->
                                <div
                                    class="flex-1 bg-card border border-border rounded-xl p-6 hover:border-primary/50 transition-colors">
                                    <div class="flex flex-col md:flex-row gap-6">
                                        <!-- Event Image -->
                                        <div class="md:w-32 md:h-32 flex-shrink-0">
                                            <img v-if="event.cover_image_url" :src="event.cover_image_url"
                                                :alt="event.title"
                                                class="w-full h-32 md:h-full object-cover rounded-lg" />
                                            <div v-else
                                                class="w-full h-32 md:h-full bg-gradient-to-br from-primary/20 to-primary/40 rounded-lg flex items-center justify-center">
                                                <div class="text-2xl font-bold text-primary">{{ event.title.charAt(0) }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Event Details -->
                                        <div class="flex-1 space-y-3">
                                            <div class="flex items-start justify-between">
                                                <h3 class="text-xl font-bold text-foreground">{{ event.title }}</h3>
                                                <span v-if="event.is_upcoming"
                                                    class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-medium">
                                                    Upcoming
                                                </span>
                                                <span v-else
                                                    class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-medium">
                                                    Past
                                                </span>
                                            </div>

                                            <div class="flex flex-wrap gap-4 text-sm text-muted-foreground">
                                                <div class="flex items-center gap-2">
                                                    <Calendar class="w-4 h-4" />
                                                    <span>{{ event.formatted_date }}</span>
                                                    <span v-if="event.formatted_time">at
                                                        {{ event.formatted_time }}</span>
                                                </div>
                                                <div v-if="event.venue_name" class="flex items-center gap-2">
                                                    <MapPin class="w-4 h-4" />
                                                    <span>{{ event.venue_name }}</span>
                                                </div>
                                            </div>

                                            <!-- <article v-if="event.description"
                                                class="text-muted-foreground text-sm line-clamp-3"
                                                v-html="event.description">
                                            </article> -->

                                            <div v-if="event.ticket_types.length" class="flex flex-wrap gap-2">
                                                <span v-for="ticket in event.ticket_types" :key="ticket.name"
                                                    class="bg-muted text-muted-foreground px-2 py-1 rounded text-xs">
                                                    {{ ticket.name }} - GHS {{ ticket.price }}
                                                </span>
                                            </div>

                                            <a :href="route('events.show', event.slug || event.id)"
                                                class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground rounded-full text-sm font-medium hover:bg-primary/90 transition-colors">
                                                View Event Details
                                                <ArrowRight class="w-4 h-4 ml-2" />
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="events.length === 0" class="text-center py-12">
                        <Calendar class="w-16 h-16 text-muted-foreground mx-auto mb-4" />
                        <h3 class="text-xl font-semibold text-foreground mb-2">No Events Yet</h3>
                        <p class="text-muted-foreground">{{ organizer.name }} hasn't created any events yet. Subscribe
                            to get notified when they do!</p>
                    </div>
                </div>

                <!-- Right Column: Calendar & Info -->
                <div class="order-first md:order-last space-y-6">
                    <!-- Calendar Widget -->
                    <div class="bg-card border border-border rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-foreground mb-4 flex items-center gap-2">
                            <Calendar class="w-5 h-5" />
                            Upcoming Events
                        </h3>

                        <!-- Simple Calendar with Event Indicators -->
                        <div class="w-full">
                            <!-- Calendar Month Header -->
                            <div class="text-center mb-4">
                                <h4 class="text-md font-semibold text-foreground">
                                    {{ getCurrentMonthYear() }}
                                </h4>
                                <hr>
                            </div>

                            <!-- Calendar Grid -->
                            <div class="grid grid-cols-7 gap-1 text-center text-xs">
                                <!-- Day headers -->
                                <div v-for="day in ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']"
                                     :key="day"
                                     class="p-2 font-medium text-muted-foreground">
                                    {{ day }}
                                </div>

                                <!-- Calendar dates -->
                                <div v-for="date in getCalendarDates()"
                                     :key="date.key"
                                     class="relative p-2 h-10 flex items-center justify-center">
                                    <!-- Date number -->
                                    <span :class="[
                                        'text-xs',
                                        date.isCurrentMonth ? 'text-foreground' : 'text-muted-foreground/50',
                                        date.isToday ? 'bg-primary text-primary-foreground rounded-xs w-6 h-6 flex items-center justify-center' : ''
                                    ]">
                                        {{ date.day }}
                                    </span>

                                    <!-- Event indicator -->
                                    <div v-if="hasEventOnDate(date)"
                                         class="absolute bottom-1 left-1/2 transform -translate-x-1/2 w-1.5 h-1.5 bg-primary rounded-xs">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Event count summary -->
                        <div class="mt-4 text-center">
                            <p class="text-sm text-muted-foreground">
                                {{ upcomingEvents.length }} upcoming event{{ upcomingEvents.length !== 1 ? 's' : '' }}
                            </p>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="bg-card border border-border rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-foreground mb-4">Quick Stats</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-4 bg-primary/10 rounded-lg">
                                <div class="text-2xl font-bold text-primary">{{ stats.total_events }}</div>
                                <div class="text-sm text-muted-foreground">Total Events</div>
                            </div>
                            <div class="text-center p-4 bg-background border rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ stats.upcoming_events }}</div>
                                <div class="text-sm text-muted-foreground">Upcoming</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Subscribe Modal -->
        <Dialog :open="showSubscribeModal" @update:open="showSubscribeModal = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Subscribe to {{ organizer.name }}</DialogTitle>
                    <DialogDescription>
                        Get notified when {{ organizer.name }} creates new events or makes important announcements.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="subscribe" class="space-y-4">
                    <div>
                        <Label for="email">Email Address *</Label>
                        <Input id="email" v-model="subscribeForm.email" type="email" placeholder="your@email.com"
                            required />
                    </div>
                    <div>
                        <Label for="name">Name (Optional)</Label>
                        <Input id="name" v-model="subscribeForm.name" type="text" placeholder="Your Name" />
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <Button type="button" variant="outline" @click="showSubscribeModal = false">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="subscribing">
                            <span v-if="subscribing">Subscribing...</span>
                            <span v-else>Subscribe</span>
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Footer -->
    </div>
    <Footer />
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { toast, Toaster } from 'vue-sonner';
import axios from 'axios';
import NavBar from '@/components/NavBar.vue';
import SeoHead from '@/components/SeoHead.vue';
import Footer from '@/components/Footer.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import {
    MapPin, Globe, Mail, Phone, Bell, Grid3X3, Clock, Calendar,
    ArrowRight, Ticket,
    PhoneCall
} from 'lucide-vue-next';
import { rand } from '@vueuse/core';

interface Event {
    id: number;
    title: string;
    slug: string;
    description?: string;
    start_date: string;
    start_time?: string;
    end_date?: string;
    end_time?: string;
    status: string;
    cover_image_url?: string;
    venue?: {
        name: string;
        city: string;
        full_address?: string;
    };
    ticket_types: Array<{
        name: string;
        price: string;
        available_quantity: number;
    }>;
    is_upcoming: boolean;
    formatted_date: string;
    formatted_time?: string;
}

interface Organizer {
    id: number;
    name: string;
    email?: string;
    phone?: string;
    website?: string;
    description?: string;
    city?: string;
    state?: string;
    country?: string;
    logo_url?: string;
    banner_url?: string;
    slug: string;
}

interface Stats {
    total_events: number;
    upcoming_events: number;
    past_events: number;
    total_venues: number | string;
}

const props = defineProps<{
    organizer: Organizer;
    events: Event[];
    upcomingEvents: Event[];
    calendarEvents: any[];
    stats: Stats;
    seo: any;
}>();

const viewMode = ref<'grid' | 'timeline'>('grid');
const showSubscribeModal = ref(false);
const subscribing = ref(false);
const subscribeForm = ref({
    email: '',
    name: '',
});

// Compute dates with events for calendar indicators
const eventDates = computed(() => {
    return props.upcomingEvents.map(event => new Date(event.start_date));
});

// Check if a date has events
const hasEventOnDate = (date: any) => {
    return eventDates.value.some(eventDate =>
        eventDate.getDate() === date.day &&
        eventDate.getMonth() === date.month - 1 &&
        eventDate.getFullYear() === date.year
    );
};

// Get current month and year as formatted string
const getCurrentMonthYear = () => {
    const now = new Date();
    return now.toLocaleDateString('en-US', {
        month: 'long',
        year: 'numeric'
    });
};

// Generate calendar grid dates for current month
const getCalendarDates = () => {
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth();

    // Get first day of month and how many days in month
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();

    const dates = [];

    // Add previous month's trailing days
    const prevMonth = new Date(year, month - 1, 0);
    const daysInPrevMonth = prevMonth.getDate();

    for (let i = startingDayOfWeek - 1; i >= 0; i--) {
        dates.push({
            day: daysInPrevMonth - i,
            month: month,
            year: year,
            isCurrentMonth: false,
            isToday: false,
            key: `prev-${daysInPrevMonth - i}`
        });
    }

    // Add current month days
    const today = new Date();
    for (let day = 1; day <= daysInMonth; day++) {
        const isToday = year === today.getFullYear() &&
                       month === today.getMonth() &&
                       day === today.getDate();

        dates.push({
            day: day,
            month: month + 1, // month is 1-indexed for display
            year: year,
            isCurrentMonth: true,
            isToday: isToday,
            key: `current-${day}`
        });
    }

    // Add next month's leading days to complete the grid (42 total cells = 6 rows × 7 days)
    const totalCells = 42;
    const remainingCells = totalCells - dates.length;

    for (let day = 1; day <= remainingCells; day++) {
        dates.push({
            day: day,
            month: month + 2,
            year: year,
            isCurrentMonth: false,
            isToday: false,
            key: `next-${day}`
        });
    }

    return dates;
};

function toTitleCase(str: string) {
    str = str.replace('_', ' ');
    return str.replace(
        /\w\S*/g,
        text => text.charAt(0).toUpperCase() + text.substring(1).toLowerCase()
    );
}


const getRandomBanner = computed( ()=> {
    return '/banners/banner' + rand(1,10) + '.jpg';
});
const subscribe = async () => {
    subscribing.value = true;

    try {
        await axios.post(route('organizers.subscribe', props.organizer.slug), subscribeForm.value);

        toast.success('Successfully subscribed!', {
            description: `You'll receive updates about ${props.organizer.name}'s events.`,
            duration: 5000,
        });

        showSubscribeModal.value = false;
        subscribeForm.value = { email: '', name: '' };

    } catch (error: any) {
        if (error.response?.status === 422) {
            const message = error.response.data.message || 'You are already subscribed to this organizer.';
            toast.error('Subscription Error', {
                description: message,
                duration: 5000,
            });
        } else {
            toast.error('Failed to subscribe', {
                description: 'Please try again later.',
                duration: 5000,
            });
        }
    } finally {
        subscribing.value = false;
    }
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
