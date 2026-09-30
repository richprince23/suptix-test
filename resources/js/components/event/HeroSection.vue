<template>
    <section class="relative min-h-[85vh] flex flex-col justify-end overflow-hidden">
        <!-- Background image -->
        <div class="absolute inset-0">
            <img
                v-if="coverImageUrl"
                :src="coverImageUrl"
                :alt="title"
                class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full bg-foreground/10"></div>
            <!-- Dark gradient overlay -->
            <div class="absolute inset-0 bg-linear-to-t from-black/90 via-black/50 to-black/20"></div>
        </div>

        <!-- Status badge -->
        <div class="absolute top-24 right-6 z-10">
            <span
                class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold backdrop-blur-md"
                :class="statusClasses"
            >
                <span v-if="status === 'live'" class="w-2 h-2 bg-red-400 rounded-full mr-2 animate-pulse"></span>
                {{ status.charAt(0).toUpperCase() + status.slice(1) }}
            </span>
        </div>

        <!-- Hero content -->
        <div class="relative z-10 max-w-7xl mx-auto px-6 pb-16 pt-32 w-full">
            <!-- Tags -->
            <div v-if="tags && tags.length" class="flex flex-wrap gap-2 mb-6">
                <span
                    v-for="tag in tags"
                    :key="tag"
                    class="text-xs font-medium text-white/70 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-3 py-1"
                >
                    {{ tag }}
                </span>
            </div>

            <h1 class="text-3xl md:text-7xl font-bold text-white leading-tight tracking-tight mb-8 max-w-4xl">
                {{ title }}
            </h1>

            <!-- Meta chips -->
            <div class="flex flex-wrap gap-4 mb-10">
                <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-4 py-2">
                    <Calendar class="w-4 h-4 text-white/70" />
                    <span class="text-sm text-white font-medium">{{ formattedDate }}</span>
                </div>
                <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-4 py-2">
                    <Clock class="w-4 h-4 text-white/70" />
                    <span class="text-sm text-white font-medium">{{ formattedTime }}</span>
                </div>
                <div v-if="location" class="flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-4 py-2">
                    <MapPin class="w-4 h-4 text-white/70" />
                    <span class="text-sm text-white font-medium">{{ location }}</span>
                </div>
                <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-4 py-2">
                    <Presentation class="w-4 h-4 text-white/70" />
                    <span class="text-sm text-white font-medium uppercase">{{ mode }}</span>
                </div>
            </div>

            <!-- CTAs -->
            <div class="flex flex-wrap gap-3">
                <button
                    v-if="showTicketCta"
                    @click="$emit('get-tickets')"
                    class="bg-primary text-white font-semibold px-8 py-3.5 rounded-full text-base cursor-pointer transition-opacity hover:opacity-90"
                >
                    Get Tickets
                </button>
                <button
                    v-else-if="showRsvpCta"
                    @click="$emit('rsvp')"
                    class="bg-primary text-white font-semibold px-8 py-3.5 rounded-full text-base cursor-pointer transition-opacity hover:opacity-90"
                >
                    RSVP Now
                </button>
                <button
                    @click="$emit('share')"
                    class="bg-white/10 backdrop-blur-sm border border-white/20 text-white font-semibold px-8 py-3.5 rounded-full text-base cursor-pointer transition-colors hover:bg-white/20"
                >
                    Share
                </button>
                <button
                    @click="$emit('report')"
                    class="text-red-300 border border-red-400/30 bg-red-500/10 backdrop-blur-sm font-medium px-6 py-3.5 rounded-full text-sm cursor-pointer transition-colors hover:bg-red-500/20"
                >
                    Report
                </button>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { Calendar, Clock, MapPin, Presentation } from 'lucide-vue-next';

const props = defineProps<{
    title: string;
    coverImageUrl?: string;
    status: string;
    tags?: string[];
    formattedDate: string;
    formattedTime: string;
    location?: string;
    mode?: string;
    showTicketCta: boolean;
    showRsvpCta: boolean;
    statusClasses: string;
}>();

defineEmits<{
    'get-tickets': [];
    'rsvp': [];
    'share': [];
    'report': [];
}>();
</script>
