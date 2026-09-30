<template>
    <SeoHead :seo="seo" />
    <NavBar text-color="text-foreground" background-color="bg-transparent " class="fixed z-10 w-full border-b-pink-600/10 shadow-xs backdrop-blur" />
    <div :key="$page.url" class="min-h-screen bg-background">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mt-16 mb-8">
                <h1 class="mb-2 text-3xl font-bold text-foreground">Event Calendar</h1>
                <p class="text-foreground/70">View all upcoming events in calendar format</p>
            </div>

            <!-- Calendar Container -->
            <div class="rounded-lg borders bg-background shadow-sm text-foreground">
                <VCalendar
                    :attributes="calendarAttributes"
                    @dayclick="onDayClick"
                    @page-change="onPageChange"
                    class="w-full bg-background"
                    is-expanded
                    expanded
                    :rows="1"
                    view="monthly"
                    :masks="masks"
                    :color="calendarColor"
                    title-position="left"
                    nav-visibility="hover"
                >
                    <!-- Custom content for calendar days -->
                    <template v-slot:day-content="{ day, attributes }">
                        <div class="relative h-full min-h-[100px] w-full p-1">
                            <!-- Day number -->
                            <span class="mb-1 block text-sm font-medium text-foreground">
                                {{ day.day }}
                            </span>

                            <!-- Events for this day -->
                            <div class="space-y-1">
                                <div
                                    v-for="attr in attributes"
                                    :key="attr.key"
                                    :class="['cursor-pointer truncate rounded px-2 py-1 text-xs text-white hover:opacity-80 transition-opacity', attr.customData?.color || 'bg-blue-500']"
                                    :title="`${attr.customData?.title} - ${attr.customData?.venue_name || 'TBD'}`"
                                    @click.stop="showEventModal(attr.customData)"
                                >
                                    {{ attr.customData?.title }}
                                </div>
                            </div>
                        </div>
                    </template>
                </VCalendar>
            </div>

            <!-- Event Details Modal/Popup -->
            <Transition name="modal">
                <div v-if="selectedEvent" class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black p-4" @click="closeModal">
                    <div class="max-h-[80vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-background p-6 shadow-xl border border-border" @click.stop>
                    <div class="mb-4 flex items-start justify-between">
                        <h3 class="text-xl font-bold text-foreground">{{ selectedEvent.title }}</h3>
                        <button @click="closeModal" class="text-gray-400 transition-colors hover:foreground/70">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div v-if="selectedEvent.cover_image_url" class="mb-4">
                        <img :src="selectedEvent.cover_image_url" :alt="selectedEvent.title" class="h-48 w-full rounded-lg object-cover" />
                    </div>

                    <div class="space-y-3">
                        <div v-if="selectedEvent.description">
                            <h4 class="font-semibold text-foreground">Description</h4>
                            <article class="text-sm foreground/70" v-html="selectedEvent.description"></article>
                        </div>

                        <div v-if="selectedEvent.venue_name">
                            <h4 class="font-semibold text-foreground">Venue</h4>
                            <p class="text-sm foreground/70">{{ selectedEvent.venue_name }}</p>
                        </div>

                        <div v-if="selectedEvent.organizer">
                            <h4 class="font-semibold text-foreground">Organizer</h4>
                            <p class="text-sm foreground/70">{{ selectedEvent.organizer }}</p>
                        </div>

                        <div v-if="selectedEvent.event_type">
                            <h4 class="font-semibold text-foreground">Event Type</h4>
                            <p class="text-sm foreground/70">{{ selectedEvent.event_type }}</p>
                        </div>

                        <div>
                            <h4 class="font-semibold text-foreground">Date & Time</h4>
                            <p class="text-sm foreground/70">{{ formatEventDateTime(selectedEvent) }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <Button variant="outline" @click="closeModal"> Close </Button>
                        <Button
                            @click="goToEvent(selectedEvent)"
                            class="bg-primary text-white hover:from-blue-700 hover:to-purple-700"
                        >
                            View Details
                        </Button>
                    </div>
                    </div>
                </div>
            </Transition>
        </div>
    </div>
</template>

<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import SeoHead from '@/components/SeoHead.vue';
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';
import { Calendar as VCalendar } from 'v-calendar';
import 'v-calendar/style.css';
import { computed, ref } from 'vue';

interface CalendarEvent {
    id: number;
    title: string;
    start: string;
    end?: string;
    description?: string;
    venue_name?: string;
    organizer?: string;
    event_type?: string;
    cover_image_url?: string;
    url: string;
}

const props = defineProps<{
    events: CalendarEvent[];
    seo?: any;
    currentYear?: number;
    currentMonth?: number;
}>();

const selectedEvent = ref<CalendarEvent | null>(null);

const masks = {
    weekdays: 'WWW',
    navMonths: 'MMMM',
    input: ['L', 'YYYY-MM-DD', 'YYYY/MM/DD'],
    dayPopover: 'WWW, MMM D, YYYY',
};

const calendarColor = 'purple';

const getEventColor = (eventType?: string) => {
    const colors = {
        Educational: 'bg-blue-500',
        Corporate: 'bg-gray-600',
        Social: 'bg-green-500',
        Party: 'bg-pink-500',
        Cultural: 'bg-purple-500',
        Sports: 'bg-orange-500',
        Government: 'bg-indigo-500',
        'Health & Wellness': 'bg-emerald-500',
        'Charity & Non-Profit': 'bg-rose-500',
        'Virtual/Hybrid': 'bg-cyan-500',
        Commercial: 'bg-amber-500',
    };
    return colors[eventType as keyof typeof colors] || 'bg-gray-500';
};

const calendarAttributes = computed(() => {
    return props.events.map((event) => ({
        key: `event-${event.id}`,
        dates: new Date(event.start),
        customData: {
            ...event,
            color: getEventColor(event.event_type),
        },
    }));
});

const formatEventDateTime = (event: CalendarEvent) => {
    const start = new Date(event.start);
    let formatted = start.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });

    if (event.start.includes(' ')) {
        formatted +=
            ' at ' +
            start.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true,
            });
    }

    if (event.end && event.end !== event.start) {
        const end = new Date(event.end);
        if (event.end.includes(' ')) {
            formatted +=
                ' - ' +
                end.toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true,
                });
        }
    }

    return formatted;
};

