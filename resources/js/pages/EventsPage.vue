<template>
    <SeoHead :seo="seo" />
    <section class="relative h-auto overflow-hidden mb-12">
        <NavBar text-color="text-black" background-color="bg-transparent"
            class="fixed z-40 w-full border-b-pink-600/10 shadow-md shadow-black/10 backdrop-blur" />
    </section>
    <!-- <NavBar class="fixed w-full border-b-pink-600/10 bg-black shadow-md shadow-black/10 backdrop-blur" /> -->
    <div :key="$page.url" class="mx-auto w-full bg-background px-8 py-16 sm:px-12 lg:px-16">
        <!-- Header -->
        <div class="mt-10 mb-8">
            <h1 class="mb-2 text-3xl font-bold text-foreground">Upcoming Events</h1>
            <p class="text-foreground/80">Discover and book tickets for amazing events</p>
        </div>

        <!-- Quick Category Filter Header -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-foreground/80 mb-4 text-center sm:text-left">Browse by Category</h2>
            <div class="flex flex-wrap gap-3 justify-center sm:justify-start">
                <button
                    v-for="(label, key) in eventTypes"
                    :key="key"
                    @click="quickFilterByCategory(key)"
                    :class="[
                        'px-6 py-3 rounded-full text-sm font-medium transition-all duration-300 cursor-pointer border-2 shadow-sm hover:shadow-lg transform hover:-translate-y-1',
                        activeCategory === key
                            ? 'bg-gradient-to-r from-primary to-primary/80 text-white border-primary shadow-lg scale-105 shadow-primary/25'
                            : 'bg-background text-foreground border-border hover:border-primary/30 hover:text-primary hover:bg-primary/5'
                    ]"
                >
                    {{ label }}
                    <span v-if="activeCategory === key"
                          class="ml-2 inline-flex items-center justify-center w-2 h-2 bg-white rounded-full opacity-80">
                    </span>
                </button>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="mb-8">
            <div class="my-1 flex flex-row justify-end">
                <button @click="toggleFilter"
                        :class="[
                            'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 cursor-pointer',
                            isFilterShow
                                ? 'bg-primary text-white shadow-md'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                        ]">
                    {{ isFilterShow ? 'Hide filters' : 'Show advanced filters' }}
                </button>
            </div>
            <div class="transition-all duration-300 ease-in-out rounded-xl border border-gray-200 bg-card p-6 shadow-sm"
                v-if="isFilterShow">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <!-- Search (full width on mobile, 2 cols on sm, 2 cols on lg) -->
                    <div class="sm:col-span-2 lg:col-span-2">
                        <Label for="search">Search Events</Label>
                        <Input id="search" v-model="searchForm.search" type="text"
                            placeholder="Search by title, description, or tags..." class="rounded  mt-1 w-full" />
                    </div>

                    <!-- Category Filter -->
                    <div class="sm:col-span-1 lg:col-span-1">
                        <Label for="category">Category</Label>
                        <Select v-model="searchForm.category">
                            <SelectTrigger class="rounded mt-1 w-full">
                                <SelectValue placeholder="All Categories" />
                            </SelectTrigger>
                            <SelectContent class="bg-background rounded">
                                <SelectItem v-for="(label, key) in eventTypes" :key="key" :value="key">
                                    {{ label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Tags Filter -->
                    <div class="sm:col-span-1 lg:col-span-1">
                        <Label for="tags">Tags</Label>
                        <Select v-model="searchForm.tags">
                            <SelectTrigger class="rounded mt-1 w-full">
                                <SelectValue placeholder="All Tags" />
                            </SelectTrigger>
                            <SelectContent class="bg-background rounded">
                                <SelectItem v-for="(label, key) in tags" :key="key" :value="key">
                                    {{ label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Status Filter -->
                    <div class="sm:col-span-1 lg:col-span-1">
                        <Label for="status">Status</Label>
                        <Select v-model="searchForm.status">
                            <SelectTrigger class="rounded mt-1 w-full">
                                <SelectValue placeholder="All Events" />
                            </SelectTrigger>
                            <SelectContent class="bg-background rounded">
                                <SelectItem v-for="(label, key) in availableStatuses" :key="key" :value="key">
                                    {{ label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Venue Filter (full width on mobile, half on sm, single on lg) -->
                    <div class="sm:col-span-1 lg:col-span-1">
                        <Label for="venue">Venue</Label>
                        <Input id="venue" v-model="searchForm.venue" type="text" placeholder="Venue name"
                            class="rounded  mt-1 w-full" />
                    </div>

                    <!-- Date Filter (full width on mobile, full on sm, single on lg) -->
                    <div class="sm:col-span-2 lg:col-span-1">
                        <Label for="start_date">From Date</Label>
                        <Input id="start_date" v-model="searchForm.start_date" type="date"
                            class=" rounded mt-1 w-full" />
                    </div>
                </div>

                <!-- Filter Actions - Stacked on mobile, row on sm+ -->
                <div class="mt-4 flex flex-col items-center justify-between gap-3 sm:flex-row sm:gap-0">
                    <div class="order-1 text-sm text-foreground sm:order-none">
                        Showing {{ events?.data?.length || 0 }} of {{ events?.total || 0 }} events
                    </div>
                    <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                        <Button variant="outline" class="rounded w-full cursor-pointer sm:w-auto text-foreground"
                            @click="clearFilters" :disabled="!hasActiveFilters">
                            Clear Filters
                        </Button>
                        <button @click="applyFilters"
                            class="rounded w-full cursor-pointer bg-primary px-4 py-2 text-sm text-white transition-colors sm:w-auto">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="view-toggle flex justify-between items-center mb-8">
            <div class="flex items-center space-x-4">
                <h3 class="text-lg font-medium text-foreground">
                    {{ events?.total || 0 }} Events Found
                </h3>
                <div v-if="hasActiveFilters" class="flex items-center space-x-2">
                    <span class="text-sm text-foreground/70">Active filters:</span>
                    <button @click="clearFilters"
                            class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded-full hover:bg-red-200 transition-colors">
                        Clear all
                    </button>
                </div>
            </div>
            <div class="flex space-x-1 p-1 bg-gray-100 rounded-lg inset-shadow-border p-1 inset-shadow-sm">
                <!-- Grid button -->
                <button @click="isGridView = true"  :class="[
                    'p-2 rounded-md transition-all duration-200 transform cursor-pointer flex items-center justify-center',
                    isGridView
                         ? 'bg-white text-primary shadow-md scale-105'
                        : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'
                ]" title="Grid View">
                    <SquareChartGantt class="h-4 w-4" />
                </button>

                <!-- List button -->
                <button @click="isGridView = false" :class="[
                    'p-2 rounded-md transition-all duration-200 transform cursor-pointer flex items-center justify-center',
                    !isGridView
                        ? 'bg-white text-primary shadow-md scale-105'
                        : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'
                ]" title="List View">
                    <List class="h-4 w-4" />
                </button>
            </div>
        </div>

        <!-- Events Grid -->
        <div v-if="events?.data?.length > 0" class="mb-8 grid gap-6 transition-all duration-500 ease-in-out"
            :class="!isGridView ? 'grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-8' : 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4'">
            <EventItem :horizontal="!isGridView" v-for="event in events.data" :key="event.id" :event="event"
                class="z-0 transition duration-500 ease-in-out transform" />
        </div>


        <!-- No Events Message -->
        <div v-else class="py-12 text-center">
            <svg class="mx-auto mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="mb-2 text-lg font-medium text-gray-900">No events found</h3>
            <p class="text-gray-600">Try adjusting your search criteria or check back later for new events.</p>
        </div>

        <!-- Pagination -->
        <div v-if="events?.data?.length > 0 && (events.prev_page_url || events.next_page_url)"
            class="flex justify-center">
            <div class="flex items-center gap-2">
                <Button variant="outline" @click="goToPage(events.prev_page_url!)" :disabled="!events.prev_page_url"
                    class="rounded "> Previous </Button>

                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600"> Page {{ events?.current_page || 1 }} of
                        {{ events?.last_page || 1 }} </span>
                </div>

                <Button variant="outline" @click="goToPage(events.next_page_url!)" :disabled="!events.next_page_url"
                    class="rounded "> Next </Button>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <Footer />
</template>

<script setup lang="ts">
import EventItem from '@/components/EventItem.vue';
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';
import SeoHead from '@/components/SeoHead.vue';
import { Switch } from '@/components/ui/switch';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { router, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import { Grid3X3, List, ListTreeIcon, Square, SquareChartGantt } from 'lucide-vue-next';

interface Event {
    id: number;
    title: string;
    description?: string;
    cover_image_url?: string;
    start_date: string;
    start_time?: string;
    end_date?: string;
    end_time?: string;
    event_type?: string;
    status: string;
    tags?: string[];
    venue?: {
        id: number;
        name: string;
    };
    organizer?: {
        id: number;
        name: string;
    };
}

interface PaginatedEvents {
    data: Event[];
    current_page: number;
    last_page: number;
    prev_page_url?: string;
    next_page_url?: string;
    total: number;
}

interface Filters {
    search?: string;
    category?: string;
    event_type?: string;
    venue?: string;
    start_date?: string;
    end_date?: string;
    status?: string;
    tags?: string;
}

const props = withDefaults(
    defineProps<{
        events?: PaginatedEvents;
        eventTypes?: Record<string, string>;
        tags?: Record<string, string>;
        availableStatuses?: Record<string, string>;
        filters?: Filters;
        seo?: any;
    }>(),
    {
        events: () => ({ data: [], current_page: 1, last_page: 1, total: 0 }),
        eventTypes: () => ({}),
        tags: () => ({}),
        availableStatuses: () => ({}),
        filters: () => ({}),
        seo: () => ({}),
    },
);

const page = usePage();

const searchForm = reactive({
    search: props.filters?.search || '',
    category: props.filters?.category || 'all',
    event_type: props.filters?.event_type || '',
    venue: props.filters?.venue || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    status: props.filters?.status || 'all',
    tags: props.filters?.tags || 'all',
});

// Watch for changes in props.filters and sync the form
watch(() => props.filters, (newFilters) => {
    if (newFilters) {
        searchForm.search = newFilters.search || '';
        searchForm.category = newFilters.category || 'all';
        searchForm.event_type = newFilters.event_type || '';
        searchForm.venue = newFilters.venue || '';
        searchForm.start_date = newFilters.start_date || '';
        searchForm.end_date = newFilters.end_date || '';
        searchForm.status = newFilters.status || 'all';
        searchForm.tags = newFilters.tags || 'all';
    }
}, { deep: true, immediate: true });

const hasActiveFilters = computed(() => {
    return Object.entries(searchForm).some(([key, value]) => {
        if (key === 'category' || key === 'status' || key === 'tags') {
            return value !== 'all';
        }
        return value !== '';
    });
});

// Computed property to get the current active category
const activeCategory = computed(() => {
    return searchForm.category || 'all';
});

const applyFilters = () => {
    const filters: Record<string, string> = {};

    // Handle search parameter (not prefixed with 'filter.')
    if (searchForm.search) {
        filters.search = searchForm.search;
        filters['filter[search_global]'] = searchForm.search;
    }

    // Handle filter parameters (prefixed with 'filter.')
    Object.entries(searchForm).forEach(([key, value]) => {
        if (key === 'search') return; // Already handled above
        if (value && value !== 'all' && value !== '') {
            filters[`filter[${key}]`] = value as string;
        }
    });

    router.visit(route('events.index'), {
        method: 'get',
        data: filters,
        preserveState: false,
        preserveScroll: true,
        replace: true,
        only: ['events', 'eventTypes', 'tags', 'availableStatuses', 'filters'],
    });
};

const clearFilters = () => {
    Object.keys(searchForm).forEach((key) => {
        if (key === 'category' || key === 'status' || key === 'tags') {
            searchForm[key as keyof typeof searchForm] = 'all';
        } else {
            searchForm[key as keyof typeof searchForm] = '';
        }
    });

    router.visit(route('events.index'), {
        method: 'get',
        preserveState: false,
        preserveScroll: true,
        replace: true,
        only: ['events', 'eventTypes', 'tags', 'availableStatuses', 'filters'],
    });
};

const goToPage = (url: string) => {
    if (url) {
        router.visit(url, {
            preserveState: false,
            preserveScroll: true,
            replace: false,
            only: ['events', 'eventTypes', 'tags', 'availableStatuses', 'filters'],
        });
    }
};

// Manual filter application only - removed auto-apply to prevent navigation conflicts
const isFilterShow = ref(false);

const isGridView = ref(true);

const toggleView = () => {
    isGridView.value = !isGridView.value
}

const toggleFilter = () => {
    isFilterShow.value = !isFilterShow.value;
};

// Quick filter by category
const quickFilterByCategory = (category: string) => {
    searchForm.category = category;
    applyFilters();
};
</script>
