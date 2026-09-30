<template>
    <!-- Horizontal Card -->
    <div
      v-if="horizontal"
      class="cursor-pointer flex h-40 overflow-hidden rounded border-0 bg-card shadow-xs transition-shadow duration-300 hover:shadow-sm"
      @click="viewEvent"
    >
      <!-- Image -->
      <div class="relative h-40 sm:h-full w-1/4 bg-transparent">
        <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.title"
             class=" sm:h-full w-full object-cover aspect-square rounded" />
        <div v-else class="flex h-full w-full items-center justify-center">
          <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>

        <!-- Status -->
        <div class="absolute top-3 right-3">
          <span class="rounded px-2 py-1 text-xs font-medium" :class="statusClasses">
            {{ currentStatus }}
          </span>
        </div>
      </div>

      <!-- Content -->
      <div class="w-3/4 flex flex-1 flex-col justify-between p-4 ">
        <div class="">
          <h3 class="mb-2 line-clamp-1 truncate text-lg font-semibold text-foreground">
            {{ event.title }}
          </h3>

          <div class="space-y-1 text-sm text-foreground/60">
            <!-- Date/Time -->
            <div class="flex items-center flex-row flex-wrap gap-2">
              <CalendarIcon class="w-4 h-4 " />
              <span>{{ formatDate(event.start_date) }}</span>
            </div>
            <div class="flex items-center gap-2">
                <ClockIcon class="w-4 h-4"/>
                <span v-if="event.start_time">{{ formatTime(event.start_time) }}</span>
            </div>

            <!-- Venue / Online -->
            <div class="flex items-center justify-start gap-2">
              <MapPinIcon class="w-4 h-4" />
              <div class="line-clamp-2">
                  <span v-if="event.mode === 'online' && event.platform_name">{{ event.platform_name }}</span>
                  <span v-else-if="event.mode === 'hybrid' && event.venue_name && event.platform_name">{{ event.venue_name }} & {{ event.platform_name }}</span>
                  <span v-else-if="event.venue_name">{{ event.venue_name }}</span>
                  <span v-else>Venue TBA</span>
              </div>
            </div>
          </div>
        </div>
        <!-- <Button @click.stop="buyTickets"
              class="rounded w-fit px-3 py-1 text-white transition-colors hover:from-blue-700 hover:to-purple-700"
              :disabled="!hasAvailableTickets">
        {{ hasAvailableTickets ? 'Buy Tickets' : 'Sold Out' }}
      </Button> -->
      </div>
    </div>

    <!-- Vertical Card (old view) -->
    <div
      v-else
      class="cursor-pointer overflow-hidden rounded border-0 bg-card shadow-xs transition-shadow duration-300 hover:shadow-sm"
      @click="viewEvent"
    >
      <!-- Image -->
      <div class="relative h-48 w-full bg-gray-200">
        <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.title"
             class="h-full w-full object-cover pointer-events-none" />
        <div v-else class="flex h-full w-full items-center justify-center">
          <svg class="h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>

        <!-- Status -->
        <div class="absolute top-3 right-3">
          <span class="rounded px-2 py-1 text-xs font-medium" :class="statusClasses">
            {{ currentStatus }}
          </span>
        </div>
      </div>

      <!-- Content -->
      <div class="p-4">
        <h3 class="mb-2 line-clamp-1 text-xl font-semibold text-foreground">
          {{ event.title }}
        </h3>

        <div class="space-y-2 text-sm text-foreground/60">
          <!-- Date/Time -->
          <div class="flex items-center gap-2">
            <CalendarIcon class="h-4 w-4" />
            <span>{{ formatDate(event.start_date) }}</span>
            <span v-if="event.start_time">at {{ formatTime(event.start_time) }}</span>
          </div>

          <!-- Venue / Online -->
          <div class="flex items-center gap-2">
            <MapPinIcon class="h-4 w-4" />
            <span v-if="event.mode === 'online' && event.platform_name">{{ event.platform_name }}</span>
            <span v-else-if="event.mode === 'hybrid' && event.venue_name && event.platform_name">{{ event.venue_name }} & {{ event.platform_name }}</span>
            <span v-else-if="event.venue_name">{{ event.venue_name }}</span>
            <span v-else>Venue TBA</span>
          </div>

          <!-- Organizer -->
          <div v-if="event.organizer" class="flex items-center gap-2">
            <UserIcon class="h-4 w-4" />
            <span>{{ event.organizer.name }}</span>
          </div>
        </div>

        <!-- Action -->
        <!-- <div class="mt-3 border-t border-gray-100 pt-2">
          <Button @click.stop="buyTickets"
                  class="rounded w-full px-4 py-2 text-white transition-colors hover:from-blue-700 hover:to-purple-700"
                  :disabled="!hasAvailableTickets">
            {{ hasAvailableTickets ? 'Buy Tickets' : 'Sold Out' }}
          </Button>
        </div> -->
      </div>
    </div>
  </template>

  <script setup lang="ts">
  import { Button } from '@/components/ui/button'
  import { router } from '@inertiajs/vue3'
  import { computed } from 'vue'
  import { CalendarIcon, Clock, ClockIcon, MapPinIcon, UserIcon } from 'lucide-vue-next'

  interface Organizer { id: number; name: string }
  interface Event {
    id: number; slug?: string; title: string; cover_image_url?: string;
    start_date: string; start_time?: string; end_date?: string; end_time?: string;
    mode?: string; online_link?: string; platform_name?: string; is_public_link?: boolean;
    status: string; venue_name?: string; organizer?: Organizer; is_rsvp?: boolean;
  }

  const props = defineProps<{ event: Event; horizontal?: boolean }>()

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

  const statusClasses = computed(() => {
    switch (currentStatus.value) {
      case 'published': return 'bg-green-100 text-green-800'
      case 'draft': return 'bg-gray-100 text-gray-800'
      case 'live': return 'bg-red-600 text-white animate-pulse'
      case 'past': return 'bg-gray-500 text-white'
      case 'canceled': return 'bg-orange-100 text-red-800'
      default: return 'bg-gray-100 text-gray-800'
    }
  })

  const hasAvailableTickets = computed(() => ['published', 'live'].includes(props.event.status))

  const formatDate = (dateString: string) =>
    new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })

  const formatTime = (timeString?: string) =>
    timeString
      ? new Date(`2000-01-01T${timeString}`).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })
      : ''

  const viewEvent = () => {
    router.visit(route('events.show', props.event.slug || props.event.id))
  }

  const buyTickets = () => {
    const eventKey = props.event.slug || props.event.id
    if (props.event.is_rsvp) {
      router.visit(route('events.rsvp.show', eventKey))
    } else {
      router.visit(route('events.tickets.show', eventKey))
    }
  }
  </script>
