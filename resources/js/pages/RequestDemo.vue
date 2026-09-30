<template>
    <SeoHead :seo="seo" />
    <Toaster :close-button="true" />
    <div class="min-h-screen overflow-hidden bg-background text-foreground">
        <!-- Hero Content Section -->
        <section class="bg-gradient-to-br from-primary/10 via-background to-primary/5">
            <NavBar text-color="text-foreground" background-color="bg-background/90"
                class="fixed z-20 w-full border-b border-border backdrop-blur" />

            <div class="mx-auto h-screen items-center flex max-w-4xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
                <div class="text-center">
                    <h1 class="mb-6 text-4xl leading-tight font-bold text-foreground md:text-5xl lg:text-6xl animate-fade-in">
                        See Suptix in
                        <span class="relative inline-block text-primary">
                            Action
                            <!-- Orange underline -->
                            <svg class="absolute -bottom-2 left-0 w-full h-3" viewBox="0 0 200 12"
                                preserveAspectRatio="none">
                                <path d="M0,8 Q50,2 100,6 T200,4" stroke="oklch(0.6056 0.2189 292.7172)"
                                    stroke-width="3" fill="none" class="animate-draw" />
                            </svg>
                        </span>
                    </h1>

                    <p class="mb-8 max-w-3xl mx-auto text-lg leading-relaxed text-muted-foreground md:text-xl">
                        Experience Suptix in action—book a demo to see key features and get your questions answered.
                        </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-8">
                        <button @click="scrollToSection('demo-form')"
                        class="bg-primary cursor-pointer hover:bg-primary/90 text-primary-foreground px-8 py-4 rounded-full transition-all duration-300 transform hover:scale-105 shadow-lg">
                        Request Demo
                        </button>
                        <button @click="scrollToSection('features-section')"
                            class="border-2 cursor-pointer border-primary text-primary hover:bg-primary hover:text-primary-foreground px-8 py-4 rounded-full text-lg font-semibold transition-all duration-300">
                            See Features
                        </button>
                    </div>

                    <div class="flex items-center flex-wrap text-left text-sm justify-center gap-4 sm:gap-8 text-muted-foreground">
                        <div class="flex items-center">
                            <CheckCircle class="h-5 w-5 text-primary mr-2" />
                            30-45 minute demo
                        </div>
                        <div class="flex items-center">
                            <CheckCircle class="h-5 w-5 text-primary mr-2" />
                            Expert guidance
                        </div>
                        <div class="flex items-center">
                            <CheckCircle class="h-5 w-5 text-primary mr-2" />
                            Customized solutions
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Demo Request Section -->
        <section class="bg-background relative py-16 md:py-24" id="demo-form">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-foreground mb-6">Request Your Personalized Demo</h2>
                    <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
                        Fill out the form below and we'll schedule a comprehensive demonstration tailored to your specific needs.
                    </p>
                </div>

                <!-- Demo Request Form -->
                <div class="relative mb-16">
                    <!-- Form Background with Glass Effect -->
                    <div class="absolute inset-0 bg-gradient-to-br from-card/80 to-card/60 backdrop-blur-xl rounded-3xl"></div>
                    <div class="relative bg-card/90 backdrop-blur-sm border border-border/50 rounded-3xl p-8 lg:p-10 shadow-2xl">
                        <h3 class="text-2xl font-bold text-foreground mb-6 text-center">Demo Request Form</h3>

                            <form @submit.prevent="submitDemoRequest" class="space-y-6">
                                <!-- Personal Information -->
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

                                <div class="space-y-2">
                                    <Label for="email">Work Email *</Label>
                                    <Input
                                        v-model="form.email"
                                        type="email"
                                        id="email"
                                        required
                                        placeholder="john.doe@company.com"
                                        class="rounded-xl"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="phone">Phone Number *</Label>
                                    <Input
                                        v-model="form.phone"
                                        type="tel"
                                        id="phone"
                                        required
                                        placeholder="+233240123456"
                                        class="rounded-xl"
                                    />
                                </div>

                                <!-- Organization Information -->
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="company" class="block text-sm font-medium text-foreground mb-2">
                                            Organization Name *
                                        </label>
                                        <Input
                                            v-model="form.company"
                                            type="text"
                                            id="company"
                                            required
                                            class="w-full px-4 py-3 border border-border rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition-all bg-background"
                                            placeholder="Your Organization"
                                        />
                                    </div>
                                    <div>
                                        <label for="jobTitle" class="block text-sm font-medium text-foreground mb-2">
                                            Job Title *
                                        </label>
                                        <Input
                                            v-model="form.jobTitle"
                                            type="text"
                                            id="jobTitle"
                                            required
                                            class="w-full px-4 py-3 border border-border rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition-all bg-background"
                                            placeholder="Event Manager"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label for="companySize">Organization Size *</Label>
                                    <Select v-model="form.companySize" required>
                                        <SelectTrigger class="rounded-xl">
                                            <SelectValue placeholder="Select company size" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectLabel>Company Size</SelectLabel>
                                                <SelectItem value="1-10">1-10 employees</SelectItem>
                                                <SelectItem value="11-50">11-50 employees</SelectItem>
                                                <SelectItem value="51-200">51-200 employees</SelectItem>
                                                <SelectItem value="201-500">201-500 employees</SelectItem>
                                                <SelectItem value="501-1000">501-1000 employees</SelectItem>
                                                <SelectItem value="1000+">1000+ employees</SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <!-- Event Details -->
                                <div>
                                    <label for="eventTypes" class="block text-sm font-medium text-foreground mb-2">
                                        Types of Events You Organize *
                                    </label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <label v-for="eventType in eventTypes" :key="eventType.value" class="flex items-center">
                                            <input
                                                v-model="form.eventTypes"
                                                type="checkbox"
                                                :value="eventType.value"
                                                class="h-4 w-4 text-primary focus:ring-primary border-border rounded"
                                            />
                                            <span class="ml-2 text-sm text-foreground">{{ eventType.label }}</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="grid md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <Label for="eventsPerYear">Events Per Year *</Label>
                                        <Select v-model="form.eventsPerYear" required>
                                            <SelectTrigger class="rounded-xl">
                                                <SelectValue placeholder="Select frequency" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    <SelectLabel>Event Frequency</SelectLabel>
                                                    <SelectItem value="1-5">1-5 events</SelectItem>
                                                    <SelectItem value="6-10">6-10 events</SelectItem>
                                                    <SelectItem value="11-25">11-25 events</SelectItem>
                                                    <SelectItem value="26-50">26-50 events</SelectItem>
                                                    <SelectItem value="50+">50+ events</SelectItem>
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="averageAttendees">Average Attendees *</Label>
                                        <Select v-model="form.averageAttendees" required>
                                            <SelectTrigger class="rounded-xl">
                                                <SelectValue placeholder="Select size" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    <SelectLabel>Attendee Size</SelectLabel>
                                                    <SelectItem value="1-50">1-50 attendees</SelectItem>
                                                    <SelectItem value="51-100">51-100 attendees</SelectItem>
                                                    <SelectItem value="101-500">101-500 attendees</SelectItem>
                                                    <SelectItem value="501-1000">501-1000 attendees</SelectItem>
                                                    <SelectItem value="1000+">1000+ attendees</SelectItem>
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label for="currentSolution">Current Event Management Solution</Label>
                                    <Input
                                        v-model="form.currentSolution"
                                        type="text"
                                        id="currentSolution"
                                        placeholder="e.g., Eventbrite, manual process, other platform"
                                        class="rounded-xl"
                                    />
                                </div>

                                <!-- Demo Preferences -->
                                <div class="space-y-2">
                                    <Label for="demoFocus">What would you like to focus on during the demo? *</Label>
                                    <Textarea
                                        v-model="form.demoFocus"
                                        id="demoFocus"
                                        rows="3"
                                        required
                                        placeholder="Tell us about your specific needs, challenges, or features you're most interested in..."
                                        class="rounded-xl"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="timeline">Implementation Timeline *</Label>
                                    <Select v-model="form.timeline" required>
                                        <SelectTrigger class="rounded-xl">
                                            <SelectValue placeholder="Select timeline" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectLabel>Timeline</SelectLabel>
                                                <SelectItem value="immediate">Immediate (within 1 month)</SelectItem>
                                                <SelectItem value="1-3months">1-3 months</SelectItem>
                                                <SelectItem value="3-6months">3-6 months</SelectItem>
                                                <SelectItem value="6-12months">6-12 months</SelectItem>
                                                <SelectItem value="exploring">Just exploring options</SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <!-- Preferred Demo Time -->
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <Label for="preferredDate">Preferred Demo Date</Label>
                                        <Input
                                            v-model="form.preferredDate"
                                            type="date"
                                            id="preferredDate"
                                            :min="minDate"
                                            class="rounded-xl"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="preferredTime">Preferred Time</Label>
                                        <Select v-model="form.preferredTime">
                                            <SelectTrigger class="rounded-xl">
                                                <SelectValue placeholder="Select time preference" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    <SelectLabel>Time Preference</SelectLabel>
                                                    <SelectItem value="morning">Morning (9:00 AM - 12:00 PM)</SelectItem>
                                                    <SelectItem value="afternoon">Afternoon (12:00 PM - 5:00 PM)</SelectItem>
                                                    <SelectItem value="evening">Evening (5:00 PM - 7:00 PM)</SelectItem>
                                                    <SelectItem value="flexible">I'm flexible</SelectItem>
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>

                            <!-- Honeypot field - hidden from users -->
                            <div style="position: absolute; left: -9999px; opacity: 0; pointer-events: none;">
                                <label for="company_website">Organization Website (leave blank)</label>
                                <input
                                    v-model="form.honeypot"
                                    type="text"
                                    id="company_website"
                                    name="company_website"
                                    tabindex="-1"
                                    autocomplete="off"
                                />
                            </div>

                                <!-- Marketing Consent -->
                                <div class="space-y-3">
                                    <div class="flex items-start space-x-3">
                                        <Checkbox
                                            id="newsletter"
                                            v-model:checked="form.newsletter"
                                            class="mt-1"
                                        />
                                        <Label for="newsletter" class="text-sm font-normal cursor-pointer">
                                            I'd like to receive updates about Suptix features, events, and industry insights.
                                        </Label>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <Checkbox
                                            id="terms"
                                            v-model:checked="form.terms"
                                            required
                                            class="mt-1"
                                        />
                                        <Label for="terms" class="text-sm font-normal inline cursor-pointer">
                                            I agree to the <a href="/terms-of-service" class=" text-primary hover:text-primary/80 underline">Terms of Service</a>
                                            and <a href="/privacy-policy" class="text-primary hover:text-primary/80 underline">Privacy Policy</a> *
                                        </Label>
                                    </div>
                                </div>

                                <Button
                                    type="submit"
                                    :disabled="isSubmitting"
                                    class="w-full md:w-1/3 bg-primary py-3 px-8 rounded-full text-lg font-bold transition-all duration-300 transform hover:scale-105 shadow-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none h-auto"
                                >
                                    <span v-if="isSubmitting" class="flex items-center justify-center">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Scheduling Demo...
                                    </span>
                                    <span v-else>Request Demo</span>
                                </Button>
                            </form>

                            <!-- Success Message -->
                            <div v-if="showSuccess" class="mt-6 p-6 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-2xl">
                                <div class="flex items-center">
                                    <CheckCircle class="h-6 w-6 text-green-600 mr-3" />
                                    <div>
                                        <h4 class="text-lg font-bold text-green-800 mb-2">Demo Request Submitted!</h4>
                                        <p class="text-green-700 mb-2">
                                            Thank you for your interest in Suptix! We've received your demo request and will contact you within 24 hours.
                                        </p>
                                        <p class="text-sm text-green-600">
                                            Please check your email for confirmation and next steps.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <!-- What to Expect -->
                <div class="mb-16">
                    <div class="bg-card/90 backdrop-blur-sm border border-border/50 rounded-3xl p-8 shadow-xl">
                        <h3 class="text-2xl font-bold text-foreground mb-6 text-center">What to Expect</h3>
                        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">

                            <div class="text-center">
                                <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <Clock class="h-6 w-6 text-primary" />
                                </div>
                                <h4 class="font-semibold text-foreground mb-2">30-45 Minutes</h4>
                                <p class="text-sm text-muted-foreground">Comprehensive walkthrough tailored to your needs</p>
                            </div>

                            <div class="text-center">
                                <div class="w-12 h-12 bg-green-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <Users class="h-6 w-6 text-green-600" />
                                </div>
                                <h4 class="font-semibold text-foreground mb-2">Expert Guidance</h4>
                                <p class="text-sm text-muted-foreground">Event management specialists guide you</p>
                            </div>

                            <div class="text-center">
                                <div class="w-12 h-12 bg-purple-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <Target class="h-6 w-6 text-purple-600" />
                                </div>
                                <h4 class="font-semibold text-foreground mb-2">Customized Solutions</h4>
                                <p class="text-sm text-muted-foreground">See how Suptix solves your challenges</p>
                            </div>

                            <div class="text-center">
                                <div class="w-12 h-12 bg-orange-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <MessageSquare class="h-6 w-6 text-orange-600" />
                                </div>
                                <h4 class="font-semibold text-foreground mb-2">Q&A Session</h4>
                                <p class="text-sm text-muted-foreground">Get all your questions answered</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Key Features -->
                <div class="mb-16">
                    <div class="bg-gradient-to-br from-primary/5 to-primary/10 border border-primary/20 rounded-3xl p-8" id="features-section">
                        <h3 class="text-2xl font-bold text-foreground mb-8 text-center">Key Features We'll Show You</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="flex items-center">
                                <CheckCircle class="h-5 w-5 text-primary mr-3" />
                                <span class="text-foreground">Event Creation & Management</span>
                            </div>
                            <div class="flex items-center">
                                <CheckCircle class="h-5 w-5 text-primary mr-3" />
                                <span class="text-foreground">Ticket Sales & Distribution</span>
                            </div>
                            <div class="flex items-center">
                                <CheckCircle class="h-5 w-5 text-primary mr-3" />
                                <span class="text-foreground">Attendee Management</span>
                            </div>
                            <div class="flex items-center">
                                <CheckCircle class="h-5 w-5 text-primary mr-3" />
                                <span class="text-foreground">Analytics & Reporting</span>
                            </div>
                            <div class="flex items-center">
                                <CheckCircle class="h-5 w-5 text-primary mr-3" />
                                <span class="text-foreground">Payment Processing</span>
                            </div>
                            <div class="flex items-center">
                                <CheckCircle class="h-5 w-5 text-primary mr-3" />
                                <span class="text-foreground">Marketing Tools</span>
                            </div>
                            <div class="flex items-center">
                                <CheckCircle class="h-5 w-5 text-primary mr-3" />
                                <span class="text-foreground">Mobile App Features</span>
                            </div>
                            <div class="flex items-center">
                                <CheckCircle class="h-5 w-5 text-primary mr-3" />
                                <span class="text-foreground">Integration Capabilities</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-card/90 backdrop-blur-sm border border-border/50 rounded-3xl p-8 shadow-xl">
                    <h3 class="text-2xl font-bold text-foreground mb-8 text-center">Need Immediate Assistance?</h3>

                    <div class="grid md:grid-cols-3 gap-8">
                        <div class="text-center">
                            <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                                <Phone class="h-6 w-6 text-primary" />
                            </div>
                            <h4 class="font-medium text-foreground mb-2">Call us directly</h4>
                            <div class="flex flex-col space-y-1 mb-2">
                                <a href="tel:+233242728115" class="text-primary hover:text-primary/80 transition-colors">+233242728115</a>
                                <a href="tel:+233541341222" class="text-primary hover:text-primary/80 transition-colors">+233541341222</a>
                            </div>
                            <p class="text-sm text-muted-foreground">Monday - Friday, 9:00 AM - 6:00 PM GMT</p>
                        </div>

                        <div class="text-center">
                            <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                                <Mail class="h-6 w-6 text-primary" />
                            </div>
                            <h4 class="font-medium text-foreground mb-2">Email our sales team</h4>
                            <a href="mailto:hello@suptix.live" class="text-primary hover:text-primary/80 transition-colors block mb-2">hello@suptix.live</a>
                            <p class="text-sm text-muted-foreground">We typically respond within 24 hours</p>
                        </div>

                        <div class="text-center">
                            <div class="w-12 h-12 bg-green-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                                <MessageSquare class="h-6 w-6 text-green-500" />
                            </div>
                            <h4 class="font-medium text-foreground mb-2">WhatsApp support</h4>
                            <a href="https://wa.me/233245001476" target="_blank" class="text-green-600 hover:text-green-700 transition-colors block mb-2">+233245001476</a>
                            <p class="text-sm text-muted-foreground">Quick support via WhatsApp</p>
                        </div>
                    </div>
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
import { CheckCircle, Clock, Mail, MessageSquare, Phone, Target, Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';
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
    company: '',
    jobTitle: '',
    companySize: '',
    eventTypes: [] as string[],
    eventsPerYear: '',
    averageAttendees: '',
    currentSolution: '',
    demoFocus: '',
    timeline: '',
    preferredDate: '',
    preferredTime: '',
    newsletter: false,
    terms: false,
    honeypot: '' // Hidden field for spam protection
});

