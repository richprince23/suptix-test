<template>
    <div>
        <p class="text-xs font-semibold uppercase tracking-widest text-foreground/40 mb-3">[About]</p>
        <h2 class="text-3xl md:text-5xl font-bold text-foreground mb-10 leading-tight">
            About This <span class="text-foreground/40">Event</span>
        </h2>
        <div
            v-if="description"
            v-html="description"
            class="prose max-w-none leading-relaxed text-foreground/75 text-base"
        ></div>
        <p v-else class="text-foreground/50 italic">No description available for this event.</p>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    description?: string;
}>();
</script>
