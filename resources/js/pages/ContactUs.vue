<template>
    <SeoHead :seo="seo" />
    <Toaster :close-button="true" />
    <div class="min-h-screen overflow-hidden bg-background text-foreground">
        <!-- Hero Content Section -->
        <section class="bg-gradient-to-br from-primary/10 via-background to-primary/5">
            <NavBar text-color="text-foreground" background-color="bg-background/90"
                class="fixed z-20 w-full border-b border-border backdrop-blur" />

            <div class="mx-auto h-screen items-center flex max-w-4xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
                <div class="text-center flex flex-col justify-center items-center">
                    <h1 class="mb-6 text-4xl leading-tight font-bold text-foreground md:text-5xl lg:text-6xl animate-fade-in">
                        Contact
                        <span class="relative inline-block text-primary">
                            Us
                            <!-- Orange underline -->
                            <svg class="absolute -bottom-2 left-0 w-full h-3" viewBox="0 0 200 12"
                                preserveAspectRatio="none">
                                <path d="M0,8 Q50,2 100,6 T200,4" stroke="oklch(0.6056 0.2189 292.7172)"
                                    stroke-width="3" fill="none" class="animate-draw" />
                            </svg>
                        </span>
                    </h1>

                    <p class="mb-8 max-w-3xl mx-auto text-lg leading-relaxed text-muted-foreground md:text-xl">
                        Get in touch with our team - we're here to help! Whether you have questions, need support, or want to learn more about Suptix.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
                        <button @click="scrollToSection('contact-form')"
                            class="bg-primary cursor-pointer hover:bg-primary/90 text-primary-foreground px-8 py-4 rounded-full transition-all duration-300 transform hover:scale-105 shadow-lg">
                            Send Message
                        </button>
                        <button @click="scrollToSection('contact-info')"
                            class="border-2 cursor-pointer border-primary text-primary hover:bg-primary hover:text-primary-foreground px-8 py-4 rounded-full text-lg font-semibold transition-all duration-300">
                            Contact Info
                        </button>
                    </div>

                    <div class="flex items-center flex-wrap text-left text-sm justify-center gap-4 sm:gap-8 text-muted-foreground">
                        <div class="flex items-center">
                            <CheckCircle class="h-5 w-5 text-primary mr-2" />
                            24/7 Support
                        </div>
                        <div class="flex items-center">
                            <CheckCircle class="h-5 w-5 text-primary mr-2" />
                            Quick Response
                        </div>
                        <div class="flex items-center">
                            <CheckCircle class="h-5 w-5 text-primary mr-2" />
                            Expert Team
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Form Section -->
        <section class="bg-background relative py-16 md:py-24" id="contact-form">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-foreground mb-6">Send us a message</h2>
                    <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
                        Have a question or need assistance? Fill out the form below and we'll get back to you within 24 hours.
                    </p>
                </div>

                <!-- Contact Form -->
                <div class="relative mb-16">
                    <!-- Form Background with Glass Effect -->
                    <div class="absolute inset-0 bg-gradient-to-br from-card/80 to-card/60 backdrop-blur-xl rounded-3xl"></div>
                    <div class="relative bg-card/90 backdrop-blur-sm border border-border/50 rounded-3xl p-8 lg:p-10 shadow-2xl">
                        <h3 class="text-2xl font-bold text-foreground mb-6 text-center">Contact Form</h3>

                        <form @submit.prevent="submitForm" class="space-y-6">
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label for="firstName">First Name *</Label>
                                    <Input
                                        v-model="form.firstName"
                                        type="text"
                                        id="firstName"
                                        required
                                        placeholder="John"
                                        class="rounded-xl"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label for="lastName">Last Name *</Label>
                                    <Input
                                        v-model="form.lastName"
                                        type="text"
                                        id="lastName"
                                        required
                                        placeholder="Doe"
                                        class="rounded-xl"
                                    />
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label for="email">Email Address *</Label>
                                    <Input
                                        v-model="form.email"
                                        type="email"
                                        id="email"
                                        required
                                        placeholder="someone@company.com"
                                        class="rounded-xl"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="phone">Phone Number</Label>
                                    <Input
                                        v-model="form.phone"
                                        type="tel"
                                        id="phone"
                                        placeholder="0240123456"
                                        class="rounded-xl"
                                    />
                                </div>
                            </div>


                            <div class="space-y-2">
                                <Label for="subject">Subject *</Label>
                                <Select v-model="form.subject" required>
                                    <SelectTrigger class="rounded-xl">
                                        <SelectValue placeholder="Select a subject" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>Subject</SelectLabel>
                                            <SelectItem value="general">General Inquiry</SelectItem>
                                            <SelectItem value="support">Technical Support</SelectItem>
                                            <SelectItem value="billing">Billing Question</SelectItem>
                                            <SelectItem value="refund">Refund Request</SelectItem>
                                            <SelectItem value="organizer">Event Organizer Support</SelectItem>
                                            <SelectItem value="partnership">Partnership Opportunity</SelectItem>
                                            <SelectItem value="other">Other</SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-2">
                                <Label for="message">Message *</Label>
                                <Textarea
                                    v-model="form.message"
                                    id="message"
                                    rows="5"
                                    required
                                    placeholder="Please provide as much detail as possible..."
                                    class="rounded-xl"
                                />
                            </div>

                            <!-- Honeypot field - hidden from users -->
                            <div style="position: absolute; left: -9999px; opacity: 0; pointer-events: none;">
                                <Label for="website">Website (leave blank)</Label>
                                <Input
                                    v-model="form.honeypot"
                                    type="text"
                                    id="website"
                                    name="website"
                                    tabindex="-1"
                                    autocomplete="off"
                                />
                            </div>

                            <div class="flex items-center space-x-3">
                                <Checkbox
                                    id="newsletter"
                                    v-model:checked="form.newsletter"
                                />
                                <Label for="newsletter" class="text-sm font-normal cursor-pointer">
                                    Subscribe to our newsletter for event updates and news
                                </Label>
                            </div>

                            <Button
                                type="submit"
                                :disabled="isSubmitting"
                                class="w-full bg-gradient-to-r from-primary to-primary/90 hover:from-primary/90 hover:to-primary py-4 px-8 rounded-2xl text-lg font-bold transition-all duration-300 transform hover:scale-105 shadow-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none h-auto"
                            >
                                <span v-if="isSubmitting" class="flex items-center justify-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Sending...
                                </span>
                                <span v-else>Send Message</span>
                            </Button>
                        </form>

                        <!-- Success Message -->
                        <div v-if="showSuccess" class="mt-6 p-6 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-2xl">
                            <div class="flex items-center">
                                <CheckCircle class="h-6 w-6 text-green-600 mr-3" />
                                <div>
                                    <h4 class="text-lg font-bold text-green-800 mb-2">Message Sent!</h4>
                                    <p class="text-green-700">Thank you! Your message has been sent successfully. We'll get back to you within 24 hours.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-card/90 backdrop-blur-sm border border-border/50 rounded-3xl p-8 shadow-xl mb-16" id="contact-info">
                    <h3 class="text-2xl font-bold text-foreground mb-8 text-center">Get in touch</h3>

                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                        <div class="text-center">
                            <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                                <Mail class="h-6 w-6 text-primary" />
                            </div>
                            <h4 class="font-medium text-foreground mb-2">Email</h4>
                            <a href="mailto:support@suptix.live" class="text-primary hover:text-primary/80 transition-colors block mb-2">support@suptix.live</a>
                            <p class="text-sm text-muted-foreground">We typically respond within 24 hours</p>
                        </div>

                        <div class="text-center">
                            <div class="w-12 h-12 bg-green-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                                <Phone class="h-6 w-6 text-green-600" />
                            </div>
                            <h4 class="font-medium text-foreground mb-2">Phone</h4>
                            <div class="flex flex-col space-y-1 mb-2">
                                <a href="tel:+233242728115" class="text-primary hover:text-primary/80 transition-colors">+233242728115</a>
                                <a href="tel:+233541341222" class="text-primary hover:text-primary/80 transition-colors">+233541341222</a>
                            </div>
                            <p class="text-sm text-muted-foreground">Monday - Friday, 9:00 AM - 6:00 PM GMT</p>
                        </div>

                        <div class="text-center">
                            <div class="w-12 h-12 bg-green-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                                <MessageSquare class="h-6 w-6 text-green-500" />
                            </div>
                            <h4 class="font-medium text-foreground mb-2">WhatsApp</h4>
                            <a href="https://wa.me/233245001476" target="_blank" class="text-green-600 hover:text-green-700 transition-colors block mb-2">+233245001476</a>
                            <p class="text-sm text-muted-foreground">Quick support via WhatsApp</p>
                        </div>

                        <div class="text-center">
                            <div class="w-12 h-12 bg-orange-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                                <Clock class="h-6 w-6 text-orange-600" />
                            </div>
                            <h4 class="font-medium text-foreground mb-2">Business Hours</h4>
                            <div class="text-sm text-muted-foreground space-y-1">
                                <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                                <p>Saturday: 10:00 AM - 4:00 PM</p>
                                <p>Sunday: Closed</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQ Link -->
                <div class="bg-gradient-to-br from-primary/5 to-primary/10 border border-primary/20 rounded-3xl p-8 text-center">
                    <h3 class="text-2xl font-bold text-foreground mb-4">Need Quick Answers?</h3>
                    <p class="text-muted-foreground mb-6 max-w-2xl mx-auto">
                        Looking for immediate help? Check out our comprehensive FAQ section for instant answers to common questions and detailed guides.
                    </p>
                    <a
                        href="/help-center"
                        class="inline-flex items-center bg-primary hover:bg-primary/90 text-primary-foreground px-6 py-3 rounded-2xl font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg"
                    >
                        Visit Help Center
                        <ExternalLink class="ml-2 h-4 w-4" />
                    </a>
                </div>

            </div>
        </section>
        <!-- Footer -->
        <Footer />
    </div>
