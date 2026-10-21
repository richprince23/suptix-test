<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Order;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\TwitterCard;
use Artesaos\SEOTools\Facades\JsonLd;

class SeoService
{
    /**
     * Generate SEO meta tags for the home page
     */
    public static function homePage(): array
    {
        return [
            'title' => 'Suptix - Discover Amazing Events Near You',
            'description' => 'Join thousands of event-goers who trust Suptix for seamless ticket booking. From concerts to conferences, discover and book tickets for amazing events near you.',
            'keywords' => 'event tickets, event booking, concerts, conferences, entertainment, ticket platform, event management, Ghana events, online ticketing',
            'og_title' => 'Suptix - Your Trusted Event Ticketing Platform',
            'og_description' => 'Discover and book tickets for amazing events near you. Lightning fast, secure, and mobile-friendly event ticketing.',
            'og_image' => asset('suptix-og-home.jpg'),
            'og_type' => 'website',
            'twitter_card' => 'summary_large_image',
            'twitter_title' => 'Suptix - Discover Amazing Events Near You',
            'twitter_description' => 'Lightning fast, secure event ticketing. Book tickets for concerts, conferences and more.',
            'twitter_image' => asset('suptix-twitter-home.jpg'),
            'canonical' => route('home'),
            'structured_data' => [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => 'Suptix',
                'description' => 'Event ticketing and management platform',
                'url' => route('home'),
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => route('events.index').'?search={search_term_string}',
                    'query-input' => 'required name=search_term_string',
                ],
            ],
        ];
    }

    /**
     * Generate SEO meta tags for events listing page
     */
    public static function eventsIndex(int $totalEvents = 0): array
    {
        return [
            'title' => "Browse All Events | {$totalEvents}+ Events Available",
            'description' => "Discover {$totalEvents}+ exciting events near you. Browse concerts, conferences, workshops, and more. Book your tickets securely with Suptix.",
            'keywords' => 'browse events, event listings, upcoming events, concerts, conferences, workshops, entertainment, ticket booking',
            'og_title' => 'Browse All Events',
            'og_description' => "Discover {$totalEvents}+ exciting events near you. From concerts to conferences, find your perfect event.",
            'og_image' => asset('suptix-default.jpg'),
            'og_type' => 'website',
            'twitter_card' => 'summary_large_image',
            'twitter_title' => 'Browse All Events',
            'twitter_description' => "Discover {$totalEvents}+ exciting events. Book tickets securely and instantly.",
            'twitter_image' => asset('suptix-twitter-home.jpg'),
            'canonical' => route('events.index'),
            'structured_data' => [
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => 'Events',
                'description' => 'Browse all available events on Suptix',
                'url' => route('events.index'),
                'mainEntity' => [
                    '@type' => 'ItemList',
                    'numberOfItems' => $totalEvents,
                ],
            ],
        ];
    }

    /**
     * Generate SEO meta tags for individual event page
     */
    public static function eventShow(Event $event): array
    {
        $eventDate = \Carbon\Carbon::parse($event->start_date)->format('F j, Y');
        $venue = $event->venue_name ?? 'TBA';
        $organizer = $event->organizer?->name ?? 'Unknown';

        $title = $event->title; // Event name as the default title for better SEO
        $description = $event->description
            ? substr(strip_tags($event->description), 0, 155).'...'
            : "Join us for {$event->title} on {$eventDate} at {$venue}. Book your tickets now on Suptix.";

        // Use event cover image, or fallback to a default Suptix image
        $ogImage = $event->cover_image_url ?? asset('suptix-default.jpg');

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => "event tickets, {$event->title}, {$venue}, {$eventDate}, {$organizer}, ticket booking",
            'og_title' => $event->title,
            'og_description' => $description,
            'og_image' => $ogImage,
            'og_type' => 'event',
            'twitter_card' => 'summary_large_image',
            'twitter_title' => $event->title,
            'twitter_description' => $description,
            'twitter_image' => $ogImage,
            'canonical' => route('events.show', $event->slug ?? $event->id),
            'structured_data' => [
                '@context' => 'https://schema.org',
                '@type' => 'Event',
                'name' => $event->title,
                'description' => $event->description,
                'startDate' => $event->start_date instanceof \Carbon\Carbon
                    ? $event->start_date->toIso8601String()
                    : \Carbon\Carbon::parse($event->start_date)->toIso8601String(),
                'endDate' => $event->end_date instanceof \Carbon\Carbon
                    ? $event->end_date->toIso8601String()
                    : ($event->end_date ? \Carbon\Carbon::parse($event->end_date)->toIso8601String() : null),
                'location' => [
                    '@type' => 'Place',
                    'name' => $venue,
                    'address' => $event->venue_address ?? $venue,
                ],
                'organizer' => [
                    '@type' => 'Organization',
                    'name' => $organizer,
                ],
                'image' => $ogImage,
                'url' => route('events.show', $event->slug ?? $event->id),
                'offers' => $event->ticketTypes->map(fn ($type) => [
                    '@type' => 'Offer',
                    'name' => $type->name,
                    'price' => $type->price,
                    'priceCurrency' => 'GHS',
                    'availability' => $type->available_quantity > 0 ? 'InStock' : 'OutOfStock',
                    'url' => route('events.show', $event->slug ?? $event->id),
                ])->toArray(),
            ],
        ];
    }

    /**
     * Generate SEO meta tags for calendar page
     */
    public static function calendar(): array
    {
        return [
            'title' => 'Event Calendar | View Events by Date | Suptix',
            'description' => 'Browse events by date with our interactive calendar view. Find events happening today, this week, or plan ahead for future events.',
            'keywords' => 'event calendar, events by date, event schedule, upcoming events, event planning',
            'og_title' => 'Event Calendar',
            'og_description' => 'Browse events by date with our interactive calendar view. Find your perfect event.',
            'og_image' => asset('suptix-default.jpg'),
            'og_type' => 'website',
            'canonical' => route('calendar'),
        ];
    }

    /**
     * Generate SEO meta tags for order search page
     */
    public static function orderSearch(): array
    {
        return [
            'title' => 'Find Your Order | Order Search | Suptix',
            'description' => 'Find your order and access your tickets. Enter your order number to view order details, download tickets, and manage your event bookings.',
            'keywords' => 'order search, find order, ticket access, order lookup, ticket download, order management',
            'og_title' => 'Find Your Order',
            'og_description' => 'Enter your order number to access your tickets and order details.',
            'og_image' => asset('suptix-default.jpg'),
            'og_type' => 'website',
            'canonical' => route('orders.search.page'),
            'robots' => 'noindex,nofollow', // Private page
        ];
    }

    /**
     * Generate SEO meta tags for order details page
     */
    public static function orderDetails(Order $order): array
    {
        $eventTitle = $order->event->title ?? 'Event';
        $ticketCount = $order->tickets->count();

        return [
            'title' => "Order #{$order->order_number} - {$eventTitle} | Suptix",
            'description' => "Order details for {$eventTitle}. {$ticketCount} ticket(s) purchased. Access your tickets and order information.",
            'keywords' => "order details, ticket access, {$eventTitle}, order {$order->order_number}",
            'og_title' => "Order #{$order->order_number} - {$eventTitle}",
            'og_description' => "Order confirmation for {$eventTitle}. {$ticketCount} ticket(s) purchased.",
            'og_image' => $order->event->cover_image_url ?? asset('suptix-default.jpg'),
            'og_type' => 'article',
            'canonical' => route('orders.show', $order->order_number),
            'robots' => 'noindex,nofollow', // Private page
        ];
    }

    /**
     * Generate SEO meta tags for payment success page
     */
    public static function paymentSuccess(Order $order): array
    {
        $eventTitle = $order->event->title ?? 'Event';

        return [
            'title' => "Payment Successful - {$eventTitle} | Suptix",
            'description' => "Your payment was successful! Your tickets for {$eventTitle} have been confirmed. Check your email for ticket details.",
            'keywords' => "payment success, ticket confirmation, {$eventTitle}, successful booking",
            'og_title' => "Payment Successful - {$eventTitle}",
            'og_description' => "Your tickets for {$eventTitle} have been confirmed. Thank you for your purchase!",
            'canonical' => route('payment.success', $order->order_number),
            'robots' => 'noindex,nofollow', // Private page
        ];
    }

    /**
     * Generate SEO meta tags for payment failed page
     */
    public static function paymentFailed(): array
    {
        return [
            'title' => 'Payment Failed | Suptix',
            'description' => 'Your payment could not be processed. Please try again or contact support for assistance.',
            'keywords' => 'payment failed, payment error, booking failed, payment support',
            'og_title' => 'Payment Failed',
            'og_description' => 'Your payment could not be processed. Please try again.',
            'canonical' => route('payment.failed'),
            'robots' => 'noindex,nofollow', // Private page
        ];
    }

    /**
     * Generate SEO meta tags for ticket verification page
     */
    public static function ticketVerification(string $ticketNumber): array
    {
        return [
            'title' => "Verify Ticket #{$ticketNumber} | Suptix",
            'description' => "Ticket verification for ticket #{$ticketNumber}. Scan QR code to verify ticket authenticity and check-in status.",
            'keywords' => 'ticket verification, QR code scan, ticket check-in, ticket validation',
            'canonical' => route('tickets.verify', $ticketNumber),
            'robots' => 'noindex,nofollow', // Private page
        ];
    }

    /**
     * Get default SEO meta tags for fallback
     */
    public static function default(string $title = 'Suptix'): array
    {
        return [
            'title' => $title.' | Suptix',
            'description' => 'Suptix - Your trusted platform for event ticketing and management. Discover, book, and manage event tickets with ease.',
            'keywords' => 'event tickets, event booking, ticket platform, event management',
            'og_title' => $title.'',
            'og_description' => 'Your trusted platform for event ticketing and management.',
            'og_image' => asset('suptix-default.jpg'),
            'og_type' => 'website',
            'twitter_card' => 'summary',
            'canonical' => url()->current(),
        ];
    }

    /**
     * Format structured data for JSON-LD
     */
    public static function formatStructuredData(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Set SEO using SEOTools for event pages
     */
    public static function setEventSEO(Event $event): void
    {
        $seoData = self::eventShow($event);

        SEOMeta::setTitle($seoData['title'])
            ->setDescription($seoData['description'])
            ->addKeyword(explode(', ', $seoData['keywords']))
            ->setCanonical($seoData['canonical']);

        OpenGraph::setTitle($seoData['og_title'])
            ->setDescription($seoData['og_description'])
            ->setUrl($seoData['canonical'])
            ->setType($seoData['og_type'])
            ->addImage($seoData['og_image']);

        TwitterCard::setTitle($seoData['twitter_title'])
            ->setDescription($seoData['twitter_description'])
            ->setType($seoData['twitter_card'])
            ->setImage($seoData['twitter_image']);

        JsonLd::addValues($seoData['structured_data']);
    }

    /**
     * Set SEO using SEOTools for home page
     */
    public static function setHomeSEO(): void
    {
        $seoData = self::homePage();

        SEOMeta::setTitle($seoData['title'])
            ->setDescription($seoData['description'])
            ->addKeyword(explode(', ', $seoData['keywords']))
            ->setCanonical($seoData['canonical']);

        OpenGraph::setTitle($seoData['og_title'])
            ->setDescription($seoData['og_description'])
            ->setUrl($seoData['canonical'])
            ->setType($seoData['og_type'])
            ->addImage($seoData['og_image']);

        TwitterCard::setTitle($seoData['twitter_title'])
            ->setDescription($seoData['twitter_description'])
            ->setType($seoData['twitter_card'])
            ->setImage($seoData['twitter_image']);

        JsonLd::addValues($seoData['structured_data']);
    }

    /**
     * Set SEO using SEOTools for events index page
     */
    public static function setEventsIndexSEO(int $totalEvents = 0): void
    {
        $seoData = self::eventsIndex($totalEvents);

        SEOMeta::setTitle($seoData['title'])
            ->setDescription($seoData['description'])
            ->addKeyword(explode(', ', $seoData['keywords']))
            ->setCanonical($seoData['canonical']);

        OpenGraph::setTitle($seoData['og_title'])
            ->setDescription($seoData['og_description'])
            ->setUrl($seoData['canonical'])
            ->setType($seoData['og_type'])
            ->addImage($seoData['og_image']);

        TwitterCard::setTitle($seoData['twitter_title'])
            ->setDescription($seoData['twitter_description'])
            ->setType($seoData['twitter_card'])
            ->setImage($seoData['twitter_image']);

        JsonLd::addValues($seoData['structured_data']);
    }

    /**
     * Set SEO using SEOTools for order search page
     */
    public static function setOrderSearchSEO(): void
    {
        $seoData = self::orderSearch();

        SEOMeta::setTitle($seoData['title'])
            ->setDescription($seoData['description'])
            ->addKeyword(explode(', ', $seoData['keywords']))
            ->setCanonical($seoData['canonical'])
            ->addMeta('robots', $seoData['robots'] ?? 'noindex,nofollow');

        OpenGraph::setTitle($seoData['og_title'])
            ->setDescription($seoData['og_description'])
            ->setUrl($seoData['canonical'])
            ->setType($seoData['og_type'])
            ->addImage($seoData['og_image']);
    }

    /**
     * Set SEO using SEOTools for order details
     */
    public static function setOrderSEO(Order $order): void
    {
        $seoData = self::orderDetails($order);

        SEOMeta::setTitle($seoData['title'])
            ->setDescription($seoData['description'])
            ->addKeyword(explode(', ', $seoData['keywords']))
            ->setCanonical($seoData['canonical'])
            ->addMeta('robots', $seoData['robots'] ?? 'index,follow');

        OpenGraph::setTitle($seoData['og_title'])
            ->setDescription($seoData['og_description'])
            ->setUrl($seoData['canonical'])
            ->setType($seoData['og_type'])
            ->addImage($seoData['og_image']);
    }

    /**
     * Set SEO using SEOTools for calendar page
     */
    public static function setCalendarSEO(): void
    {
        $seoData = self::calendar();

        SEOMeta::setTitle($seoData['title'])
            ->setDescription($seoData['description'])
            ->addKeyword(explode(', ', $seoData['keywords']))
            ->setCanonical($seoData['canonical']);

        OpenGraph::setTitle($seoData['og_title'])
            ->setDescription($seoData['og_description'])
            ->setUrl($seoData['canonical'])
            ->setType($seoData['og_type']);

        TwitterCard::setTitle($seoData['og_title'])
            ->setDescription($seoData['og_description']);

        JsonLd::addValues([
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => 'Event Calendar',
            'description' => $seoData['description'],
            'url' => $seoData['canonical'],
        ]);
    }

    /**
     * Generate SEO meta tags for Contact Us page
     */
    public static function contactUs(): array
    {
        return [
            'title' => 'Contact Us | Get Support & Help | Suptix',
            'description' => 'Get in touch with our support team. We\'re here to help with your event tickets, technical issues, or any questions about our platform.',
            'keywords' => 'contact support, customer service, help desk, ticket support, event help, contact suptix',
            'og_title' => 'Contact Us',
            'og_description' => 'Get in touch with our support team for help with tickets and events.',
            'og_image' => asset('suptix-default.jpg'),
            'og_type' => 'website',
            'canonical' => route('contact-us'),
            'structured_data' => [
                '@context' => 'https://schema.org',
                '@type' => 'ContactPage',
                'name' => 'Contact Us',
                'description' => 'Get in touch with Suptix support team',
                'url' => route('contact-us'),
            ],
        ];
    }

    /**
     * Generate SEO meta tags for Privacy Policy page
     */
    public static function privacyPolicy(): array
    {
        return [
            'title' => 'Privacy Policy | How We Protect Your Data | Suptix',
            'description' => 'Learn how Suptix collects, uses, and protects your personal information. Read our comprehensive privacy policy for event ticket booking.',
            'keywords' => 'privacy policy, data protection, personal information, privacy rights, data security, GDPR compliance',
            'og_title' => 'Privacy Policy',
            'og_description' => 'Learn how we protect your personal information and data privacy.',
            'og_image' => asset('suptix-default.jpg'),
            'og_type' => 'article',
            'canonical' => route('privacy-policy'),
            'structured_data' => [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => 'Privacy Policy',
                'description' => 'Suptix Privacy Policy - How we protect your data',
                'url' => route('privacy-policy'),
            ],
        ];
    }

    /**
     * Generate SEO meta tags for Terms of Service page
     */
    public static function termsOfService(): array
    {
        return [
            'title' => 'Terms of Service | User Agreement | Suptix',
            'description' => 'Read our terms of service and user agreement. Understand your rights and responsibilities when using Suptix for event tickets.',
            'keywords' => 'terms of service, user agreement, terms and conditions, legal agreement, service terms',
            'og_title' => 'Terms of Service',
            'og_description' => 'Read our terms of service and user agreement for using Suptix.',
            'og_image' => asset('suptix-default.jpg'),
            'og_type' => 'article',
            'canonical' => route('terms-of-service'),
            'structured_data' => [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => 'Terms of Service',
                'description' => 'Suptix Terms of Service and User Agreement',
                'url' => route('terms-of-service'),
            ],
        ];
    }

    /**
     * Generate SEO meta tags for Refund Policy page
     */
    public static function refundPolicy(): array
    {
        return [
            'title' => 'Refund Policy | Ticket Refunds & Cancellations | Suptix',
            'description' => 'Learn about our ticket refund policy, cancellation terms, and how to request refunds for event tickets purchased through Suptix.',
            'keywords' => 'refund policy, ticket refunds, cancellation policy, refund process, ticket cancellation, money back',
            'og_title' => 'Refund Policy',
            'og_description' => 'Learn about our ticket refund policy and cancellation terms.',
            'og_image' => asset('suptix-default.jpg'),
            'og_type' => 'article',
            'canonical' => route('refund-policy'),
            'structured_data' => [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => 'Refund Policy',
                'description' => 'Suptix Refund Policy for event tickets',
                'url' => route('refund-policy'),
            ],
        ];
    }

    /**
     * Generate SEO meta tags for Help Center page
     */
    public static function helpCenter(): array
    {
        return [
            'title' => 'Help Center | FAQs & Support | Suptix',
            'description' => 'Find answers to frequently asked questions about ticket booking, event management, payments, and more. Get help with your Suptix account.',
            'keywords' => 'help center, FAQ, frequently asked questions, support, help guides, ticket help, event help',
            'og_title' => 'Help Center',
            'og_description' => 'Find answers to frequently asked questions and get support.',
            'og_image' => asset('suptix-default.jpg'),
            'og_type' => 'website',
            'canonical' => route('help-center'),
            'structured_data' => [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'name' => 'Help Center',
                'description' => 'Frequently asked questions and support for Suptix',
                'url' => route('help-center'),
            ],
        ];
    }

    /**
     * Generate SEO meta tags for Request Demo page
     */
    public static function requestDemo(): array
    {
        return [
            'title' => 'Request Demo | See Suptix in Action | Event Management Platform',
            'description' => 'Request a personalized demo of Suptix event management platform. See how our ticketing solution can streamline your event organization.',
            'keywords' => 'request demo, platform demo, event management demo, ticketing platform, event organizer, demo request',
            'og_title' => 'Request Demo',
            'og_description' => 'Request a personalized demo of our event management platform.',
            'og_image' => asset('suptix-default.jpg'),
            'og_type' => 'website',
            'canonical' => route('request-demo'),
            'structured_data' => [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => 'Request Demo',
                'description' => 'Request a personalized demo of Suptix platform',
                'url' => route('request-demo'),
            ],
        ];
    }

    /**
     * Generate SEO meta tags for Attendee Check-in page
     */
    public static function attendeeCheckIn(): array
    {
        return [
            'title' => 'Attendee Check-in | Event Entry Management | Suptix',
            'description' => 'Streamline event check-in with QR code scanning. Fast and efficient attendee entry management for event organizers.',
            'keywords' => 'attendee check-in, QR code scanner, event entry, check-in app, event management, gate control',
            'og_title' => 'Attendee Check-in',
            'og_description' => 'Streamline event check-in with QR code scanning and entry management.',
            'og_image' => asset('suptix-default.jpg'),
            'og_type' => 'website',
            'canonical' => route('attendee-checkin'),
            'robots' => 'noindex,nofollow', // Private page for organizers
        ];
    }

    /**
     * Generate SEO meta tags for About page
     */
    public static function about(): array
    {
        return [
            'title' => 'About Suptix | Event Ticketing & Management Platform',
            'description' => 'Learn about Suptix - Ghana\'s leading event ticketing and management platform. Discover our mission to make event booking seamless and secure.',
            'keywords' => 'about suptix, event platform, ticketing platform, Ghana events, event management, about us',
            'og_title' => 'About Suptix - Event Ticketing Platform',
            'og_description' => 'Learn about Ghana\'s leading event ticketing and management platform.',
            'og_image' => asset('suptix-default.jpg'),
            'og_type' => 'website',
            'canonical' => url('/about'),
            'structured_data' => [
                '@context' => 'https://schema.org',
                '@type' => 'AboutPage',
                'name' => 'About Suptix',
                'description' => 'About Suptix - Ghana\'s leading event ticketing platform',
                'url' => url('/about'),
            ],
        ];
    }

    /**
     * Set SEO methods for static pages
     */
    public static function setContactUsSEO(): void
    {
        $seoData = self::contactUs();
        self::setSEOData($seoData);
    }

    public static function setPrivacyPolicySEO(): void
    {
        $seoData = self::privacyPolicy();
        self::setSEOData($seoData);
    }

    public static function setTermsOfServiceSEO(): void
    {
        $seoData = self::termsOfService();
        self::setSEOData($seoData);
    }

    public static function setRefundPolicySEO(): void
    {
        $seoData = self::refundPolicy();
        self::setSEOData($seoData);
    }

    public static function setHelpCenterSEO(): void
    {
        $seoData = self::helpCenter();
        self::setSEOData($seoData);
    }

    public static function setRequestDemoSEO(): void
    {
        $seoData = self::requestDemo();
        self::setSEOData($seoData);
    }

    public static function setAttendeeCheckInSEO(): void
    {
        $seoData = self::attendeeCheckIn();
        self::setSEOData($seoData);
    }

    public static function setAboutSEO(): void
    {
        $seoData = self::about();
        self::setSEOData($seoData);
    }

    /**
     * Helper method to set SEO data using SEOTools
     */
    private static function setSEOData(array $seoData): void
    {
        SEOMeta::setTitle($seoData['title'])
            ->setDescription($seoData['description'])
            ->addKeyword(explode(', ', $seoData['keywords']))
            ->setCanonical($seoData['canonical']);

        if (isset($seoData['robots'])) {
            SEOMeta::addMeta('robots', $seoData['robots']);
        }

        OpenGraph::setTitle($seoData['og_title'])
            ->setDescription($seoData['og_description'])
            ->setUrl($seoData['canonical'])
            ->setType($seoData['og_type'])
            ->addImage($seoData['og_image']);

        if (isset($seoData['twitter_card'])) {
            TwitterCard::setTitle($seoData['twitter_title'] ?? $seoData['og_title'])
                ->setDescription($seoData['twitter_description'] ?? $seoData['og_description'])
                ->setType($seoData['twitter_card'])
                ->setImage($seoData['twitter_image'] ?? $seoData['og_image']);
        }

        if (isset($seoData['structured_data'])) {
            JsonLd::addValues($seoData['structured_data']);
        }
    }
}