const onDayClick = (day: any) => {
    const dayEvents = props.events.filter((event) => {
        const eventDate = new Date(event.start).toDateString();
        return day.date.toDateString() === eventDate;
    });

    if (dayEvents.length === 1) {
        showEventModal(dayEvents[0]);
    } else if (dayEvents.length > 1) {
        // For multiple events, show modal on desktop, go to first event on mobile
        const isMobile = window.innerWidth < 768;
        if (isMobile) {
            goToEvent(dayEvents[0]);
        } else {
            selectedEvent.value = dayEvents[0];
        }
    }
};

const onPageChange = (page: any) => {
    console.log('Calendar page changed:', page);

    // Navigate to the new month with year and month parameters
    router.visit(route('calendar'), {
        method: 'get',
        data: {
            year: page.year,
            month: page.month + 1, // v-calendar months are 0-indexed, Laravel expects 1-indexed
        },
        preserveState: true,
        preserveScroll: true,
        only: ['events'],
    });
};

const showEventModal = (event: CalendarEvent) => {
    // Check if user is on mobile device
    const isMobile = window.innerWidth < 768;

    if (isMobile) {
        // Go directly to event page on mobile
        goToEvent(event);
    } else {
        // Show modal on desktop
        selectedEvent.value = event;
    }
};

const goToEvent = (event: CalendarEvent) => {
    router.visit(event.url);
};

const closeModal = () => {
    selectedEvent.value = null;
};
</script>

<style scoped>
:deep(.vc-container) {
    --vc-border-color: rgb(229 231 235);
    --vc-header-border-color: rgb(229 231 235);
    border: none;
    width: 100%;
    max-width: 100%;
    min-width: 100%;
    background-color: var(--background);
}

:deep(.vc-weeks) {
    padding: 0;
    margin-top: 1.5rem;
    width: 100%;
    max-width: 100%;
}

:deep(.vc-weekday) {
    /* background-color: rgb(249 250 251); */
    background-color: var(--background);
    /* border-bottom: 1px solid rgb(229 231 235); */
    border-bottom: 1px solid var(--border);
    /* color: rgb(75 85 99); */
    color: var(--foreground);
    font-weight: 600;
    font-size: 0.875rem;
    padding: 0.75rem 0.5rem;
}

:deep(.vc-day) {
    border-right: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    min-height: 120px;
}

:deep(.vc-day:hover) {
    /* background-color: rgb(249 250 251); */
    background-color: var(--card);
}

:deep(.vc-day.is-today) {
    /* background-color: rgb(239 246 255); */
    background-color: var(--primary);
}

:deep(.vc-header) {
    padding: 1rem 1rem 2rem 1rem;
    border-bottom: 1px solid var(--border);
    margin-bottom: 0.5rem;
}

:deep(.vc-title) {
    font-size: 1.25rem;
    font-weight: 700;
    /* color: rgb(17 24 39); */
    color: var(--foreground);
}

:deep(.vc-arrow) {
    /* color: rgb(107 114 128); */
    color: var(--foreground);
}

:deep(.vc-arrow:hover) {
    color: rgb(17 24 39);
}

/* Additional responsive styles */
:deep(.vc-weekdays) {
    width: 100%;
    display: flex;
}

:deep(.vc-week) {
    width: 100%;
    display: flex;
}

:deep(.vc-weekday) {
    flex: 1;
    min-width: 0;
}

:deep(.vc-day) {
    flex: 1;
    min-width: 0;
}

/* .vc-popover-content-content{
    max-width: 400px !important;
    min-width: 320px !important;
    width: 400px !important;
} */

/* Modal transitions */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active .modal-content,
.modal-leave-active .modal-content {
    transition: transform 0.3s ease;
}

.modal-enter-from .modal-content,
.modal-leave-to .modal-content {
    transform: scale(0.95) translateY(-20px);
}

/* Ensure calendar is fully responsive */
@media (max-width: 768px) {
    :deep(.vc-day) {
        min-height: 80px;
    }

    :deep(.vc-weekday) {
        font-size: 0.75rem;
        padding: 0.5rem 0.25rem;
    }
}

@media (min-width: 769px) {
    :deep(.vc-day) {
        min-height: 120px;
    }
}
</style>
