<template>
    <div v-if="sessions && sessions.length">
        <p class="text-xs font-semibold uppercase tracking-widest text-foreground/40 mb-3">[Schedule]</p>
        <h2 class="text-3xl md:text-5xl font-bold text-foreground mb-10 leading-tight">
            Fuel Your Curiosity —<br /><span class="text-foreground/40">Discover the Schedule</span>
        </h2>

        <!-- Day tabs (multi-day only) -->
        <div v-if="isMultiDay" class="flex flex-wrap gap-2 mb-8">
            <button
                v-for="(_, date) in sessionsByDate"
                :key="date"
                @click="$emit('change-day', String(date))"
                :class="[
                    'px-4 py-1.5 rounded-full text-sm font-medium transition-colors border',
                    activeDay === String(date)
                        ? 'bg-primary text-white border-primary'
                        : 'bg-transparent text-foreground/70 border-border hover:border-primary/40'
                ]"
            >
                {{ date === 'unset' ? 'General' : formatDateShort(String(date)) }}
            </button>
        </div>

        <div class="space-y-2 border rounded-2xl p-4">
            <!-- Section divider rows -->
            <template v-for="session in activeSessions" :key="session.id">
                <div class="grid grid-cols-[140px_1fr] gap-6 items-start py-5 border-b border-border/50 last:border-0">
                    <!-- Time -->
                    <div class="pt-0.5">
                        <p class="text-sm font-semibold text-primary leading-tight">{{ formatTime(session.start_time) }}</p>
                        <p v-if="session.end_time" class="text-xs text-foreground/40 mt-0.5">{{ formatTime(session.end_time) }}</p>
                    </div>

                    <!-- Content -->
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-semibold text-foreground leading-snug text-base">{{ session.title }}</p>
                            <p v-if="session.description" class="text-sm text-foreground/55 mt-1 leading-relaxed">{{ session.description }}</p>
                            <p v-if="session.location" class="text-xs text-foreground/40 mt-1.5 font-medium">{{ session.location }}</p>

                            <!-- Speakers linked to session -->
                            <div v-if="speakersForSession(session.id).length" class="mt-3 flex flex-wrap gap-3">
                                <div
                                    v-for="person in speakersForSession(session.id)"
                                    :key="person.id"
                                    class="flex items-center gap-2"
                                >
                                    <div class="w-7 h-7 rounded-full overflow-hidden bg-muted flex items-center justify-center flex-shrink-0">
                                        <img v-if="person.photo_url" :src="person.photo_url" :alt="person.name" class="w-full h-full object-cover" />
                                        <span v-else class="text-xs font-bold text-muted-foreground">{{ person.name.charAt(0) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-foreground leading-tight">{{ person.name }}</p>
                                        <p class="text-xs text-foreground/40 capitalize">{{ person.role }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Speaker photo (first speaker thumbnail) -->
                        <div
                            v-if="speakersForSession(session.id).length && speakersForSession(session.id)[0].photo_url"
                            class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden bg-muted"
                        >
                            <img
                                :src="speakersForSession(session.id)[0].photo_url"
                                :alt="speakersForSession(session.id)[0].name"
                                class="w-full h-full object-cover"
                            />
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface EventPerson {
    id: number;
    name: string;
    role: string;
    photo_url?: string;
    session_id?: number | null;
}

interface EventSession {
    id: number;
    title: string;
    description?: string;
    session_date?: string;
    start_time: string;
    end_time?: string;
    location?: string;
    sort_order: number;
}

const props = defineProps<{
    sessions?: EventSession[];
    people?: EventPerson[];
    isMultiDay: boolean;
    sessionsByDate: Record<string, EventSession[]>;
    activeSessions: EventSession[];
    activeDay: string;
    formatTime: (t: string) => string;
    formatDateShort: (d: string) => string;
}>();

defineEmits<{ 'change-day': [day: string] }>();

const speakersForSession = (sessionId: number) => {
    return (props.people || []).filter(p => p.session_id === sessionId || p.session_id == null);
};
</script>
