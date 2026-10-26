<template>
    <div v-if="people && people.length">
        <p class="text-xs font-semibold uppercase tracking-widest text-foreground/40 mb-3">[Speakers]</p>
        <h2 class="text-3xl md:text-5xl font-bold text-foreground mb-10 leading-tight">
            Meet the <span class="text-foreground/40">Speakers &amp; Guests</span>
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div
                v-for="person in people"
                :key="person.id"
                class="flex items-start gap-4 border border-border rounded-xl p-4"
            >
                <div class="w-14 h-14 rounded-full overflow-hidden bg-muted flex items-center justify-center flex-shrink-0">
                    <img
                        v-if="person.photo_url"
                        :src="person.photo_url"
                        :alt="person.name"
                        class="w-full h-full object-cover"
                    />
                    <span v-else class="text-lg font-bold text-muted-foreground">{{ person.name.charAt(0) }}</span>
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-foreground leading-tight">{{ person.name }}</p>
                    <p class="text-sm text-primary capitalize mt-0.5">{{ person.role }}</p>
                    <p v-if="person.bio" class="text-xs text-foreground/60 mt-2 leading-relaxed line-clamp-2">{{ person.bio }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
interface EventPerson {
    id: number;
    name: string;
    role: string;
    bio?: string;
    photo_url?: string;
    session_id?: number | null;
}

defineProps<{
    people?: EventPerson[];
}>();
</script>
