<template>
    <SeoHead :seo="seo" />
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
        <NavBar text-color="text-black" background-color="bg-white " class="fixed z-20 w-full backdrop-blur border-bs border-gray-200 shadow-xs" />

        <div class="pt-20 pb-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-12">
                <!-- Header -->
                <div class="text-center mb-12">
                    <h1 class="text-3xl md:text-5xl font-bold text-gray-900 mb-6">
                        Become an Event Organizer
                    </h1>
                    <p class="text-md md:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                        Sell tickets effortlessly and securely with Suptix.
                        From creating your first ticketed event to tracking every sale, our platform empowers you to maximize ticket sales and delight your attendees.
                    </p>
                </div>
                <div class="grid lg:grid-cols-2 gap-12 items-start">
                    <!-- Benefits -->
                    <div class="space-y-8">
                        <!-- Why Choose Suptix -->
                        <div class="bg-whites  rounded-all p-8">
                            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6">Why Choose Suptix?</h2>

                            <div class="space-y-6">
                                <div class="flex items-start">
                                    <div class="rounded w-8 h-8 bg-blue-100 p-2 flex items-center justify-center mr-4 mt-1">
                                        <Zap class="h-5 w-5 text-blue-600" />
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 mb-1">Quick Setup</h3>
                                        <p class="text-gray-600">Create your first event in under 5 minutes with our intuitive event builder.</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="rounded w-8 h-8 bg-green-100 p-2  flex items-center justify-center mr-4 mt-1">
                                        <TrendingUp class="h-5 w-5 text-green-600" />
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 mb-1">Boost Attendance</h3>
                                        <p class="text-gray-600">Reach more attendees with our built-in marketing tools and event discovery features.</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="rounded w-8 h-8 bg-purple-100 p-2  flex items-center justify-center mr-4 mt-1">
                                        <Shield class="h-5 w-5 text-purple-600" />
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 mb-1">Secure Payments</h3>
                                        <p class="text-gray-600">Accept payments safely with industry-standard security and multiple payment options.</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="rounded w-8 h-8 bg-orange-100 p-2  flex items-center justify-center mr-4 mt-1">
                                        <BarChart class="h-5 w-5 text-orange-600" />
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 mb-1">Real-time Analytics</h3>
                                        <p class="text-gray-600">Track ticket sales, revenue, and attendee data with comprehensive analytics dashboard.</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="rounded w-8 h-8 bg-indigo-100 p-2  flex items-center justify-center mr-4 mt-1">
                                        <Users class="h-5 w-5 text-indigo-600" />
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 mb-1">Attendee Management</h3>
                                        <p class="text-gray-600">Manage RSVPs, send updates, and handle check-ins seamlessly.</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="rounded w-8 h-8 bg-pink-100 p-2  flex items-center justify-center mr-4 mt-1">
                                        <HeadphonesIcon class="h-5 w-5 text-pink-600" />
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 mb-1">24/7 Support</h3>
                                        <p class="text-gray-600">Get help when you need it with our dedicated support team.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing -->
                        <div class="bg-card p-8 rounded-all">
                            <h3 class="text-2xl font-bold text-gray-900 mb-4 text-center">Simple, Transparent Pricing</h3>
                            <div class="text-center">
                                <div class="text-4xl font-bold text-gray-900 mb-2">7% </div>
                                <p class="text-gray-600 mb">per ticket sold</p>
                                <p class="text-gray-500 mb-4 text-xs italic">Including transaction charges</p>
                                <div class="bg-white rounded p-4 text-left">
                                    <h4 class="font-semibold text-gray-900 mb-2">What's included:</h4>
                                    <ul class="space-y-1 text-sm text-gray-600">
                                        <li>✓ Unlimited events</li>
                                        <li>✓ Payment processing</li>
                                        <li>✓ Mobile check-in app</li>
                                        <li>✓ Analytics & reporting</li>
                                        <!-- <li>✓ Email marketing tools</li> -->
                                        <li>✓ Customer support</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sign Up CTA -->
                    <div class="bg-whites rounded-all p-8 lg:p-10">
                        <div class="text-center mb-8">
                            <h2 class="text-3xl font-bold text-gray-900 mb-3">Ready to Get Started?</h2>
                            <p class="text-gray-600">Create your organizer account and start building amazing events today.</p>
                        </div>

                        <div class="space-y-6">
                            <div class="bg-blue-50 rounded p-6">
                                <h3 class="font-semibold text-gray-900 mb-3">Getting started is easy:</h3>
                                <ol class="space-y-2 text-sm text-gray-700">
                                    <li class="flex items-center">
                                        <span class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs mr-3">1</span>
                                        Create your organizer account
                                    </li>
                                    <li class="flex items-center">
                                        <span class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs mr-3">2</span>
                                        Set up your organization profile
                                    </li>
                                    <li class="flex items-center">
                                        <span class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs mr-3">3</span>
                                        Create your first event
                                    </li>
                                    <li class="flex items-center">
                                        <span class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs mr-3">4</span>
                                        Start selling tickets!
                                    </li>
                                </ol>
                            </div>

                            <div class="text-center space-y-4">
                                <a
                                    :href="route('register')"
                                    class="text-xs md:text-lg rounded w-full inline-block bg-primary text-white py-4 px-6  font-semibold text-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-[1.02]"
                                >
                                    Create Organizer Account
                                </a>

                                <p class="text-sm text-gray-500">
                                    Already have an account?
                                    <a :href="route('login')" class="text-xs md:text-lg rounded text-blue-600 hover:text-blue-700 font-medium">Sign in</a>
                                </p>
                            </div>

                            <div class="border-t pt-6">
                                <h4 class="font-semibold text-gray-900 mb-3">Questions? We're here to help!</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center">
                                        <Mail class="h-4 w-4 text-gray-400 mr-2" />
                                        <a href="mailto:hello@suptix.live" class="text-blue-600 hover:text-blue-700">hello@suptix.live</a>
                                    </div>
                                    <div class="flex items-center">
                                        <Phone class="h-4 w-4 text-gray-400 mr-2" />
                                        <div class="grid grid-cols-2 gap-4">
                                            <a href="tel:+233242728115" class="text-blue-600 hover:text-blue-700">+233 242 728 115</a>
                                            <a href="tel:+233541341222" class="text-blue-600 hover:text-blue-700">+233 541 341 222</a>
                                        </div>
                                    </div>
                                    <!-- <div class="flex items-center">
                                        <Phone class="h-4 w-4 text-gray-400 mr-2" />
                                        <div class="grid md:grid-cols-2 gap-4">
                                            <a href="tel:+233541341222" class="text-blue-600 hover:text-blue-700">+233 541 341 222</a>
                                        </div>
                                    </div> -->
                                    <div class="flex items-center">
                                        <MessageSquare class="h-4 w-4 text-gray-400 mr-2" />
                                        <a :href="route('request-demo')" class="text-blue-600 hover:text-blue-700">Request a demo</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Testimonials // Hide this till after first three events-->
                <!-- <div class="mt-16">
                    <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">What Our Organizers Say</h2>
                    <div class="grid md:grid-cols-3 gap-8">
                        <div class="bg-white  shadow-sm p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                                    A
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-semibold text-gray-900">Alex Johnson</h4>
                                    <p class="text-sm text-gray-600">Conference Organizer</p>
                                </div>
                            </div>
                            <p class="text-gray-700 italic">"Suptix made organizing our annual tech conference a breeze. The registration process was smooth and the analytics helped us understand our audience better."</p>
                        </div>

                        <div class="bg-white  shadow-sm p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center text-white font-semibold">
                                    S
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-semibold text-gray-900">Sarah Williams</h4>
                                    <p class="text-sm text-gray-600">Wedding Planner</p>
                                </div>
                            </div>
                            <p class="text-gray-700 italic">"The RSVP management and guest communication features saved us countless hours. Our clients love how professional everything looks."</p>
                        </div>

                        <div class="bg-white  shadow-sm p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                                    M
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-semibold text-gray-900">Michael Chen</h4>
                                    <p class="text-sm text-gray-600">Community Event Organizer</p>
                                </div>
                            </div>
                            <p class="text-gray-700 italic">"From small meetups to large festivals, Suptix scales with our needs. The support team is incredibly responsive and helpful."</p>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
        <Footer />
    </div>
</template>

<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import NavBar from '@/components/NavBar.vue';
import SeoHead from '@/components/SeoHead.vue';
import { BarChart, HeadphonesIcon, Mail, MessageSquare, Phone, Shield, TrendingUp, Users, Zap } from 'lucide-vue-next';

defineProps<{
    seo: any;
}>();

// var currentYear:number = 0;
const currentDate = new Date();
const currentYear = currentDate.getFullYear();

</script>
