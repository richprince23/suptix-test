<template>
    <main :class="props.backgroundColor" class="bg-background shadow-xs">
        <div class="mx-auto max-w-7xl px-4 py-2 sm:px-6 lg:px-8">
            <nav class="flex items-center justify-between">
                <!-- Logo -->
                <Link :href="route('home')" class="flex items-center space-x-2">
                    <img :src="props.logoSrc" :alt="props.logoAlt" class="w-16 h-16">
                    <!-- <span :class="['text-xl font-bold', textColorClasses.primary]">Suptix</span> -->
                </Link>

                <!-- Desktop Links -->
                <div class="hidden space-x-6 sm:flex items-center">
                    <a :href="route('home')" :class="['font-medium', textColorClasses.mutedHover]">Home</a>
                    <a :href="route('events.index')" :class="['font-medium', textColorClasses.mutedHover]">Events</a>
                    <a :href="route('calendar')" :class="['font-medium', textColorClasses.mutedHover]">Calendar</a>
                    <a :href="route('orders.search.page')" :class="['font-medium', textColorClasses.mutedHover]">Find Order</a>

                    <!-- Auth Buttons -->
                    <div class="flex items-center space-x-3 ml-4">
                        <a :href="route('login')" :class="[
                            'font-medium px-4 py-2 pri-border rounded-full transition-all',
                            textColorClasses.mutedHover,
                            borderColorClass,
                            hoverBgClass
                        ]">
                            Login
                        </a>
                        <a :href="route('register')" class="font-medium bg-white text-gray-900 px-4 py-2 rounded-full hover:bg-gray-100 transition-all">
                        Sign Up
                        </a>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <button :class="[textColorClasses.primary, 'focus:outline-none sm:hidden']" @click="isOpen = !isOpen">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="!isOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </nav>

            <!-- Mobile Menu Links -->
            <transition name="fade">
                <div v-if="isOpen" class="mt-4 flex flex-col space-y-4 sm:hidden">
                    <a :href="route('home')" :class="['font-medium', textColorClasses.mutedHover]">Home</a>
                    <a :href="route('events.index')" :class="['font-medium', textColorClasses.mutedHover]">Events</a>
                    <a :href="route('calendar')" :class="['font-medium', textColorClasses.mutedHover]">Calendar</a>
                    <a :href="route('orders.search.page')" :class="['font-medium', textColorClasses.mutedHover]">Find Order</a>

                    <!-- Mobile Auth Buttons -->
                    <div :class="['border-t pt-4 flex flex-rows space-x-3', mobileBorderClass]">
                        <a :href="route('login')" :class="[
                            'font-medium text-center px-4 py-2 border rounded-full transition-all w-fit',
                            textColorClasses.mutedHover,
                            borderColorClass,
                            hoverBgClass
                        ]">
                            Login
                        </a>
                        <a :href="route('register')" class="w-fit font-medium bg-primary text-white text-center px-4 py-2 rounded-full hover:bg-gray-100 transition-all">
                        Signup
                        </a>
                    </div>
                </div>
            </transition>
        </div>
    </main>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';

interface Props {
    textColor?: string;
    backgroundColor?: string;
    logoSrc?: string;
    logoAlt?: string;
}

const props = withDefaults(defineProps<Props>(), {
    textColor: 'text-white',
    backgroundColor: 'bg-transparent',
    logoSrc: '/suptix.png',
    logoAlt: 'Suptix Logo'
});

const isOpen = ref(false);

// Compute text color variants
const textColorClasses = computed(() => ({
    primary: props.textColor,
    muted: props.textColor === 'text-white' ? 'text-white/90' : `${props.textColor}/90`,
    hover: props.textColor === 'text-white' ? 'hover:text-white' : `hover:${props.textColor}`,
    mutedHover: props.textColor === 'text-white' ? 'text-white/90 hover:text-white' : `${props.textColor}/90 hover:${props.textColor}`
}));

// Compute border and background variants
const borderColorClass = computed(() =>
    props.textColor === 'text-white' ? 'border-white/30' : 'border-current/30'
);

const hoverBgClass = computed(() =>
    props.textColor === 'text-white' ? 'hover:bg-white/10' : 'hover:bg-current/10'
);

const mobileBorderClass = computed(() =>
    props.textColor === 'text-white' ? 'border-white/20' : 'border-current/20'
);
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: all 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
