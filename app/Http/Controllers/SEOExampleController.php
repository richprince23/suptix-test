<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SEOExampleController extends Controller
{
    /**
     * Example of how to use SEO service in event show page
     */
    public function eventShow(Event $event)
    {
        // Set specific SEO for this event using SEOTools
        SeoService::setEventSEO($event);

        return Inertia::render('Events/Show', [
            'event' => $event->load(['organizer', 'ticketTypes']),
            // No need to pass SEO data to frontend as SEOTools handles it
        ]);
    }

    /**
     * Example of how to use SEO service in events index page
     */
    public function eventsIndex(Request $request)
    {
        $events = Event::whereIn('status', ['published', 'live'])
            ->with('organizer')
            ->paginate(20);

        // Set SEO for events listing page
        SeoService::setEventsIndexSEO($events->total());

        return Inertia::render('Events/Index', [
            'events' => $events,
            // No need to pass SEO data to frontend as SEOTools handles it
        ]);
    }

    /**
     * Example of how to use SEO service in home page
     */
    public function home()
    {
        // Set SEO for home page
        SeoService::setHomeSEO();

        $featuredEvents = Event::whereIn('status', ['published', 'live'])
            ->where('is_featured', true)
            ->with('organizer')
            ->take(6)
            ->get();

        return Inertia::render('Home', [
            'featuredEvents' => $featuredEvents,
            // No need to pass SEO data to frontend as SEOTools handles it
        ]);
    }

    /**
     * Example of setting custom SEO manually
     */
    public function customSEO()
    {
        // You can also set SEO manually using the facades directly
        \SEOMeta::setTitle('Custom Page - Suptix')
            ->setDescription('This is a custom page with manually set SEO.')
            ->addKeyword(['custom', 'example', 'seo'])
            ->setCanonical(request()->url());

        \OpenGraph::setTitle('Custom Page')
            ->setDescription('This is a custom page with manually set SEO.')
            ->setType('website')
            ->setUrl(request()->url());

        \TwitterCard::setTitle('Custom Page')
            ->setDescription('This is a custom page with manually set SEO.')
            ->setType('summary');

        \JsonLd::addValues([
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => 'Custom Page',
            'description' => 'This is a custom page with manually set SEO.',
            'url' => request()->url(),
        ]);

        return Inertia::render('CustomPage');
    }
}