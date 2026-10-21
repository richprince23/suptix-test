<template>
    <SeoHead :seo="seo" />
    <div class="min-h-screen bg-background">
        <NavBar text-color="text-foreground" background-color="bg-background" class=" z-20 w-full border-b border-gray-200 shadow-sm" />

        <div class="pt-20 pb-16">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="bg-background shadow-sm rounded-all p-8">
                    <h1 class="text-4xl font-bold text-gray-900 mb-8">Privacy Policy</h1>
                    <p class="foreground/70 mb-8">Last updated: 25/04/2026</p>

                    <div class="prose prose-lg max-w-none">
                        <h2>Introduction</h2>
                        <p>
                            Suptix ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our event ticketing platform.
                        </p>

                        <h2>Information We Collect</h2>
                        <h3>Personal Information</h3>
                        <p>We may collect personal information that you provide directly to us, including:</p>
                        <ul>
                            <li>Name and contact information (email address, phone number)</li>
                            <li>Payment information (processed securely through our payment providers)</li>
                            <li>Event preferences and purchase history</li>
                            <li>Account credentials</li>
                        </ul>

                        <h3>Automatically Collected Information</h3>
                        <p>We automatically collect certain information when you use our platform:</p>
                        <ul>
                            <li>Device information and browser type</li>
                            <li>IP address and location data</li>
                            <li>Usage patterns and preferences</li>
                            <li>Cookies and similar tracking technologies</li>
                        </ul>

                        <h2>How We Use Your Information</h2>
                        <p>We use your information to:</p>
                        <ul>
                            <li>Process ticket purchases and event registrations</li>
                            <li>Send confirmation emails and event updates</li>
                            <li>Provide customer support</li>
                            <li>Improve our platform and services</li>
                            <li>Send marketing communications (with your consent)</li>
                            <li>Comply with legal obligations</li>
                        </ul>

                        <h2>Information Sharing</h2>
                        <p>We may share your information with:</p>
                        <ul>
                            <li>Event organizers for events you register for</li>
                            <li>Payment processors to handle transactions</li>
                            <li>Service providers who assist in platform operations</li>
                            <li>Legal authorities when required by law</li>
                        </ul>

                        <h2>Data Security</h2>
                        <p>
                            We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over the internet is 100% secure.
                        </p>

                        <h2>Your Rights</h2>
                        <p>You have the right to:</p>
                        <ul>
                            <li>Access and update your personal information</li>
                            <li>Request deletion of your data</li>
                            <li>Opt-out of marketing communications</li>
                            <li>Request data portability</li>
                        </ul>

                        <h2>Cookies</h2>
                        <p>
                            We use cookies to enhance your experience on our platform. You can control cookie settings through your browser preferences.
                        </p>

                        <h2>Third-Party Links</h2>
                        <p>
                            Our platform may contain links to third-party websites. We are not responsible for the privacy practices of these external sites.
                        </p>

                        <h2>Changes to This Policy</h2>
                        <p>
                            We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new policy on this page.
                        </p>

                        <h2>Contact Us</h2>
                        <p>
                            If you have any questions about this Privacy Policy, please contact us at:
                        </p>
                        <div class="bg-card p-4 rounded">
                            <p class="mb-2"><strong>Email:</strong> support@suptix.live</p>
                            <p class="mb-2"><strong>Address:</strong> Suptix Team</p>
                            <p class="mb-2"><strong>Phone:</strong> +233242728115 | +233541341222</p>
                            <p><strong>WhatsApp:</strong> <a href="https://wa.me/233245001476" target="_blank" class="text-green-600 hover:text-green-700">+233245001476</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <Footer />
</template>

<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';
import SeoHead from '@/components/SeoHead.vue';

defineProps<{
    seo: any;
}>();
</script>


