<template>
    <Head :title="seo.title">
        <!-- Only override essential meta tags that need to be dynamic -->
        <meta name="description" :content="seo.description" />
        <meta v-if="seo.keywords" name="keywords" :content="seo.keywords" />
        
        <!-- Override Open Graph title and description for dynamic content -->
        <meta property="og:title" :content="seo.og_title || seo.title" />
        <meta property="og:description" :content="seo.og_description || seo.description" />
        <meta v-if="seo.og_image" property="og:image" :content="seo.og_image" />
        <meta v-if="seo.canonical" property="og:url" :content="seo.canonical" />
        
        <!-- Twitter overrides -->
        <meta name="twitter:title" :content="seo.twitter_title || seo.title" />
        <meta name="twitter:description" :content="seo.twitter_description || seo.description" />
        <meta v-if="seo.twitter_image || seo.og_image" name="twitter:image" :content="seo.twitter_image || seo.og_image" />
        
        <!-- Canonical -->
        <link v-if="seo.canonical" rel="canonical" :href="seo.canonical" />
    </Head>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { onMounted } from 'vue'

interface SeoData {
    title: string
    description: string
    keywords?: string
    robots?: string
    og_title?: string
    og_description?: string
    og_image?: string
    og_type?: string
    twitter_card?: string
    twitter_title?: string
    twitter_description?: string
    twitter_image?: string
    canonical?: string
    structured_data?: object
}

const props = defineProps<{
    seo: SeoData
}>()

// Simple structured data handling
onMounted(() => {
    if (props.seo.structured_data) {
        const script = document.createElement('script')
        script.type = 'application/ld+json'
        script.textContent = JSON.stringify(props.seo.structured_data, null, 2)
        document.head.appendChild(script)
    }
})
</script>