</template>

<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';
import SeoHead from '@/components/SeoHead.vue';
import { CheckCircle, Clock, ExternalLink, Mail, MessageSquare, Phone } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast, Toaster } from 'vue-sonner';
import axios from 'axios';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import Button from '@/components/ui/button/Button.vue';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import Checkbox from '@/components/ui/checkbox/Checkbox.vue';
import { Select, SelectContent, SelectItem, SelectLabel, SelectGroup, SelectTrigger, SelectValue } from '@/components/ui/select';

defineProps<{
    seo: any;
}>();

const form = ref({
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
    newsletter: false,
    honeypot: ''
});

const isSubmitting = ref(false);
const showSuccess = ref(false);

const submitForm = async () => {
    isSubmitting.value = true;

    try {
        await axios.post('/api/contact', form.value);

        // Success
        showSuccess.value = true;

        // Show success toast
        toast.success('Message sent successfully!', {
            description: "We'll get back to you within 24 hours.",
            duration: 5000,
        });

        // Reset form
        form.value = {
            firstName: '',
            lastName: '',
            email: '',
            phone: '',
            subject: '',
            message: '',
            newsletter: false,
            honeypot: ''
        };

        setTimeout(() => {
            showSuccess.value = false;
        }, 5000);

    } catch (error: any) {
        console.error('Form submission error:', error);

        // Handle different types of errors
        if (error.response && error.response.data && error.response.data.errors) {
            const errors = error.response.data.errors;

            // Check if it's a honeypot error or generic error
            if (errors.honeypot) {
                // Generic error message for honeypot (security)
                toast.error('Something went wrong', {
                    description: 'Please try again later or contact support.',
                    duration: 5000,
                });
            } else if (errors.message || errors.email || errors.firstName || errors.lastName || errors.subject) {
                // Specific validation errors
                const firstError = Object.values(errors)[0];
                toast.error('Validation Error', {
                    description: Array.isArray(firstError) ? firstError[0] : firstError,
                    duration: 5000,
                });
            } else {
                // Generic error for other validation cases
                toast.error('Please check your input', {
                    description: 'Some fields may be missing or invalid.',
                    duration: 5000,
                });
            }
        } else if (error.response && error.response.status === 422) {
            // Unprocessable Entity - likely honeypot or validation
            toast.error('Something went wrong', {
                description: 'Please try again later or contact support.',
                duration: 5000,
            });
        } else if (error.response && error.response.status >= 500) {
            // Server error
            toast.error('Server error', {
                description: 'Please try again later or contact support.',
                duration: 5000,
            });
        } else {
            // Network or other errors
            toast.error('Failed to send message', {
                description: 'Please check your connection and try again.',
                duration: 5000,
            });
        }
    } finally {
        isSubmitting.value = false;
    }
};

const scrollToSection = (id: string) => {
    const el = document.getElementById(id);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
    }
};
</script>

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
