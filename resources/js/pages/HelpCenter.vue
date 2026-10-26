<template>
    <SeoHead :seo="seo" />
    <!-- Hero Content Section -->
    <section class="bg-gradient-to-br from-primary/10 via-background to-primary/5">
        <NavBar text-color="text-foreground" background-color="bg-background/90"
            class="fixed z-20 w-full border-b border-border backdrop-blur" />

        <div class="mx-auto h-screen items-center flex max-w-4xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
            <div class="text-center">
                <h1 class="mb-6 text-4xl leading-tight font-bold text-foreground md:text-5xl lg:text-6xl animate-fade-in">
                    Help
                    <span class="relative inline-block text-primary">
                        Center
                        <!-- Orange underline -->
                        <svg class="absolute -bottom-2 left-0 w-full h-3" viewBox="0 0 200 12"
                            preserveAspectRatio="none">
                            <path d="M0,8 Q50,2 100,6 T200,4" stroke="oklch(0.6056 0.2189 292.7172)"
                                stroke-width="3" fill="none" class="animate-draw" />
                        </svg>
                    </span>
                </h1>

                <p class="mb-8 max-w-3xl mx-auto text-lg leading-relaxed text-muted-foreground md:text-xl">
                    Find answers to frequently asked questions, get support, and learn how to make the most of Suptix.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
                    <button @click="scrollToSection('search-section')"
                        class="bg-primary cursor-pointer hover:bg-primary/90 text-primary-foreground px-8 py-4 rounded-full transition-all duration-300 transform hover:scale-105 shadow-lg">
                        Search FAQs
                    </button>
                    <button @click="scrollToSection('contact-support')"
                        class="border-2 cursor-pointer border-primary text-primary hover:bg-primary hover:text-primary-foreground px-8 py-4 rounded-full text-lg font-semibold transition-all duration-300">
                        Contact Support
                    </button>
                </div>

                <div class="flex items-center flex-wrap text-left text-sm justify-center gap-4 sm:gap-8 text-muted-foreground">
                    <div class="flex items-center">
                        <CheckCircle class="h-5 w-5 text-primary mr-2" />
                        Instant answers
                    </div>
                    <div class="flex items-center">
                        <CheckCircle class="h-5 w-5 text-primary mr-2" />
                        24/7 availability
                    </div>
                    <div class="flex items-center">
                        <CheckCircle class="h-5 w-5 text-primary mr-2" />
                        Expert support
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="min-h-screen bg-background">
        <div class="py-16 md:py-24">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

                <!-- Search Section -->
                <div id="search-section rounded-3xl" class="mb-16">
                    <div class="relative">
                        <!-- Form Background with Glass Effect -->
                        <!-- <div class="absolute inset-0 bg-gradient-to-br from-card/80 to-card/60 backdrop-blur-xl rounded-3xl"></div> -->
                        <div class="relative bg-card/90 backdrop-blur-sm p-8 rounded-2xl">
                            <h2 class="text-2xl font-bold text-foreground mb-6 text-center">Search for Help</h2>
                            <div class="relative max-w-2xl mx-auto">
                                <Input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search for help topics, features, or issues..."
                                    class="w-full px-6 py-6 pr-12 border border-border rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent bg-background transition-all"
                                />
                                <Search class="absolute right-4 top-4 h-6 w-6 text-muted-foreground" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mb-16">
                    <h2 class="text-3xl font-bold text-foreground mb-8 text-center">Quick Help Topics</h2>
                    <div class="grid md:grid-cols-3 gap-6">
                        <div class="bg-card/90 backdrop-blur-sm border border-border/50 rounded-2xl p-6 text-center shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                            <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                                <Ticket class="h-6 w-6 text-primary" />
                            </div>
                            <h3 class="text-lg font-semibold text-foreground mb-2">Buy Tickets</h3>
                            <p class="text-muted-foreground mb-4">Learn how to purchase tickets for events</p>
                            <button @click="scrollToSection('buying-tickets')" class="text-primary hover:text-primary/80 font-medium transition-colors">
                                Learn More →
                            </button>
                        </div>

                        <div class="bg-card/90 backdrop-blur-sm border border-border/50 rounded-2xl p-6 text-center shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                            <div class="w-12 h-12 bg-green-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                                <Calendar class="h-6 w-6 text-green-600" />
                            </div>
                            <h3 class="text-lg font-semibold text-foreground mb-2">Create Events</h3>
                            <p class="text-muted-foreground mb-4">Get started with organizing your own events</p>
                            <button @click="scrollToSection('creating-events')" class="text-green-600 hover:text-green-700 font-medium transition-colors">
                                Learn More →
                            </button>
                        </div>

                        <div class="bg-card/90 backdrop-blur-sm border border-border/50 rounded-2xl p-6 text-center shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                            <div class="w-12 h-12 bg-purple-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                                <RefreshCw class="h-6 w-6 text-purple-600" />
                            </div>
                            <h3 class="text-lg font-semibold text-foreground mb-2">Refunds</h3>
                            <p class="text-muted-foreground mb-4">Understand our refund policies and process</p>
                            <button @click="scrollToSection('refunds')" class="text-primary hover:text-purple-700 font-medium transition-colors">
                                Learn More →
                            </button>
                        </div>
                    </div>
                </div>

                <!-- FAQ Sections -->
                <div class="space-y-8">
                    <!-- Buying Tickets -->
                    <div id="buying-tickets" class="bg-card/90 backdrop-blur-sm border border-border/50 rounded-3xl p-8 shadow-xl">
                        <h2 class="text-2xl font-bold text-foreground mb-6 flex items-center">
                            <Ticket class="h-6 w-6 text-primary mr-2" />
                            Buying Tickets
                        </h2>

                        <div class="space-y-4">
                            <div v-for="faq in buyingTicketsFAQs" :key="faq.question" class="border-b border-border/30 pb-4 last:border-b-0">
                                <button
                                    @click="faq.isOpen = !faq.isOpen"
                                    class="w-full text-left flex justify-between items-center py-2 hover:text-primary transition-colors"
                                >
                                    <span class="font-medium text-foreground">{{ faq.question }}</span>
                                    <ChevronDown :class="{ 'transform rotate-180': faq.isOpen }" class="h-5 w-5 text-muted-foreground transition-all duration-300" />
                                </button>
                                <div v-show="faq.isOpen" class="mt-2 text-muted-foreground">
                                    {{ faq.answer }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Creating Events -->
                    <div id="creating-events" class="bg-card/90 backdrop-blur-sm border border-border/50 rounded-3xl p-8 shadow-xl">
                        <h2 class="text-2xl font-bold text-foreground mb-6 flex items-center">
                            <Calendar class="h-6 w-6 text-green-600 mr-2" />
                            Creating Events
                        </h2>

                        <div class="space-y-4">
                            <div v-for="faq in creatingEventsFAQs" :key="faq.question" class="border-b border-border/30 pb-4 last:border-b-0">
                                <button
                                    @click="faq.isOpen = !faq.isOpen"
                                    class="w-full text-left flex justify-between items-center py-2 hover:text-primary transition-colors"
                                >
                                    <span class="font-medium text-foreground">{{ faq.question }}</span>
                                    <ChevronDown :class="{ 'transform rotate-180': faq.isOpen }" class="h-5 w-5 text-muted-foreground transition-all duration-300" />
                                </button>
                                <div v-show="faq.isOpen" class="mt-2 text-muted-foreground">
                                    {{ faq.answer }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account & Payment -->
                    <div class="bg-card/90 backdrop-blur-sm border border-border/50 rounded-3xl p-8 shadow-xl">
                        <h2 class="text-2xl font-bold text-foreground mb-6 flex items-center">
                            <User class="h-6 w-6 text-orange-600 mr-2" />
                            Account & Payment
                        </h2>

                        <div class="space-y-4">
                            <div v-for="faq in accountFAQs" :key="faq.question" class="border-b border-border/30 pb-4 last:border-b-0">
                                <button
                                    @click="faq.isOpen = !faq.isOpen"
                                    class="w-full text-left flex justify-between items-center py-2 hover:text-primary transition-colors"
                                >
                                    <span class="font-medium text-foreground">{{ faq.question }}</span>
                                    <ChevronDown :class="{ 'transform rotate-180': faq.isOpen }" class="h-5 w-5 text-muted-foreground transition-all duration-300" />
                                </button>
                                <div v-show="faq.isOpen" class="mt-2 text-muted-foreground">
                                    {{ faq.answer }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Refunds & Cancellations -->
                    <div id="refunds" class="bg-card/90 backdrop-blur-sm border border-border/50 rounded-3xl p-8 shadow-xl">
                        <h2 class="text-2xl font-bold text-foreground mb-6 flex items-center">
                            <RefreshCw class="h-6 w-6 text-purple-600 mr-2" />
                            Refunds & Cancellations
                        </h2>

                        <div class="space-y-4">
                            <div v-for="faq in refundsFAQs" :key="faq.question" class="border-b border-border/30 pb-4 last:border-b-0">
                                <button
                                    @click="faq.isOpen = !faq.isOpen"
                                    class="w-full text-left flex justify-between items-center py-2 hover:text-primary transition-colors"
                                >
                                    <span class="font-medium text-foreground">{{ faq.question }}</span>
                                    <ChevronDown :class="{ 'transform rotate-180': faq.isOpen }" class="h-5 w-5 text-muted-foreground transition-all duration-300" />
                                </button>
                                <div v-show="faq.isOpen" class="mt-2 text-muted-foreground">
                                    {{ faq.answer }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Support -->
                <div id="contact-support" class="mt-16 bg-gradient-to-br from-primary/5 to-primary/10 border border-primary/20 rounded-3xl p-8 text-center">
                    <h2 class="text-2xl font-bold text-foreground mb-4">Still need help?</h2>
                    <p class="text-muted-foreground mb-8 max-w-2xl mx-auto">Our support team is here to assist you with any questions or issues. Get in touch with us through your preferred channel.</p>

                    <div class="grid md:grid-cols-3 gap-6 max-w-4xl mx-auto">
                        <div class="bg-card/90 backdrop-blur-sm border border-border/50 rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                            <Mail class="h-8 w-8 text-primary mx-auto mb-3" />
                            <h3 class="font-semibold text-foreground mb-2">Email Support</h3>
                            <p class="text-muted-foreground text-sm mb-4">Get detailed help via email</p>
                            <a href="mailto:support@suptix.live" class="text-primary hover:text-primary/80 font-medium transition-colors">
                                support@suptix.live
                            </a>
                        </div>

                        <div class="bg-card/90 backdrop-blur-sm border border-border/50 rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                            <Phone class="h-8 w-8 text-green-600 mx-auto mb-3" />
                            <h3 class="font-semibold text-foreground mb-2">Phone Support</h3>
                            <p class="text-muted-foreground text-sm mb-4">Speak with our team directly</p>
                            <a href="tel:+233242728115" class="text-green-600 hover:text-green-700 font-medium transition-colors">
                                +233242728115
                            </a>
                        </div>

                        <div class="bg-card/90 backdrop-blur-sm border border-border/50 rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                            <MessageSquare class="h-8 w-8 text-green-500 mx-auto mb-3" />
                            <h3 class="font-semibold text-foreground mb-2">WhatsApp Support</h3>
                            <p class="text-muted-foreground text-sm mb-4">Quick support via WhatsApp</p>
                            <a href="https://wa.me/233245001476" target="_blank" class="text-green-600 hover:text-green-700 font-medium transition-colors">
                                +233245001476
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <Footer />
</template>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(30px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes draw {
    from {
        stroke-dasharray: 200;
        stroke-dashoffset: 200;
    }

    to {
        stroke-dasharray: 200;
        stroke-dashoffset: 0;
    }
}

.animate-fade-in {
    animation: fade-in 1s ease-out;
}

.animate-draw {
    animation: draw 2s ease-out 0.5s both;
}

.animation-delay-1000 {
    animation-delay: 1s;
}

.animation-delay-2000 {
    animation-delay: 2s;
}
</style>

<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import SeoHead from '@/components/SeoHead.vue';
import Footer from '@/components/Footer.vue';
import { Calendar, CheckCircle, ChevronDown, Mail, MessageSquare, Phone, RefreshCw, Search, Ticket, User } from 'lucide-vue-next';
import { ref } from 'vue';
import { Input } from '@/components/ui/input';

defineProps<{
    seo: any;
}>();

const searchQuery = ref('');

const buyingTicketsFAQs = ref([
    {
        question: "How do I purchase tickets?",
        answer: "Browse events, select your preferred event, choose ticket type and quantity, then proceed to checkout. You can pay with various payment methods including credit cards and digital wallets.",
        isOpen: false
    },
    {
        question: "Do I need to create an account to buy tickets?",
        answer: "No, you can purchase tickets as a guest. However, creating an account helps you manage your tickets and order history more easily.",
        isOpen: false
    },
    {
        question: "What payment methods do you accept?",
        answer: "We accept major credit cards (Visa, MasterCard), debit cards, and mobile wallets like Mobile Money. Payment methods may vary by region.",
        isOpen: false
    },
    {
        question: "How will I receive my tickets?",
        answer: "Digital tickets are sent to your email immediately after purchase. Ticket code is also sent by sms",
        isOpen: false
    },
    {
        question: "Can I transfer my tickets to someone else?",
        answer: "Ticket transfer policies vary by event organizer. Check the event details or contact the organizer directly for transfer options.",
        isOpen: false
    }
]);

const creatingEventsFAQs = ref([
    {
        question: "How do I create an event?",
        answer: "Register as an organizer, access the admin panel, setup payment info and use our event creation tools to set up your event with all necessary details, ticket types, and pricing.",
        isOpen: false
    },
    {
        question: "What are the fees for organizers?",
        answer: "We charge a small service fee per ticket sold, which varies from 7% - 15%. Detailed pricing information is available in your organizer dashboard when you create an account.",
        isOpen: false
    },
    {
        question: "How do I get paid for ticket sales?",
        answer: "Payments are processed through secure payment providers and transferred to your account based on the payout schedule you set up in your organizer profile, usually within 24 hours on weekdays, and Mondays if the tickets were purchased over the weekend",
        isOpen: false
    },
    // {
    //     question: "Can I customize my event page?",
    //     answer: "Yes, you can add event descriptions, images, venue information, and customize ticket types and pricing through our event management tools.",
    //     isOpen: false
    // },
    {
        question: "How do I promote my event?",
        answer: "Use our built-in promotion tools, social media integration, and marketing features. You can also generate discount codes and track event analytics.",
        isOpen: false
    }
]);

const accountFAQs = ref([
    {
        question: "How do I reset my password?",
        answer: "Click 'Forgot Password' on the login page, enter your email address, and follow the instructions in the reset email we send you.",
        isOpen: false
    },
    {
        question: "How do I update my account information?",
        answer: "Log into your account and go to your profile settings to update personal information, payment methods, and preferences.",
        isOpen: false
    },
    {
        question: "Is my payment information secure?",
        answer: "Yes, we use industry-standard encryption and security measures. We don't store your full payment details - they're securely processed by our payment partners.",
        isOpen: false
    },
    {
        question: "Can I delete my account?",
        answer: "You can request account deletion by contacting our support team. Note that this action is permanent and cannot be undone.",
        isOpen: false
    }
]);

const refundsFAQs = ref([
    {
        question: "What is your refund policy?",
        answer: "Refund policies are set by individual event organizers. Check the specific event's refund policy before purchasing tickets.",
        isOpen: false
    },
    {
        question: "How do I request a refund?",
        answer: "Contact the event organizer first. If they approve the refund according to their policy, we'll process it. You can also contact our support team for assistance.",
        isOpen: false
    },
    {
        question: "How long do refunds take to process?",
        answer: "Approved refunds typically take 2-5 business days to appear in your account, depending on your payment method and bank processing times.",
        isOpen: false
    },
    {
        question: "What happens if an event is cancelled?",
        answer: "If an organizer cancels an event, refunds are usually provided according to their stated policy. We'll help facilitate the refund process.",
        isOpen: false
    }
]);

const scrollToSection = (sectionId: string) => {
    const element = document.getElementById(sectionId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
};
</script>
