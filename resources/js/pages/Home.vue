<template>
    <SeoHead :seo="seo" />
    <Toaster :close-button="true" />
    <div class="min-h-screen overflow-hidden bg-background text-foreground" ref="pageContainer">
        <!-- Hero Content Section with Video Background -->
        <section class="relative h-screen overflow-hidden">
            <NavBar text-color="text-foreground" background-color="bg-background/20"
                class="fixed z-20 w-full border-b border-primary/10 backdrop-blur" />

            <!-- Video Background -->
            <div class="pointer-events-none absolute inset-0 z-0 mx-auto">
                <video autoplay muted loop playsinline class="h-full w-full object-cover">
                    <source src="/video/bg-vid.mp4" type="video/mp4" rossorigin="anonymous" />
                </video>
                <!-- Primary color overlay -->
                <div class="absolute inset-0 bg-gradient-to-br from-primary/60  to-black/60">
                </div>
            </div>

            <!-- Content Container -->
            <div class="relative z-10 mx-auto h-screen px-4 py-16 backdrop-blur-sm sm:px-6 lg:px-8 lg:py-24">
                <div class="mx-auto flex h-full max-w-7xl flex-col items-center justify-center text-center">
                    <!-- Text Section -->
                    <div class="mb-8 text-center flex flex-col items-center justify-center">
                        <h1
                            class="mb-6 text-3xl md:text-4xl xl:text-5xl leading-tight font-bold text-white md:text-6xl lg:text-7xl animate-fade-in">
                            Unlock your new
                            <span class="relative inline-block text-primary">
                                event superpower
                                <!-- Orange underline -->
                                <svg class="absolute -bottom-2 left-0 w-full h-3" viewBox="0 0 200 12"
                                    preserveAspectRatio="none">
                                    <path d="M0,8 Q50,2 100,6 T200,4" stroke="oklch(0.3588 0.1354 278.6973)"
                                        stroke-width="3" fill="none" class="animate-draw" />
                                </svg>
                            </span>
                        </h1>

                        <p
                            class="mb-8 max-w-3xl mx-auto text-sm sm:text-md md:text-lg leading-relaxed text-white md:text-2xl">
                            Transform how you create, manage, and analyze events with the most advanced event
                            management platform.
                        </p>

                        <div
                            class="flex flex-col gap-4 justify-center items-stretch sm:flex-row sm:justify-center sm:items-center mb-8 w-full max-w-md mx-auto">
                            <button @click="scrollToSection('get-started')"
                                class="bg-primary w-full sm:w-auto cursor-pointer hover:bg-primary/90 text-primary-foreground px-8 py-3 rounded-full transition-all duration-300 transform hover:scale-105 shadow-lg text-base sm:text-lg">
                                Get Started
                            </button>
                            <Link :href="route('request-demo')"
                                class="border-2 w-full sm:w-auto cursor-pointer border-foreground text-white hover:bg-foreground hover:text-background px-8 py-3 rounded-full font-semibold transition-all duration-300 text-base sm:text-lg">
                            Request Demo
                            </Link>
                        </div>

                        <div
                            class="hidden sm:flex items-center text-left text-xs justify-center space-x-8 sm:text-sm text-white">
                            <div class="flex items-center">
                                <CheckCircle class="h-5 w-5 text-primary mr-2" />
                                No upfront charges
                            </div>
                            <div class="flex items-center">
                                <CheckCircle class="h-5 w-5 text-primary mr-2" />
                                No credit card required
                            </div>
                            <div class="flex items-center">
                                <CheckCircle class="h-5 w-5 text-primary mr-2" />
                                Get Started for free
                            </div>
                        </div>
                    </div>

                    <div class="arrow-down absolute bottom-10 w-full">
                        <button class="mx-auto animate-bounce" @click="scrollToSection('features')">
                            <ChevronDown :size="48" :stroke-width="1"
                                class="cursor-pointer text-foreground transition-all duration-500 hover:scale-150 hover:text-primary" />
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Trust Indicators -->
        <section class="bg-background py-16 border-t border-border">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <p class="text-sm text-muted-foreground uppercase tracking-wider font-medium">Trusted by industry
                        leaders</p>
                </div>
                <div v-if="stats.totalEvents > 10" class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                    <div class="group">
                        <h3 class="text-4xl font-bold text-foreground group-hover:text-primary transition-colors">
                            {{ stats.totalEvents }}+</h3>
                        <div class="text-sm text-muted-foreground">Events powered</div>
                    </div>
                    <div class="group">
                        <h3 class="text-4xl font-bold text-foreground group-hover:text-primary transition-colors">
                            {{ stats.totalTicketsSold }}+</h3>
                        <div class="text-sm text-muted-foreground">Attendees engaged</div>
                    </div>
                    <div class="group">
                        <h3 class="text-4xl font-bold text-foreground group-hover:text-primary transition-colors">
                            {{ stats.happyCustomers }}+</h3>
                        <div class="text-sm text-muted-foreground">Organizations</div>
                    </div>
                    <div class="group">
                        <h3 class="text-4xl font-bold text-foreground group-hover:text-primary transition-colors">99.9%
                        </h3>
                        <div class="text-sm text-muted-foreground">Uptime guaranteed</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="flex flex-col md:flex-row justify-center items-stretch gap-6 p-6 max-w-7xl mx-auto">
            <!-- Card 1 -->
            <div
                class="flex-1 bg-gradient-to-l from-orange-100 to-orange-300 text-primary rounded-3xl p-6 flex flex-col justify-between">
                <ol class="space-y-4 list-decimal list-inside">
                    <li>
                        <div class="bg-orange-300 text-purple-800 px-4 py-2 inline-block rounded-md font-medium">
                            Sign up
                        </div>
                    </li>
                    <li>
                        <div class="bg-purple-400 text-white px-4 py-2 inline-block rounded-md font-medium">
                            Add payment info
                        </div>
                    </li>
                    <li>
                        <div class="bg-white text-purple-800 px-4 py-2 inline-block rounded-md font-medium">
                            Setup event
                        </div>
                    </li>
                    <li>
                        <div class="bg-orange-200 text-purple-800 px-4 py-2 inline-block rounded-md font-medium">
                            Receive payments
                        </div>
                    </li>
                </ol>
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-purple-800">Go live in minutes</h3>
                    <p class="text-sm text-text-purple-800/70">Suptix saves up to 60 hours by simplifying event
                        planning.</p>
                </div>
            </div>

            <!-- Card 2 -->
            <div
                class="flex-1 bg-gradient-to-r from-purple-100 to-primary/100 rounded-3xl flex flex-col justify-between">
                <div class="">
                    <img src="@/images/theatre.jpg" alt="Event Hall" class="rounded-t-3xl object-cover w-full h-64">
                </div>
                <div class="p-6 space-y-4s text-secondary">
                    <h3 class="text-lg font-semibold ">Zero stress, all set</h3>
                    <p class="text-sm ">Suptix covers every detail, helping you plan and stay informed.
                    </p>
                </div>
            </div>

            <!-- Card 3 -->
            <div
                class="flex-1 bg-gradient-to-r from-purple-100 to-purple-300 rounded-3xl pl-6 pt-6 pb-6  flex flex-col justify-between ">
                <div class="overflow-hidden">
                    <img src="@/images/ss19.png" alt="Sales Chart"
                        class="rounded-l-2xl object-cover w-full h-64 mb-4s object-left">
                </div>
                <div class="pt-6">
                    <h3 class="text-lg font-semibold text-purple-800">Increase Revenue</h3>
                    <p class="text-sm text-purple-900">Suptix simplifies registrations and payments, boosting revenue.
                    </p>
                </div>
            </div>

        </section>

        <!-- Upcoming Events Section -->
        <section v-if="props.upcomingEvents && upcomingEvents.length > 0"
            class="py-24 bg-gradient-to-b from-background to-muted/30">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-2xl md:text-4xl font-bold text-foreground mb-6">Upcoming Events</h2>
                    <p class="text-lg md:text-xl text-muted-foreground max-w-3xl mx-auto">
                        Don't miss out on these amazing events happening soon
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="event in props.upcomingEvents" :key="event.id"
                        class="group bg-card border border-border rounded-2xl overflow-hidden hover:border-primary/50 transition-all duration-300 transform hover:-translate-y-2 hover:shadow-xl">
                        <!-- Event Image -->
                        <div class="relative h-48 overflow-hidden">
                            <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.title"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                            <div v-else
                                class="w-full h-full bg-gradient-to-br from-primary/20 to-primary/40 flex items-center justify-center">
                                <div class="text-4xl font-bold text-primary">{{ event.title.charAt(0) }}</div>
                            </div>
                            <!-- Date Badge -->
                            <div
                                class="absolute top-4 right-4 bg-background/90 backdrop-blur rounded-full px-3 py-1 text-sm font-medium text-foreground">
                                {{ formatDate(event.start_date) }}
                            </div>
                        </div>

                        <!-- Event Details -->
                        <div class="p-6">
                            <h3
                                class="text-xl font-bold text-foreground mb-3 group-hover:text-primary transition-colors">
                                {{ event.title }}
                            </h3>

                            <!-- Event Meta -->
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center text-sm text-muted-foreground">
                                    <MapPin class="w-4 h-4 mr-2 flex-shrink-0" />
                                    <span v-if="event.mode === 'online' && event.platform_name">{{ event.platform_name }}</span>
                                    <span v-else-if="event.mode === 'hybrid' && event.venue_name && event.platform_name">{{ event.venue_name }} & {{ event.platform_name }}</span>
                                    <span v-else-if="event.venue_name">{{ event.venue_name }}</span>
                                    <span v-else>Venue TBA</span>
                                </div>
                                <div v-if="event.organizer" class="flex items-center text-sm text-muted-foreground">
                                    <Users class="w-4 h-4 mr-2 flex-shrink-0" />
                                    <span>by {{ event.organizer.name }}</span>
                                </div>
                            </div>

                            <!-- View Event Button -->
                            <a :href="route('events.show', event.slug || event.id)"
                                class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground rounded-full text-sm font-medium hover:bg-primary/90 transition-all duration-200 group-hover:scale-105">
                                View Event
                                <ArrowRight class="w-4 h-4 ml-2" />
                            </a>
                        </div>
                    </div>
                </div>

                <!-- View All Events Button -->
                <div class="text-center mt-12">
                    <a :href="route('events.index')"
                        class="inline-flex items-center px-8 py-3 border-2 border-primary text-primary hover:bg-primary hover:text-primary-foreground rounded-full text-lg font-semibold transition-all duration-300">
                        View All Events
                        <ArrowRight class="w-5 h-5 ml-2" />
                    </a>
                </div>
            </div>
            <!-- <div v-if="!upcomingEvents || upcomingEvents.length === 3" class="flex flex-col items-center justify-center py-20"> -->
        </section>
        <div v-else class="flex flex-col items-center justify-center py-20">
            <svg class="w-24 h-24 text-primary/40 mb-6 animate-bounce" fill="none" stroke="currentColor"
                stroke-width="1.5" viewBox="0 0 48 48">
                <circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="2" fill="none" />
                <path d="M16 32c2.5-3 13.5-3 16 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                <circle cx="18" cy="20" r="2.5" fill="currentColor" />
                <circle cx="30" cy="20" r="2.5" fill="currentColor" />
            </svg>
            <div class="text-3xl font-extrabold text-primary mb-2">No Upcoming Events Yet!</div>
            <div class="text-lg text-muted-foreground mb-6 text-center">Looks like the stage is empty.<br>Be the first
                to create an unforgettable experience.</div>
            <a :href="route('login')"
                class="inline-flex items-center px-6 py-3 bg-primary text-primary-foreground rounded-full font-semibold text-lg shadow hover:bg-primary/90 transition-all duration-200">
                <span class="mr-2">Create Your Event</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
            </a>
        </div>


        <!-- Features Section - Creative Staggered Grid with Gradient Backgrounds -->
        <section class="py-8 md:py-16 bg-gradient-to-b from-background via-muted/30 to-background overflow-hidden"
            id="features">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-24">
                    <h2 class="text-2xl md:text-4xl font-bold text-foreground mb-6">Everything you need to supercharge
                        events</h2>
                    <p class="text-lg md:text-xl lg:text-2xl text-muted-foreground max-w-3xl mx-auto">Powerful features
                        that transform ordinary events into extraordinary experiences with data-driven insights.</p>
                </div>

                <!-- Responsive Grid Layout: Single column on mobile, 3-column staggered on desktop -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6 auto-rows-[240px]">

                    <!-- Row 1: Complete Event Management - Wide Card (2 cols x 1 row on desktop, 1 col on mobile) -->
                    <div
                        class="col-span-1 lg:col-span-2 row-span-1 group relative overflow-hidden rounded-2xl md:rounded-3xl">
                        <div class="absolute inset-0 bg-gradient-to-br from-primary/90 to-primary/60"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                        <div
                            class="relative h-full p-4 md:p-6 lg:p-8 flex flex-col items-start justify-between text-primary-foreground">
                            <div>
                                <!-- <div class="inline-flex items-center bg-white/20 backdrop-blur-sm rounded-full px-3 py-1 md:px-4 md:py-2 mb-2 md:mb-4">
                                    <span class="text-xs md:text-sm font-semibold">ALL EVENT TYPES</span>
                                </div> -->
                                <h3 class="text-sm sm:text-base md:text-lg lg:text-xl font-bold mb-2 md:mb-3">Complete
                                    Event Management</h3>
                                <p class="text-xs sm:text-sm md:text-base text-primary-foreground/90">Host in-person,
                                    online, or
                                    hybrid events. 11 event types supported from corporate to cultural events with
                                    automated ticketing and RSVP systems.</p>
                            </div>
                            <div class="text-primary-foreground/90 hidden lg:block">
                                <span class="text-sm font-semibold">Create events →</span>
                            </div>
                        </div>
                        <!-- Decorative Elements -->
                        <div
                            class="absolute -top-10 -right-10 w-20 h-20 bg-white/10 rounded-full blur-xl group-hover:scale-110 transition-transform duration-700">
                        </div>
                    </div>

                    <!-- Row 1: Revenue Analytics Dashboard - Tall Card (2 rows on desktop, 1 row on mobile) -->
                    <div
                        class="col-span-1 row-span-1 lg:row-span-2 group relative overflow-hidden rounded-2xl md:rounded-3xl">
                        <div class="absolute inset-0 bg-gradient-to-r from-violet-100 to-purple-200"></div>
                        <div class="relative h-full p-4 md:p-6 lg:p-8 flex flex-col text-violet-900">
                            <div class="mb-4 md:mb-6">
                                <h3 class="text-sm sm:text-base md:text-lg lg:text-xl font-bold mb-2 md:mb-4">Revenue
                                    Analytics Dashboard</h3>
                                <p
                                    class="text-violet-800 text-xs sm:text-sm md:text-sm lg:text-base leading-relaxed mb-3 md:mb-4">
                                    Track
                                    earnings, sales trends, and growth rates with detailed reporting. Monitor commission
                                    fees and payment success rates in real-time.</p>
                                <div class="space-y-2 md:space-y-3">
                                    <div class="flex items-center text-xs text-violet-700">
                                        • Monthly revenue comparisons
                                    </div>
                                    <div class="flex items-center text-xs text-violet-700">
                                        • Event performance metrics
                                    </div>
                                </div>
                            </div>
                            <div class="mt-auto">
                                <div class="space-y-2 mb-3 md:mb-4">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-violet-700">Payment Success Rate</span>
                                        <span class="font-semibold">98.2%</span>
                                    </div>
                                    <div class="w-full bg-violet-300 rounded-full h-1.5">
                                        <div class="bg-violet-600 rounded-full h-1.5 w-[98%]"></div>
                                    </div>
                                </div>
                                <div class="text-violet-800 text-xs md:text-sm font-medium">View analytics →</div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Paystack Integration - Single Card (1 col x 1 row) -->
                    <div class="col-span-1 row-span-1 group relative overflow-hidden rounded-2xl md:rounded-3xl">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-100 to-green-300"></div>
                        <div class="relative h-full p-4 md:p-6 lg:p-8 flex flex-col justify-between text-emerald-900">
                            <div>
                                <!-- <div class="mb-2 md:mb-3">
                                    <span class="text-xs font-medium text-emerald-800 bg-emerald-200 rounded-full px-3 py-1">SECURE PAYMENTS</span>
                                </div> -->
                                <h3 class="text-sm sm:text-base md:text-lg lg:text-xl font-bold mb-2 md:mb-3">Secure
                                    Payments</h3>
                                <p class="text-emerald-800 text-xs sm:text-sm md:text-sm lg:text-base leading-relaxed">
                                    Automated payment
                                    processing with subaccounts, real-time balance sync, and multi-currency support for
                                    Ghana.</p>
                            </div>
                            <div class="text-right">
                                <div class="text-xl md:text-2xl font-bold">GHS</div>
                                <div class="text-xs text-emerald-700">Local payments</div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: QR Code Ticket Validation - Single Card (1 col x 1 row) -->
                    <div class="col-span-1 row-span-1 group relative overflow-hidden rounded-2xl md:rounded-3xl">
                        <div class="absolute inset-0 bg-gradient-to-br from-slate-100 to-gray-200"></div>
                        <div class="relative h-full p-4 md:p-6 lg:p-8 flex flex-col justify-between text-slate-900">
                            <div>
                                <h3 class="text-sm sm:text-base md:text-lg lg:text-xl font-bold mb-2 md:mb-3">QR Code
                                    Validation
                                </h3>
                                <p class="text-slate-800 text-xs sm:text-sm md:text-sm lg:text-base leading-relaxed">
                                    Digital ticket verification
                                    with QR codes, staff check-in interface, and real-time attendance tracking.</p>
                            </div>
                            <div class="text-slate-700 text-xs md:text-sm font-medium">Scan tickets →</div>
                        </div>
                    </div>

                    <!-- Row 3: Multi-tenant Organizer System - Wide Card (2 cols on desktop, 1 col on mobile) -->
                    <div
                        class="col-span-1 lg:col-span-2 row-span-1 group relative overflow-hidden absolute inset-0 bg-gradient-to-r from-rose-100 to-pink-200 rounded-2xl md:rounded-3xl">
                        <div
                            class="relative h-full p-4 md:p-6 lg:p-8 flex flex-col items-start justify-between text-rose-900">
                            <div>
                                <h3
                                    class="text-sm sm:text-base md:text-lg lg:text-xl xl:text-2xl font-bold mb-1 md:mb-2">
                                    Multi-tenant
                                    Organizer System</h3>
                                <p class="text-rose-800 text-xs sm:text-sm md:text-sm lg:text-base">Isolated
                                    organizer accounts
                                    with dedicated admin panels, role-based permissions, and revenue splitting with
                                    automated commission tracking.</p>
                            </div>
                            <div class="text-right hidden lg:block">
                                <p class="text-sm text-rose-700">Organizers →</p>
                            </div>


                        </div>
                    </div>

                    <!-- Row 3: Discount & Promotion System - Single Card (1 col x 1 row) -->
                    <div class="col-span-1 row-span-1 group relative overflow-hidden rounded-2xl md:rounded-3xl">
                        <div class="absolute inset-0 bg-gradient-to-r from-amber-100 to-orange-200"></div>
                        <div class="relative h-full p-4 md:p-6 lg:p-8 flex flex-col justify-between text-amber-900">
                            <div>
                                <h3 class="text-sm sm:text-base md:text-lg lg:text-xl font-bold mb-2 md:mb-3">Discount &
                                    Promotions
                                </h3>
                                <p class="text-amber-800 text-xs sm:text-sm md:text-sm lg:text-base leading-relaxed">
                                    Create percentage or fixed
                                    discount codes with usage limits and date restrictions for effective event
                                    marketing.</p>
                            </div>
                            <div class="text-amber-700 text-xs md:text-sm font-medium">Create codes →</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="py-24 bg-background">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-20">
                    <h2 class="text-2xl md:text-4xl font-bold text-foreground mb-6">Your event management journey</h2>
                    <p class="text-lg md:text-xl lg:text-2xl text-muted-foreground">Three simple steps to unlock
                        powerful event insights</p>
                </div>

                <div class="grid md:grid-cols-4 gap-12">
                    <!-- Step 1 -->
                    <div class="text-center group">
                        <div class="relative mx-auto w-24 h-24 mb-8">
                            <div
                                class="w-24 h-24 bg-gradient-to-br from-primary to-primary/80 rounded-full flex items-center justify-center text-primary-foreground text-2xl font-bold group-hover:scale-110 transition-transform">
                                1
                            </div>
                            <div class="absolute -inset-2 rounded-full border-2 border-primary/30 animate-ping"></div>
                        </div>
                        <h3 class="text-2xl font-bold text-foreground mb-4">Sign Up</h3>
                        <p class="text-muted-foreground leading-relaxed">Create your organizer account and access your
                            personalized dashboard.</p>
                    </div>

                    <!-- Step 2 -->
                    <div class="text-center group">
                        <div class="relative mx-auto w-24 h-24 mb-8">
                            <div
                                class="w-24 h-24 bg-gradient-to-br from-primary to-primary/80 rounded-full flex items-center justify-center text-primary-foreground text-2xl font-bold group-hover:scale-110 transition-transform">
                                2
                            </div>
                            <div
                                class="absolute -inset-2 rounded-full border-2 border-primary/30 animate-ping animation-delay-[3000ms]">
                            </div>
                        </div>
                        <h3 class="text-2xl font-bold text-foreground mb-4">Setup Payments</h3>
                        <p class="text-muted-foreground leading-relaxed">Connect your payment account to securely
                            receive ticket sales and payouts.</p>
                    </div>
                    <!-- step 3 -->
                    <div class="text-center group">
                        <div class="relative mx-auto w-24 h-24 mb-8">
                            <div
                                class="w-24 h-24 bg-gradient-to-br from-primary to-primary/80 rounded-full flex items-center justify-center text-primary-foreground text-2xl font-bold group-hover:scale-110 transition-transform">
                                3
                            </div>
                            <div
                                class="absolute -inset-2 rounded-full border-2 border-primary/30 animate-ping animation-delay-1000">
                            </div>
                        </div>
                        <h3 class="text-2xl font-bold text-foreground mb-4">Create Your Event</h3>
                        <p class="text-muted-foreground leading-relaxed">Set up event details, ticket types, and
                            customize your registration page.</p>
                    </div>

                    <!-- Step 4 -->
                    <div class="text-center group">
                        <div class="relative mx-auto w-24 h-24 mb-8">
                            <div
                                class="w-24 h-24 bg-gradient-to-br from-primary to-primary/80 rounded-full flex items-center justify-center text-primary-foreground text-2xl font-bold group-hover:scale-110 transition-transform">
                                4
                            </div>
                            <div
                                class="absolute -inset-2 rounded-full border-2 border-primary/30 animate-ping animation-delay-2000">
                            </div>
                        </div>
                        <h3 class="text-2xl font-bold text-foreground mb-4">Sell & Track</h3>
                        <p class="text-muted-foreground leading-relaxed">Promote your event, sell tickets, and monitor
                            real-time sales and attendance.</p>
                    </div>

                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section class="py-24 bg-gradient-to-b from-muted to-background">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-20">
                    <h2 class="text-2xl md:text-4xl font-bold text-foreground mb-6">Loved by event professionals
                        worldwide</h2>
                    <p class="text-lg md:text-xl lg:text-2xl text-muted-foreground">See how industry leaders are
                        transforming their events</p>
                </div>

                <div class="grid lg:grid-cols-3 gap-8">
                    <div
                        class="bg-card/50 border border-border rounded-2xl p-8 hover:border-primary/50 transition-all duration-300 flex flex-col justify-between">
                        <div class="mb-6">
                            <div class="flex text-primary mb-6">
                                ★★★★★
                            </div>
                            <p class="text-foreground text-lg leading-relaxed mb-6">"Suptix revolutionized how we
                                understand our audience. The detailed analytics helped us increase engagement by 340%
                                and
                                revenue by 180%."</p>
                        </div>
                        <div class="flex items-center">
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-primary to-primary/80 rounded-full flex items-center justify-center text-primary-foreground font-bold mr-4">
                                S
                            </div>
                            <div>
                                <div class="font-bold text-foreground">Roger</div>
                                <div class="text-sm text-muted-foreground">Orgnaizer, INFOCTESS</div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-card/50 border border-border rounded-2xl p-8 hover:border-primary/50 transition-all duration-300 flex flex-col justify-between">
                        <div class="mb-6">
                            <div class="flex text-primary mb-6">
                                ★★★★★
                            </div>
                            <p class="text-foreground text-lg leading-relaxed mb-6">"The platform's reporting is
                                incredible. It helped us identify our peak registration times and optimize our
                                marketing spend perfectly."</p>
                        </div>
                        <div class="flex items-center">
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-primary to-primary/80 rounded-full flex items-center justify-center text-primary-foreground font-bold mr-4">
                                M
                            </div>
                            <div>
                                <div class="font-bold text-foreground">Kennedy</div>
                                <div class="text-sm text-muted-foreground">CEO, Innville</div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-card/50 border border-border rounded-2xl p-8 hover:border-primary/50 transition-all duration-300 flex flex-col justify-between">
                        <div class="mb-6">
                            <div class="flex text-primary mb-6">
                                ★★★★★
                            </div>
                            <p class="text-foreground text-lg leading-relaxed mb-6">"From complex conferences to
                                intimate workshops, Suptix scales beautifully. The analytics dashboard is pure gold for
                                strategic planning."</p>
                        </div>
                        <div class="flex items-center">
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-primary to-primary/80 rounded-full flex items-center justify-center text-primary-foreground font-bold mr-4">
                                A
                            </div>
                            <div>
                                <div class="font-bold text-foreground">Franklin</div>
                                <div class="text-sm text-muted-foreground">Founder, Techabilities</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section class="py-24 bg-background border-t border-border" id="pricing">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-20">
                    <h2 class="text-2xl md:text-4xl font-bold text-foreground mb-6">Cheapest pricing for every scale
                    </h2>
                    <p class="text-xl text-muted-foreground">Start free, scale as you grow</p>
                </div>

                <div class="bg-gradient-to-br from-card to-muted border border-border rounded-3xl overflow-hidden">
                    <div class="px-12 py-16">
                        <div class="text-center mb-12">
                            <div
                                class="inline-flex items-center bg-primary/20 border border-primary/30 rounded-full px-4 py-2 mb-6">
                                <span class="text-primary text-sm font-semibold">✨ YOUR SUPERPOWER</span>
                            </div>
                            <h3 class="text-3xl font-bold text-foreground mb-4">Perks</h3>
                            <div class="text-6xl font-bold text-foreground mb-4">7%
                                <span class="text-2xl text-muted-foreground">/ticket</span>
                            </div>
                            <p class="text-muted-foreground text-lg">which means we only get paid when you get paid</p>
                        </div>

                        <div class="grid md:grid-cols-2 gap-8 mb-12">
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <CheckCircle class="h-6 w-6 text-primary mr-3 flex-shrink-0" />
                                    <span class="text-foreground">Unlimited events & attendees</span>
                                </div>
                                <div class="flex items-center">
                                    <CheckCircle class="h-6 w-6 text-primary mr-3 flex-shrink-0" />
                                    <span class="text-foreground">Super analytics dashboard</span>
                                </div>
                                <div class="flex items-center">
                                    <CheckCircle class="h-6 w-6 text-primary mr-3 flex-shrink-0" />
                                    <span class="text-foreground">Real-time performance insights</span>
                                </div>
                                <div class="flex items-center">
                                    <CheckCircle class="h-6 w-6 text-primary mr-3 flex-shrink-0" />
                                    <span class="text-foreground">Automated payouts</span>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <CheckCircle class="h-6 w-6 text-primary mr-3 flex-shrink-0" />
                                    <span class="text-foreground">Advanced security & compliance</span>
                                </div>
                                <div class="flex items-center">
                                    <CheckCircle class="h-6 w-6 text-primary mr-3 flex-shrink-0" />
                                    <span class="text-foreground">24/7 priority support</span>
                                </div>
                                <div class="flex items-center">
                                    <CheckCircle class="h-6 w-6 text-primary mr-3 flex-shrink-0" />
                                    <span class="text-foreground">White-label options</span>
                                </div>
                                <div class="flex items-center">
                                    <CheckCircle class="h-6 w-6 text-primary mr-3 flex-shrink-0" />
                                    <span class="text-foreground">Custom integrations coming soon*</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <!-- <button @click="scrollToSection('get-started')" -->
                            <a :href="route('register')"
                                class="w-fit py-4 md:w-1/2 bg-gradient-to-r from-primary to-primary/90 hover:from-primary/90 hover:to-primary text-primary-foreground py-2 px-8 rounded-full text-xl font-bold transition-all duration-300 transform hover:scale-105 shadow-xl">
                                Get Started
                            </a>
                            <p class="text-sm text-muted-foreground mt-6">Sign up for free • No charge for non-ticketed
                                events</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <!-- Demo Request Section -->
        <section class="bg-background relative" id="demo-section">
            <div class="bg-image absolute inset-0 z-0 blur-xss">
                <img src="/images/party.jpg" alt="" class="w-full h-full object-cover object-center" />
            </div>
            <div
                class="relative bg-black/60 mx-auto w-full px-4 sm:px-6 lg:px-8 gap-8 flex flex-col md:flex-row justify-start md:justify-between items-center py-16 md:py-24">
                <div class="text-center mb-10 md:mb-0 w-full md:w-1/2 md:pr-8">
                    <h2 class="text-2xl md:text-4xl font-bold text-white mb-6">See the power in action</h2>
                    <p class="text-xl text-white ">
                        Discover how Suptix can revolutionize your event management process with a hands-on,
                        personalized demonstration.
                        Our team will walk you through the platform's powerful features, from seamless ticketing and
                        real-time analytics to advanced attendee engagement tools.
                    </p>
                </div>

                <div class="w-full md:w-1/2 bg-card border border-border rounded-3xl p-6 sm:p-10 md:p-12">
                    <h2 class="text-2xl font-bold text-foreground mb-6">Send us a message</h2>
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <div class="grid lg:grid-cols-2 gap-6">
                            <div>
                                <label for="firstName" class="block text-sm font-medium text-foreground/80 mb-2">
                                    First Name *
                                </label>
                                <Input v-model="form.firstName" placeholder="John" type="text" id="firstName" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                            <div>
                                <label for="lastName" class="block text-sm font-medium text-foreground/80 mb-2">
                                    Last Name *
                                </label>
                                <Input v-model="form.lastName" placeholder="Doe" type="text" id="lastName" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                        </div>

                        <div class="grid lg:grid-cols-2 gap-6">
                            <div class="">
                                <label for="email" class="block text-sm font-medium text-foreground/80 mb-2">
                                    Email Address *
                                </label>
                                <Input v-model="form.email" placeholder="eg. someone@acme.com" type="email" id="email"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-foreground/80 mb-2">
                                    Phone Number
                                </label>
                                <Input v-model="form.phone" placeholder="eg. 0240123456" type="tel" id="phone"
                                    class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                        </div>


                        <div>
                            <label for="subject" class="block text-sm font-medium text-foreground/80 mb-2">
                                Subject *
                            </label>
                            <Select v-model="form.subject" id="subject" required>
                                <SelectTrigger
                                    class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <SelectValue placeholder="Select a subject" />
                                </SelectTrigger>
                                <SelectContent class="bg-background rounded shadow-md">
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

                        <div>
                            <label for="message" class="block text-sm font-medium text-foreground/80 mb-2">
                                Message *
                            </label>
                            <Textarea v-model="form.message" id="message" rows="5" required
                                placeholder="Please provide as much detail as possible..."
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent"></Textarea>
                        </div>

                        <!-- Honeypot field - hidden from users -->
                        <div style="position: absolute; left: -9999px; opacity: 0; pointer-events: none;">
                            <label for="website">Website (leave blank)</label>
                            <input v-model="form.honeypot" type="text" id="website" name="website" tabindex="-1"
                                autocomplete="off" />
                        </div>

                        <div class="flex items-center">
                            <input v-model="form.newsletter" type="checkbox" id="newsletter"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                            <label for="newsletter" class="ml-2 text-sm text-foreground/80">
                                Subscribe to our newsletter for event updates and news
                            </label>
                        </div>

                        <button type="submit" :disabled="isSubmitting"
                            class="w-full rounded-full cursor-pointer bg-primary text-white py-3 px-6  font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span v-if="isSubmitting">Sending...</span>
                            <span v-else>Send Message</span>
                        </button>
                    </form>

                    <!-- Success Message -->
                    <div v-if="showSuccess" class="mt-6 p-4 bg-green-50 border border-green-200 rounded">
                        <div class="flex items-center">
                            <CheckCircle class="h-5 w-5 text-green-600 mr-2" />
                            <p class="text-green-800">Thank you! Your message has been sent successfully. We'll get back
                                to you within 24 hours.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- mobile app section -->
         <section class="bg-background py-16 lg:py-24" id="get-started">
            <div class="grid grid-cols-1 md:grid-cols-2 w-full md:max-w-5xl mx-auto items-center">
                <div class="text-section mx-auto space-y-6 px-4 md:order-first">
                    <h3 class="text-2xl md:text-4xl text-center md:text-left font-bold">Ready to check-in your attendees?</h3>
                    <p>Download the <b>Suptix Scan</b> app for seamless check-ins and event attendee management</p>
                    <div class="buttons grid grid-cols-2 w-full md:w-96 my-8 space-x-4">
                        <a href="https://play.google.com/store/apps/details?id=com.suptle.suptix_scan" target="_blank">
                            <img src="/images/google_play.png" class="h-12"/>
                        </a>

                        <a href="https://apps.apple.com/us/app/suptix-scan/id6753902194" target="_blank">
                            <img src="/images/apple_store.png" class="h-12"/>
                        </a>
                    </div>
                </div>
                <div class="mockup mx-auto order-first md:order-last">
                    <img src="/images/mockup.png" alt="suptix_scan_mockup" class="h-100">
                </div>
            </div>
         </section>
        <!-- Call to Action Section -->
        <section class="py-24 bg-gradient-to-br from-primary to-primary/90" id="get-started">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-2xl md:text-4xl font-bold text-primary-foreground mb-8">Ready to unlock your event
                    superpower?
                </h2>
                <p class="text-xl text-primary-foreground/90 mb-12 max-w-2xl mx-auto leading-relaxed">
                    Join thousands of event professionals who've transformed their events with data-driven insights and
                    optimization.
                </p>
                <div class="flex flex-col sm:flex-row gap-6 justify-center">
                    <a :href="route('register')"
                        class="bg-background text-primary px-10 py-3 rounded-full text-xl font-bold hover:bg-muted transition-all duration-300 transform hover:scale-105 shadow-xl">
                        Get Started
                    </a>
                    <Link :href="route('request-demo')"
                        class="border-2 border-primary-foreground text-primary-foreground hover:bg-primary-foreground hover:text-primary px-10 py-3 rounded-full text-xl font-bold transition-all duration-300">
                    Schedule Demo
                    </Link>
                </div>
            </div>
        </section>
        <!-- Footer -->
        <Footer />
    </div>