const eventTypes = [
    { value: 'conferences', label: 'Conferences' },
    { value: 'workshops', label: 'Workshops' },
    { value: 'seminars', label: 'Seminars' },
    { value: 'trade-shows', label: 'Trade Shows' },
    { value: 'networking', label: 'Networking Events' },
    { value: 'corporate', label: 'Corporate Events' },
    { value: 'weddings', label: 'Weddings' },
    { value: 'concerts', label: 'Concerts/Entertainment' },
    { value: 'sports', label: 'Sports Events' },
    { value: 'fundraising', label: 'Fundraising Events' },
    { value: 'festivals', label: 'Festivals' },
    { value: 'other', label: 'Other' }
];

const isSubmitting = ref(false);
const showSuccess = ref(false);

const minDate = computed(() => {
    const today = new Date();
    today.setDate(today.getDate() + 1); // Tomorrow
    return today.toISOString().split('T')[0];
});

const submitDemoRequest = async () => {
    isSubmitting.value = true;

    try {
        await axios.post('/api/demo-request', form.value);

        // Success
        showSuccess.value = true;

        // Show success toast
        toast.success('Demo request submitted successfully!', {
            description: "We'll contact you within 24 hours to schedule your demo.",
            duration: 5000,
        });

        // Reset form
        form.value = {
            firstName: '',
            lastName: '',
            email: '',
            phone: '',
            company: '',
            jobTitle: '',
            companySize: '',
            eventTypes: [],
            eventsPerYear: '',
            averageAttendees: '',
            currentSolution: '',
            demoFocus: '',
            timeline: '',
            preferredDate: '',
            preferredTime: '',
            newsletter: false,
            terms: false,
            honeypot: ''
        };

        // Scroll to success message
        setTimeout(() => {
            const successElement = document.querySelector('.bg-gradient-to-r');
            if (successElement) {
                successElement.scrollIntoView({ behavior: 'smooth' });
            }
        }, 100);

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
            } else {
                // Show first validation error
                const firstError = Object.values(errors)[0];
                toast.error('Please check your input', {
                    description: Array.isArray(firstError) ? firstError[0] : firstError,
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
            toast.error('Failed to submit demo request', {
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
