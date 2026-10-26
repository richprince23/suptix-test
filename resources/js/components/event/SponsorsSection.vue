<template>
    <div v-if="partners && partners.length">
        <p class="text-xs font-semibold uppercase tracking-widest text-foreground/40 mb-3">[Partners]</p>
        <h2 class="text-3xl md:text-5xl font-bold text-foreground mb-10 leading-tight">
            Powered by <span class="text-foreground/40">Visionary Brands</span>
        </h2>

        <template v-if="sponsors.length">
            <p class="text-xs font-semibold uppercase tracking-widest text-foreground/40 mb-4">Sponsors</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-px borders border-border rounded-xl overflow-hidden bg-transparent mb-8">
                <a
                    v-for="sponsor in sponsors"
                    :key="sponsor.id"
                    :href="sponsor.website ?? undefined"
                    :target="sponsor.website ? '_blank' : undefined"
                    :rel="sponsor.website ? 'noopener noreferrer' : undefined"
                    class="bg-card flex flex-col items-center justify-center gap-2 p-8"
                    :class="{ 'pointer-events-none': !sponsor.website }"
                >
                    <img v-if="sponsor.logo_url" :src="sponsor.logo_url" :alt="sponsor.name" class="h-10 w-auto object-contain opacity-70" />
                    <span v-else class="text-sm font-semibold text-foreground/50">{{ sponsor.name }}</span>
                    <p v-if="sponsor.tier" class="text-xs text-foreground/30 capitalize">{{ sponsor.tier }}</p>
                </a>
            </div>
        </template>

        <template v-if="eventPartners.length">
            <p class="text-xs font-semibold uppercase tracking-widest text-foreground/40 mb-4">Partners</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-px borders border-border rounded-xl overflow-hidden bg-transparent">
                <a
                    v-for="partner in eventPartners"
                    :key="partner.id"
                    :href="partner.website ?? undefined"
                    :target="partner.website ? '_blank' : undefined"
                    :rel="partner.website ? 'noopener noreferrer' : undefined"
                    class="bg-card flex items-center justify-center p-8"
                    :class="{ 'pointer-events-none': !partner.website }"
                >
                    <img v-if="partner.logo_url" :src="partner.logo_url" :alt="partner.name" class="h-10 w-auto object-contain opacity-70" />
                    <span v-else class="text-sm font-semibold text-foreground/50">{{ partner.name }}</span>
                </a>
            </div>
        </template>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface EventPartner {
    id: number;
    name: string;
    type: 'partner' | 'sponsor';
    logo_url?: string | null;
    website?: string | null;
    tier?: string | null;
    sort_order: number;
}

const props = defineProps<{
    partners?: EventPartner[];
}>();

const sponsors = computed(() => (props.partners || []).filter(p => p.type === 'sponsor'));
const eventPartners = computed(() => (props.partners || []).filter(p => p.type === 'partner'));
</script>
