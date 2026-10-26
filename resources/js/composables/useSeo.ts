import { Head } from '@inertiajs/vue3'
import { type Component, h } from 'vue'

interface SeoData {
    title?: string
    description?: string
    keywords?: string
    og_title?: string
    og_description?: string
    og_image?: string
    og_type?: string
    twitter_card?: string
    twitter_title?: string
    twitter_description?: string
    twitter_image?: string
    canonical?: string
    robots?: string
    structured_data?: object
}

export function useSeo() {
    /**
     * Create Head component with SEO meta tags
     */
    const createSeoHead = (seo: SeoData) => {
        return h(Head, {}, [
            // Title
            seo.title && h('title', seo.title),
            
            // Basic meta tags
            seo.description && h('meta', { name: 'description', content: seo.description }),
            seo.keywords && h('meta', { name: 'keywords', content: seo.keywords }),
            seo.robots && h('meta', { name: 'robots', content: seo.robots }),
            
            // Open Graph meta tags
            seo.og_title && h('meta', { property: 'og:title', content: seo.og_title }),
            seo.og_description && h('meta', { property: 'og:description', content: seo.og_description }),
            seo.og_image && h('meta', { property: 'og:image', content: seo.og_image }),
            seo.og_type && h('meta', { property: 'og:type', content: seo.og_type }),
            seo.canonical && h('meta', { property: 'og:url', content: seo.canonical }),
            
            // Twitter Card meta tags
            seo.twitter_card && h('meta', { name: 'twitter:card', content: seo.twitter_card }),
            seo.twitter_title && h('meta', { name: 'twitter:title', content: seo.twitter_title }),
            seo.twitter_description && h('meta', { name: 'twitter:description', content: seo.twitter_description }),
            seo.twitter_image && h('meta', { name: 'twitter:image', content: seo.twitter_image }),
            
            // Canonical link
            seo.canonical && h('link', { rel: 'canonical', href: seo.canonical }),
            
            // Structured data
            seo.structured_data && h('script', {
                type: 'application/ld+json',
                innerHTML: JSON.stringify(seo.structured_data)
            })
        ].filter(Boolean))
    }

    /**
     * Generate breadcrumb structured data
     */
    const createBreadcrumbs = (items: Array<{ name: string; url: string }>) => {
        return {
            '@context': 'https://schema.org',
            '@type': 'BreadcrumbList',
            'itemListElement': items.map((item, index) => ({
                '@type': 'ListItem',
                'position': index + 1,
                'name': item.name,
                'item': item.url
            }))
        }
    }

    /**
     * Generate FAQ structured data
     */
    const createFaqStructuredData = (faqs: Array<{ question: string; answer: string }>) => {
        return {
            '@context': 'https://schema.org',
            '@type': 'FAQPage',
            'mainEntity': faqs.map(faq => ({
                '@type': 'Question',
                'name': faq.question,
                'acceptedAnswer': {
                    '@type': 'Answer',
                    'text': faq.answer
                }
            }))
        }
    }

    /**
     * Generate organization structured data
     */
    const createOrganizationStructuredData = () => {
        return {
            '@context': 'https://schema.org',
            '@type': 'Organization',
            'name': 'Suptix',
            'description': 'Event ticketing and management platform',
            'url': window.location.origin,
            'logo': `${window.location.origin}/images/logo.png`,
            'sameAs': [
                'https://www.facebook.com/suptix_social',
                'https://twitter.com/suptix_social',
                'https://www.instagram.com/suptix_social'
            ],
            'contactPoint': {
                '@type': 'ContactPoint',
                'telephone': '+233242728115',
                'contactType': 'customer service',
                'availableLanguage': 'English'
            }
        }
    }

    return {
        createSeoHead,
        createBreadcrumbs,
        createFaqStructuredData,
        createOrganizationStructuredData
    }
}

export type { SeoData }