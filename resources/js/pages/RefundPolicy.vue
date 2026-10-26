<template>
    <SeoHead :seo="seo" />
    <div class="min-h-screen bg-background">
        <NavBar text-color="text-foreground" background-color="bg-background" class=" z-20 w-full border-b border-foreground/20 shadow-sm" />

        <div class="pt-20 pb-16">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="bg-background shadow-sm rounded-all  p-8">
                    <h1 class="text-4xl font-bold textforeground mb-8 mt-12">Refund Policy</h1>
                    <p class="text-foreground/70 mb-8">Last updated: 25/04/2026</p>

                    <div class="prose prose-lg max-w-none">
                        <h2>Overview</h2>
                        <p>
                            At Suptix, we strive to provide excellent service for all your event ticketing needs. This refund policy outlines the conditions under which refunds may be granted for ticket purchases made through our platform.
                        </p>

                        <h2>General Refund Policy</h2>
                        <p>
                            <strong>Important:</strong> Suptix acts as a ticketing platform for event organizers. Refund policies are primarily determined by individual event organizers, not by Suptix directly.
                        </p>

                        <h2>Event Organizer Refund Policies</h2>
                        <h3>Organizer-Specific Terms</h3>
                        <p>
                            Each event organizer sets their own refund policy, which is displayed during the ticket purchase process. These policies may include:
                        </p>
                        <ul>
                            <li>No refunds under any circumstances</li>
                            <li>Refunds available up to a certain date before the event</li>
                            <li>Partial refunds with cancellation fees</li>
                            <li>Full refunds under specific conditions</li>
                        </ul>

                        <h3>Finding Event Refund Policies</h3>
                        <p>
                            You can find the specific refund policy for any event:
                        </p>
                        <ul>
                            <li>On the event details page before purchase</li>
                            <li>In your ticket confirmation email</li>
                            <li>In your order details on our platform</li>
                        </ul>

                        <h2>Suptix Platform Refunds</h2>
                        <h3>Technical Issues</h3>
                        <p>
                            We may provide refunds in cases where technical issues on our platform prevent proper ticket delivery or access, including:
                        </p>
                        <ul>
                            <li>System errors during purchase completion</li>
                            <li>Failed ticket delivery due to platform malfunction</li>
                            <li>Duplicate charges due to technical glitches</li>
                        </ul>

                        <h3>Fraudulent Transactions</h3>
                        <p>
                            Refunds will be processed for transactions identified as fraudulent or unauthorized.
                        </p>

                        <h2>Event Cancellations</h2>
                        <h3>Organizer Cancellations</h3>
                        <p>
                            When an event is cancelled by the organizer:
                        </p>
                        <ul>
                            <li>Refunds are typically processed according to the organizer's stated policy</li>
                            <li>Suptix will facilitate the refund process</li>
                            <li>Processing time may vary depending on payment method</li>
                        </ul>

                        <h3>Force Majeure Events</h3>
                        <p>
                            For events cancelled due to circumstances beyond the organizer's control (natural disasters, government restrictions, etc.), refund availability depends on the organizer's policy and may include:
                        </p>
                        <ul>
                            <li>Full refunds</li>
                            <li>Event postponement with ticket transfer</li>
                            <li>Credit for future events</li>
                            <li>No refunds (as stated in organizer policy)</li>
                        </ul>

                        <h2>Refund Request Process</h2>
                        <h3>How to Request a Refund</h3>
                        <ol class="list-decimal list-inside space-y-2">
                            <li>Review the event organizer's refund policy</li>
                            <li>Contact the event organizer directly if their policy allows refunds</li>
                            <li>If unsuccessful, contact Suptix support with your order details</li>
                            <li>Provide relevant documentation (order confirmation, cancellation notice, etc.)</li>
                        </ol>

                        <h3>Required Information</h3>
                        <p>
                            When requesting a refund, please provide:
                        </p>
                        <ul>
                            <li>Order confirmation number</li>
                            <li>Email address used for purchase</li>
                            <li>Event name and date</li>
                            <li>Reason for refund request</li>
                            <li>Supporting documentation (if applicable)</li>
                        </ul>

                        <h2>Processing Time</h2>
                        <h3>Refund Timeline</h3>
                        <p>
                            When refunds are approved:
                        </p>
                        <ul>
                            <li><strong>Credit/Debit Cards:</strong> 5-10 business days</li>
                            <li><strong>Bank Transfers:</strong> 3-7 business days</li>
                            <li><strong>Digital Wallets:</strong> 1-3 business days</li>
                        </ul>

                        <h3>Processing Delays</h3>
                        <p>
                            Refund processing may be delayed due to:
                        </p>
                        <ul>
                            <li>Banking holidays or weekends</li>
                            <li>Verification requirements</li>
                            <li>Payment processor policies</li>
                            <li>Large volume of refund requests</li>
                        </ul>

                        <h2>Non-Refundable Situations</h2>
                        <p>
                            Refunds are generally not available for:
                        </p>
                        <ul>
                            <li>Change of mind after purchase</li>
                            <li>Inability to attend due to personal circumstances</li>
                            <li>Events that proceed as scheduled (unless organizer policy allows)</li>
                            <li>Failure to attend the event</li>
                            <li>Dissatisfaction with event content or quality</li>
                        </ul>

                        <h2>Chargebacks and Disputes</h2>
                        <p>
                            Before initiating a chargeback with your bank or credit card company, please contact us to resolve the issue. Chargebacks can result in additional fees and may affect your ability to use our platform in the future.
                        </p>

                        <h2>Contact Support</h2>
                        <p>
                            For refund requests or questions about this policy, contact our support team:
                        </p>
                        <div class="bg-card p-4 rounded">
                            <p class="mb-2"><strong>Email:</strong> support@suptix.live</p>
                            <p class="mb-2"><strong>Phone:</strong> +233242728115 | +233541341222</p>
                            <p><strong>WhatsApp:</strong> <a href="https://wa.me/233245001476" target="_blank" class="text-green-600 hover:text-green-700">+233245001476</a></p>
                            <p class="mb-2"><strong>Hours:</strong> Monday - Friday, 9:00 AM - 6:00 PM</p>
                            <p><strong>Response Time:</strong> Within 24-48 hours</p>
                        </div>

                        <h2>Policy Changes</h2>
                        <p>
                            We reserve the right to update this refund policy at any time. Changes will be posted on this page with an updated effective date.
                        </p>
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