</template>

<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import SeoHead from '@/components/SeoHead.vue';
import Footer from '@/components/Footer.vue';
import { Link } from '@inertiajs/vue3';
import { CheckCircle, ChevronDown, MapPin, Users, ArrowRight } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast, Toaster } from 'vue-sonner';
import axios from 'axios';
import Input from '@/components/ui/input/Input.vue';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import { Select, SelectContent, SelectItem, SelectLabel, SelectGroup, SelectTrigger, SelectValue } from '@/components/ui/select';


interface Stats {
    totalEvents: number;
    upcomingEvents: number;
    totalTicketsSold: number;
    happyCustomers: number;
}

interface Event {
    id: number;
    title: string;
    slug?: string;
    start_date: string;
    start_time: string;
    cover_image_url?: string;
    platform_name?: string;
    mode?: string;
    venue?: {
        name: string;
        city: string;
        country: string;
    };
    organizer?: {
        name: string;
    };
}

const props = defineProps<{
    stats: Stats;
    seo: any;
    upcomingEvents: Event[];
}>();

const pageContainer = ref<HTMLElement>();

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

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
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

/* Custom scrollbar for dark theme */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: var(--color-muted);
}

::-webkit-scrollbar-thumb {
    background: var(--color-primary);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--color-primary);
    opacity: 0.8;
}
</style>
