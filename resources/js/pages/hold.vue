<template>
    <SeoHead :seo="seo" />
    <div class="min-h-screen overflow-hidden bg-background text-foreground" ref="pageContainer">
        <!-- Hero Content Section with Video Background -->
        <section class="relative h-screen overflow-hidden">
            <NavBar text-color="text-foreground" background-color="bg-background/20"
                class="fixed z-20 w-full border-b border-primary/10 backdrop-blur" />

            <!-- Video Background -->
            <div class="pointer-events-none absolute inset-0 z-0 mx-auto">
                <video autoplay muted loop playsinline class="h-full w-full object-cover">
                    <source src="/video/bg-video.mp4" type="video/mp4" />
                </video>
                <!-- Primary color overlay -->
                <div class="absolute inset-0 bg-gradient-to-br from-primary/20 via-background/40 to-background/60"></div>
            </div>

            <!-- Content Container -->
            <div class="relative z-10 mx-auto h-screen px-4 py-16 backdrop-blur-sm sm:px-6 lg:px-8 lg:py-24">
                <div class="mx-auto flex h-full max-w-7xl flex-col items-center justify-center text-center">
                    <!-- Text Section -->
                    <div class="mb-8 text-center">
                        <h1 class="mb-6 text-5xl leading-tight font-bold text-foreground md:text-6xl lg:text-7xl animate-fade-in">
                            Unlock your new
                            <span class="relative inline-block text-primary">
                                event intelligence
                                <!-- Orange underline -->
                                <svg class="absolute -bottom-2 left-0 w-full h-3" viewBox="0 0 200 12" preserveAspectRatio="none">
                                    <path d="M0,8 Q50,2 100,6 T200,4" stroke="oklch(0.6056 0.2189 292.7172)" stroke-width="3" fill="none" class="animate-draw"/>
                                </svg>
                            </span>
                        </h1>

                        <p class="mb-8 max-w-3xl mx-auto text-xl leading-relaxed text-muted-foreground md:text-2xl">
                            Transform how you create, manage, and analyze events with the most advanced event intelligence platform.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
                            <button @click="scrollToSection('get-started')"
                                class="bg-primary hover:bg-primary/90 text-primary-foreground px-8 py-4 rounded-full text-lg font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg">
                                Start Free Trial
                            </button>
                            <button @click="scrollToSection('demo-section')"
                                class="border-2 border-foreground text-foreground hover:bg-foreground hover:text-background px-8 py-4 rounded-full text-lg font-semibold transition-all duration-300">
                                Watch Demo
                            </button>
                        </div>

                        <div class="flex items-center justify-center space-x-8 text-sm text-muted-foreground">
                            <div class="flex items-center">
                                <CheckCircle class="h-5 w-5 text-primary mr-2" />
                                Free 30-day trial
                            </div>
                            <div class="flex items-center">
                                <CheckCircle class="h-5 w-5 text-primary mr-2" />
                                No credit card required
                            </div>
                            <div class="flex items-center">
                                <CheckCircle class="h-5 w-5 text-primary mr-2" />
                                Cancel anytime
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
                    <p class="text-sm text-muted-foreground uppercase tracking-wider font-medium">Trusted by industry leaders</p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                    <div class="group">
                        <div class="text-4xl font-bold text-foreground group-hover:text-primary transition-colors">{{ stats.totalEvents }}+</div>
                        <div class="text-sm text-muted-foreground">Events powered</div>
                    </div>
                    <div class="group">
                        <div class="text-4xl font-bold text-foreground group-hover:text-primary transition-colors">{{ stats.totalTicketsSold }}+</div>
                        <div class="text-sm text-muted-foreground">Attendees engaged</div>
                    </div>
                    <div class="group">
                        <div class="text-4xl font-bold text-foreground group-hover:text-primary transition-colors">{{ stats.happyCustomers }}+</div>
                        <div class="text-sm text-muted-foreground">Organizations</div>
                    </div>
                    <div class="group">
                        <div class="text-4xl font-bold text-foreground group-hover:text-primary transition-colors">99.9%</div>
                        <div class="text-sm text-muted-foreground">Uptime guaranteed</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-24 bg-gradient-to-b from-background to-muted" id="features">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-20">
                    <h2 class="text-5xl font-bold text-foreground mb-6">Everything you need for intelligent events</h2>
                    <p class="text-xl text-muted-foreground max-w-3xl mx-auto">Powerful features that transform ordinary events into extraordinary experiences with data-driven insights.</p>
                </div>

                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Smart Analytics -->
                    <div class="group bg-card/50 border border-border hover:border-primary/50 rounded-2xl p-8 transition-all duration-300 hover:transform hover:scale-105">
                        <div class="mb-6">
                            <div class="w-16 h-16 bg-gradient-to-br from-primary to-primary/80 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <BarChart3 class="h-8 w-8 text-primary-foreground" />
                            </div>
                            <h3 class="text-2xl font-bold text-foreground mb-4">Smart Analytics</h3>
                            <p class="text-muted-foreground leading-relaxed">Get real-time insights into attendee behavior, engagement patterns, and revenue optimization opportunities.</p>
                        </div>
                        <div class="text-primary font-semibold group-hover:text-primary/90 transition-colors">
                            Advanced reporting →
                        </div>
                    </div>

                    <!-- AI-Powered Insights -->
                    <div class="group bg-card/50 border border-border hover:border-primary/50 rounded-2xl p-8 transition-all duration-300 hover:transform hover:scale-105">
                        <div class="mb-6">
                            <div class="w-16 h-16 bg-gradient-to-br from-primary to-primary/80 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <Zap class="h-8 w-8 text-primary-foreground" />
                            </div>
                            <h3 class="text-2xl font-bold text-foreground mb-4">AI-Powered Automation</h3>
                            <p class="text-muted-foreground leading-relaxed">Automate event promotion, attendee communication, and follow-ups with intelligent algorithms.</p>
                        </div>
                        <div class="text-primary font-semibold group-hover:text-primary/90 transition-colors">
                            Smart automation →
                        </div>
                    </div>

                    <!-- Seamless Integration -->
                    <div class="group bg-card/50 border border-border hover:border-primary/50 rounded-2xl p-8 transition-all duration-300 hover:transform hover:scale-105">
                        <div class="mb-6">
                            <div class="w-16 h-16 bg-gradient-to-br from-primary to-primary/80 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <Globe class="h-8 w-8 text-primary-foreground" />
                            </div>
                            <h3 class="text-2xl font-bold text-foreground mb-4">Global Reach</h3>
                            <p class="text-muted-foreground leading-relaxed">Scale your events globally with multi-language support, currency conversion, and local integrations.</p>
                        </div>
                        <div class="text-primary font-semibold group-hover:text-primary/90 transition-colors">
                            Global scaling →
                        </div>
                    </div>

                    <!-- Event Creation -->
                    <div class="group bg-card/50 border border-border hover:border-primary/50 rounded-2xl p-8 transition-all duration-300 hover:transform hover:scale-105">
                        <div class="mb-6">
                            <div class="w-16 h-16 bg-gradient-to-br from-primary to-primary/80 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <Calendar class="h-8 w-8 text-primary-foreground" />
                            </div>
                            <h3 class="text-2xl font-bold text-foreground mb-4">Intelligent Creation</h3>
                            <p class="text-muted-foreground leading-relaxed">Create stunning events in minutes with AI-assisted templates and content suggestions.</p>
                        </div>
                        <div class="text-primary font-semibold group-hover:text-primary/90 transition-colors">
                            Quick setup →
                        </div>
                    </div>

                    <!-- Attendee Experience -->
                    <div class="group bg-card/50 border border-border hover:border-primary/50 rounded-2xl p-8 transition-all duration-300 hover:transform hover:scale-105">
                        <div class="mb-6">
                            <div class="w-16 h-16 bg-gradient-to-br from-primary to-primary/80 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <Users class="h-8 w-8 text-primary-foreground" />
                            </div>
                            <h3 class="text-2xl font-bold text-foreground mb-4">Premium Experience</h3>
                            <p class="text-muted-foreground leading-relaxed">Deliver exceptional attendee experiences with personalized journeys and seamless interactions.</p>
                        </div>
                        <div class="text-primary font-semibold group-hover:text-primary/90 transition-colors">
                            Enhanced UX →
                        </div>
                    </div>

                    <!-- Security -->
                    <div class="group bg-card/50 border border-border hover:border-primary/50 rounded-2xl p-8 transition-all duration-300 hover:transform hover:scale-105">
                        <div class="mb-6">
                            <div class="w-16 h-16 bg-gradient-to-br from-primary to-primary/80 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <Shield class="h-8 w-8 text-primary-foreground" />
                            </div>
                            <h3 class="text-2xl font-bold text-foreground mb-4">Enterprise Security</h3>
                            <p class="text-muted-foreground leading-relaxed">Bank-level security with SOC 2 compliance, data encryption, and privacy protection.</p>
                        </div>
                        <div class="text-primary font-semibold group-hover:text-primary/90 transition-colors">
                            Secure platform →
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="py-24 bg-background">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-20">
                    <h2 class="text-5xl font-bold text-foreground mb-6">Your event intelligence journey</h2>
                    <p class="text-xl text-muted-foreground">Three simple steps to unlock powerful event insights</p>
                </div>

                <div class="grid md:grid-cols-3 gap-12">
                    <!-- Step 1 -->
                    <div class="text-center group">
                        <div class="relative mx-auto w-24 h-24 mb-8">
                            <div class="w-24 h-24 bg-gradient-to-br from-primary to-primary/80 rounded-full flex items-center justify-center text-primary-foreground text-2xl font-bold group-hover:scale-110 transition-transform">
                                1
                            </div>
                            <div class="absolute -inset-2 rounded-full border-2 border-primary/30 animate-ping"></div>
                        </div>
                        <h3 class="text-2xl font-bold text-foreground mb-4">Connect & Configure</h3>
                        <p class="text-muted-foreground leading-relaxed">Set up your intelligent event dashboard and connect your existing tools and data sources.</p>
                    </div>

                    <!-- Step 2 -->
                    <div class="text-center group">
                        <div class="relative mx-auto w-24 h-24 mb-8">
                            <div class="w-24 h-24 bg-gradient-to-br from-primary to-primary/80 rounded-full flex items-center justify-center text-primary-foreground text-2xl font-bold group-hover:scale-110 transition-transform">
                                2
                            </div>
                            <div class="absolute -inset-2 rounded-full border-2 border-primary/30 animate-ping animation-delay-1000"></div>
                        </div>
                        <h3 class="text-2xl font-bold text-foreground mb-4">Launch & Monitor</h3>
                        <p class="text-muted-foreground leading-relaxed">Create and launch your events while our AI monitors performance and suggests optimizations.</p>
                    </div>

                    <!-- Step 3 -->
                    <div class="text-center group">
                        <div class="relative mx-auto w-24 h-24 mb-8">
                            <div class="w-24 h-24 bg-gradient-to-br from-primary to-primary/80 rounded-full flex items-center justify-center text-primary-foreground text-2xl font-bold group-hover:scale-110 transition-transform">
                                3
                            </div>
                            <div class="absolute -inset-2 rounded-full border-2 border-primary/30 animate-ping animation-delay-2000"></div>
                        </div>
                        <h3 class="text-2xl font-bold text-foreground mb-4">Analyze & Optimize</h3>
                        <p class="text-muted-foreground leading-relaxed">Get actionable insights and recommendations to continuously improve your event performance.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section class="py-24 bg-gradient-to-b from-muted to-background">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-20">
                    <h2 class="text-5xl font-bold text-foreground mb-6">Loved by event professionals worldwide</h2>
                    <p class="text-xl text-muted-foreground">See how industry leaders are transforming their events</p>
                </div>

                <div class="grid lg:grid-cols-3 gap-8">
                    <div class="bg-card/50 border border-border rounded-2xl p-8 hover:border-primary/50 transition-all duration-300">
                        <div class="mb-6">
                            <div class="flex text-primary mb-6">
                                ★★★★★
                            </div>
                            <p class="text-foreground text-lg leading-relaxed mb-6">"Suptix revolutionized how we understand our audience. The AI insights helped us increase engagement by 340% and revenue by 180%."</p>
                        </div>
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-br from-primary to-primary/80 rounded-full flex items-center justify-center text-primary-foreground font-bold mr-4">
                                S
                            </div>
                            <div>
                                <div class="font-bold text-foreground">Sarah Chen</div>
                                <div class="text-sm text-muted-foreground">Head of Events, TechCorp Global</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-card/50 border border-border rounded-2xl p-8 hover:border-primary/50 transition-all duration-300">
                        <div class="mb-6">
                            <div class="flex text-primary mb-6">
                                ★★★★★
                            </div>
                            <p class="text-foreground text-lg leading-relaxed mb-6">"The platform's intelligence is incredible. It predicted our peak registration times and helped us optimize our marketing spend perfectly."</p>
                        </div>
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-br from-primary to-primary/80 rounded-full flex items-center justify-center text-primary-foreground font-bold mr-4">
                                M
                            </div>
                            <div>
                                <div class="font-bold text-foreground">Marcus Rodriguez</div>
                                <div class="text-sm text-muted-foreground">Event Director, Innovation Summit</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-card/50 border border-border rounded-2xl p-8 hover:border-primary/50 transition-all duration-300">
                        <div class="mb-6">
                            <div class="flex text-primary mb-6">
                                ★★★★★
                            </div>
                            <p class="text-foreground text-lg leading-relaxed mb-6">"From complex conferences to intimate workshops, Suptix scales beautifully. The insights dashboard is pure gold for strategic planning."</p>
                        </div>
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-br from-primary to-primary/80 rounded-full flex items-center justify-center text-primary-foreground font-bold mr-4">
                                A
                            </div>
                            <div>
                                <div class="font-bold text-foreground">Aria Patel</div>
                                <div class="text-sm text-muted-foreground">Founder, EventGenius Agency</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section class="py-24 bg-background border-t border-border" id="pricing">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-20">
                    <h2 class="text-5xl font-bold text-foreground mb-6">Intelligent pricing for every scale</h2>
                    <p class="text-xl text-muted-foreground">Start free, scale as you grow</p>
                </div>

                <div class="bg-gradient-to-br from-card to-muted border border-border rounded-3xl overflow-hidden">
                    <div class="px-12 py-16">
                        <div class="text-center mb-12">
                            <div class="inline-flex items-center bg-primary/20 border border-primary/30 rounded-full px-4 py-2 mb-6">
                                <span class="text-primary text-sm font-semibold">✨ MOST POPULAR</span>
                            </div>
                            <h3 class="text-3xl font-bold text-foreground mb-4">Intelligence Pro</h3>
                            <div class="text-6xl font-bold text-foreground mb-4">
                                <span class="text-2xl text-muted-foreground">GH₵</span>0
                                <span class="text-2xl text-muted-foreground">/month</span>
                            </div>
                            <p class="text-muted-foreground text-lg">Start with powerful event intelligence</p>
                        </div>

                        <div class="grid md:grid-cols-2 gap-8 mb-12">
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <CheckCircle class="h-6 w-6 text-primary mr-3 flex-shrink-0" />
                                    <span class="text-foreground">Unlimited events & attendees</span>
                                </div>
                                <div class="flex items-center">
                                    <CheckCircle class="h-6 w-6 text-primary mr-3 flex-shrink-0" />
                                    <span class="text-foreground">AI-powered analytics dashboard</span>
                                </div>
                                <div class="flex items-center">
                                    <CheckCircle class="h-6 w-6 text-primary mr-3 flex-shrink-0" />
                                    <span class="text-foreground">Real-time performance insights</span>
                                </div>
                                <div class="flex items-center">
                                    <CheckCircle class="h-6 w-6 text-primary mr-3 flex-shrink-0" />
                                    <span class="text-foreground">Automated marketing optimization</span>
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
                                    <span class="text-foreground">Custom integrations available</span>
                                </div>
                                <div class="flex items-center">
                                    <CheckCircle class="h-6 w-6 text-primary mr-3 flex-shrink-0" />
                                    <span class="text-foreground">White-label options</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button @click="scrollToSection('get-started')"
                                class="w-full bg-gradient-to-r from-primary to-primary/90 hover:from-primary/90 hover:to-primary text-primary-foreground py-4 px-8 rounded-2xl text-xl font-bold transition-all duration-300 transform hover:scale-105 shadow-xl">
                                Start Your Intelligence Journey
                            </button>
                            <p class="text-sm text-muted-foreground mt-6">30-day free trial • No credit card required • 7% platform fee after trial</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action Section -->
        <section class="py-24 bg-gradient-to-br from-primary to-primary/90" id="get-started">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-5xl font-bold text-primary-foreground mb-8">Ready to unlock your event intelligence?</h2>
                <p class="text-xl text-primary-foreground/90 mb-12 max-w-2xl mx-auto leading-relaxed">
                    Join thousands of event professionals who've transformed their events with data-driven insights and AI-powered optimization.
                </p>
                <div class="flex flex-col sm:flex-row gap-6 justify-center">
                    <a :href="route('register')"
                        class="bg-background text-primary px-10 py-5 rounded-2xl text-xl font-bold hover:bg-muted transition-all duration-300 transform hover:scale-105 shadow-xl">
                        Start Free Trial
                    </a>
                    <button @click="scrollToSection('demo-section')"
                        class="border-2 border-primary-foreground text-primary-foreground hover:bg-primary-foreground hover:text-primary px-10 py-5 rounded-2xl text-xl font-bold transition-all duration-300">
                        Schedule Demo
                    </button>
                </div>
            </div>
        </section>

        <!-- Demo Request Section -->
        <section class="py-24 bg-background" id="demo-section">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-5xl font-bold text-foreground mb-6">See the intelligence in action</h2>
                    <p class="text-xl text-muted-foreground">Get a personalized demo of how Suptix can transform your events</p>
                </div>

                <div class="bg-gradient-to-br from-card to-muted border border-border rounded-3xl p-12">
                    <form @submit.prevent="submitDemoRequest" class="space-y-8">
                        <div class="grid md:grid-cols-2 gap-8">
                            <div>
                                <label for="firstName" class="block text-sm font-semibold text-foreground mb-3">
                                    First Name *
                                </label>
                                <input
                                    v-model="demoForm.firstName"
                                    type="text"
                                    id="firstName"
                                    required
                                    class="w-full px-5 py-4 bg-input border border-border rounded-xl text-foreground placeholder-muted-foreground focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                    placeholder="John"
                                />
                            </div>
                            <div>
                                <label for="lastName" class="block text-sm font-semibold text-foreground mb-3">
                                    Last Name *
                                </label>
                                <input
                                    v-model="demoForm.lastName"
                                    type="text"
                                    id="lastName"
                                    required
                                    class="w-full px-5 py-4 bg-input border border-border rounded-xl text-foreground placeholder-muted-foreground focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                    placeholder="Doe"
                                />
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-semibold text-foreground mb-3">
                                Work Email *
                            </label>
                            <input
                                v-model="demoForm.email"
                                type="email"
                                id="email"
                                required
                                class="w-full px-5 py-4 bg-input border border-border rounded-xl text-foreground placeholder-muted-foreground focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                placeholder="john.doe@company.com"
                            />
                        </div>

                        <div>
                            <label for="company" class="block text-sm font-semibold text-foreground mb-3">
                                Organization Name *
                            </label>
                            <input
                                v-model="demoForm.company"
                                type="text"
                                id="company"
                                required
                                class="w-full px-5 py-4 bg-input border border-border rounded-xl text-foreground placeholder-muted-foreground focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                placeholder="Your Organization"
                            />
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-semibold text-foreground mb-3">
                                Tell us about your event intelligence needs
                            </label>
                            <textarea
                                v-model="demoForm.message"
                                id="message"
                                rows="4"
                                class="w-full px-5 py-4 bg-input border border-border rounded-xl text-foreground placeholder-muted-foreground focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                placeholder="What challenges are you facing with event management? What insights are you looking for?"
                            ></textarea>
                        </div>

                        <!-- Honeypot field -->
                        <div style="position: absolute; left: -9999px; opacity: 0; pointer-events: none;">
                            <input
                                v-model="demoForm.honeypot"
                                type="text"
                                name="website"
                                tabindex="-1"
                                autocomplete="off"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="isDemoSubmitting"
                            class="w-full bg-gradient-to-r from-primary to-primary/90 hover:from-primary/90 hover:to-primary text-primary-foreground py-5 px-8 rounded-2xl text-xl font-bold transition-all duration-300 transform hover:scale-105 shadow-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                        >
                            <span v-if="isDemoSubmitting" class="flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-3 h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Scheduling Your Demo...
                            </span>
                            <span v-else>Get My Personalized Demo</span>
                        </button>
                    </form>

                    <!-- Success Message -->
                    <div v-if="showDemoSuccess" class="mt-8 p-8 bg-gradient-to-r from-green-500/20 to-green-600/20 border border-green-500/30 rounded-2xl">
                        <div class="flex items-start">
                            <CheckCircle class="h-8 w-8 text-green-500 mt-1 mr-4 flex-shrink-0" />
                            <div>
                                <h3 class="text-xl font-bold text-green-400 mb-3">Demo Request Received!</h3>
                                <p class="text-green-300 leading-relaxed">
                                    Thank you for your interest in Suptix Intelligence! Our team will contact you within 24 hours to schedule your personalized demonstration and show you how to unlock your event potential.
                                </p>
                            </div>
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
import SeoHead from '@/components/SeoHead.vue';
import Footer from '@/components/Footer.vue';
import { router } from '@inertiajs/vue3';
import { BarChart3, Calendar, CheckCircle, ChevronDown, Globe, Shield, Users, Zap } from 'lucide-vue-next';
import { ref } from 'vue';

interface Stats {
    totalEvents: number;
    upcomingEvents: number;
    totalTicketsSold: number;
    happyCustomers: number;
}

defineProps<{
    stats: Stats;
    seo: any;
}>();

const pageContainer = ref<HTMLElement>();

const demoForm = ref({
    firstName: '',
    lastName: '',
    email: '',
    company: '',
    message: '',
    honeypot: ''
});

const isDemoSubmitting = ref(false);
const showDemoSuccess = ref(false);

const submitDemoRequest = () => {
    isDemoSubmitting.value = true;

    router.post('/api/demo-request', demoForm.value, {
        preserveState: true,
        onSuccess: () => {
            showDemoSuccess.value = true;
            demoForm.value = {
                firstName: '',
                lastName: '',
                email: '',
                company: '',
                message: '',
                honeypot: ''
            };
            isDemoSubmitting.value = false;
        },
        onError: (errors) => {
            console.error('Demo form submission error:', errors);
            isDemoSubmitting.value = false;
        }
    });
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
