<template>
    <main class="min-h-screen overflow-hidden bg-gradient-to-br from-pink-300 via-pink-400 to-purple-500">
        <Header />

        <section class="w-full px-8 md:px-16 lg:px-40">
            <h2>Events</h2>

            <!-- Show loader while data is being fetched -->
            <!-- <div v-if="loading">
          <Loader />
        </div> -->

            <!-- Show event list once loading is complete -->
            <section class="grid grid-cols-1 lg:grid-cols-3">
                <article v-for="(event, index) in events">
                    <EventItem :event="event" />
                </article>
            </section>
        </section>
    </main>
</template>

<script setup>
import EventItem from '@/components/EventItem.vue';
import Header from '@/components/Header.vue';
import { ref } from 'vue';
const events = ref([]);
const loading = ref(true);
</script>
