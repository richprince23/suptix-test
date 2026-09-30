<template>
    <SeoHead :seo="seo" />
    <div class="min-h-screen bg-background">
        <NavBar text-color="text-foreground" background-color="bg-background " class=" z-20 w-full bg-black border-b border-gray-200 shadow-sm" />

        <div class="pt-20 pb-16">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="bg-background shadow-sm rounded-all p-8">
                    <h1 class="text-4xl font-bold text-foreground mb-8">Terms of Service</h1>
                    <p class="text-foreground/70 mb-8">Last updated: 25/04/2026</p>

                    <div class="prose prose-lg max-w-none">
                        <h2>1. Acceptance of Terms</h2>
                        <p>
                            By accessing and using Suptix, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by these terms, please do not use this service.
                        </p>

                        <h2>2. Description of Service</h2>
                        <p>
                            Suptix is an online event ticketing platform that allows users to discover, purchase, and manage event tickets. We also provide tools for event organizers to create and manage their events.
                        </p>

                        <h2>3. User Accounts</h2>
                        <h3>3.1 Registration</h3>
                        <p>
                            You may be required to create an account to use certain features. You are responsible for maintaining the confidentiality of your account credentials.
                        </p>
                        <h3>3.2 Account Responsibility</h3>
                        <p>
                            You are responsible for all activities that occur under your account. You must notify us immediately of any unauthorized use.
                        </p>

                        <h2>4. Ticket Purchases</h2>
                        <h3>4.1 Purchase Terms</h3>
                        <p>
                            All ticket purchases are subject to availability and acceptance by the event organizer. Prices are subject to change without notice.
                        </p>
                        <h3>4.2 Payment</h3>
                        <p>
                            Payment is required at the time of purchase. We accept various payment methods as displayed during checkout.
                        </p>
                        <h3>4.3 Confirmation</h3>
                        <p>
                            You will receive a confirmation email upon successful purchase. This email serves as your receipt and contains important event information.
                        </p>

                        <h2>5. Refunds and Cancellations</h2>
                        <h3>5.1 Refund Policy</h3>
                        <p>
                            Refunds are subject to the individual event organizer's refund policy. Suptix does not guarantee refunds for any event.
                        </p>
                        <h3>5.2 Event Cancellation</h3>
                        <p>
                            If an event is cancelled by the organizer, you may be entitled to a refund according to the organizer's policy.
                        </p>

                        <h2>6. User Conduct</h2>
                        <p>You agree not to:</p>
                        <ul>
                            <li>Use the service for any unlawful purpose</li>
                            <li>Attempt to gain unauthorized access to any part of the service</li>
                            <li>Interfere with or disrupt the service</li>
                            <li>Upload or transmit harmful or malicious content</li>
                            <li>Violate any applicable laws or regulations</li>
                        </ul>

                        <h2>7. Event Organizer Responsibilities</h2>
                        <p>Event organizers agree to:</p>
                        <ul>
                            <li>Provide accurate event information</li>
                            <li>Honor all ticket sales made through the platform</li>
                            <li>Comply with all applicable laws and regulations</li>
                            <li>Handle customer service for their events</li>
                        </ul>

                        <h2>8. Intellectual Property</h2>
                        <p>
                            The Suptix platform and its content are protected by copyright, trademark, and other laws. You may not reproduce, distribute, or create derivative works without our express written permission.
                        </p>

                        <h2>9. Privacy</h2>
                        <p>
                            Your privacy is important to us. Please review our Privacy Policy to understand how we collect, use, and protect your information.
                        </p>

                        <h2>10. Disclaimers</h2>
                        <p>
                            The service is provided "as is" without any warranties, express or implied. We do not guarantee that the service will be uninterrupted or error-free.
                        </p>

                        <h2>11. Limitation of Liability</h2>
                        <p>
                            In no event shall Suptix be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses.
                        </p>

                        <h2>12. Indemnification</h2>
                        <p>
                            You agree to indemnify and hold harmless Suptix from any claims, damages, or losses arising from your use of the service or violation of these terms.
                        </p>

                        <h2>13. Termination</h2>
                        <p>
                            We may terminate or suspend your account and access to the service at our sole discretion, without prior notice, for conduct that we believe violates these terms.
                        </p>

                        <h2>14. Governing Law</h2>
                        <p>
                            These terms shall be governed by and construed in accordance with the laws of the jurisdiction in which Suptix operates.
                        </p>

                        <h2>15. Changes to Terms</h2>
                        <p>
                            We reserve the right to modify these terms at any time. We will notify users of any changes by posting the updated terms on this page.
                        </p>

                        <h2>16. Contact Information</h2>
                        <p>
                            If you have any questions about these Terms of Service, please contact us:
                        </p>
                        <div class="bg-card p-4 rounded">
                            <p class="mb-2"><strong>Email:</strong> support@suptix.live</p>
                            <p class="mb-2"><strong>Address:</strong> Suptix Team</p>
                            <p><strong>Phone:</strong> +233242728115 | +233541341222</p>
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
