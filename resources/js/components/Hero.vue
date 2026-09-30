<template>
    <div class="px-8 py-32 text-center">
        <h1 class="mb-12 text-6xl font-bold text-white">
            Discover
            <span class="relative">
                events
                <!-- Red oval highlight -->
                <svg class="absolute inset-0 h-full w-full" viewBox="0 0 200 60">
                    <ellipse cx="100" cy="30" rx="95" ry="25" fill="none" stroke="#ef4444" stroke-width="3" transform="rotate(-5 100 30)" />
                </svg>
            </span>
            around you
        </h1>

        <!-- Call to Action Buttons -->
        <div class="flex flex-col justify-center gap-4 sm:flex-row">
            <button
                class="transform bg-blue-600 px-8 py-4 text-lg font-semibold text-white shadow-md transition-all duration-300 hover:scale-105 hover:bg-blue-700"
            >
                Explore Events
            </button>
            <button
                class="border-2 border-white bg-transparent px-8 py-4 text-lg font-semibold text-white transition-all duration-300 hover:bg-white hover:text-gray-900"
            >
                Learn More
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
// Hero component without search functionality
</script>
