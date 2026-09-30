<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use App\Services\SeoService;
use Inertia\Inertia;

class HomeController extends Controller
{
    /**
     * Display the landing page
     */
    public function index()
    {
        // Get 3 upcoming events for the landing page
        $upcomingEvents = Event::query()
            ->with(['organizer'])
            ->whereIn('status', ['published', 'live'])
            // ->where('start_date', '>=', now()->toDateString())
            ->orderBy('start_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->limit(3)
            ->get()
            ->toArray();

        // Get event statistics
        $totalEvents = Event::count();
        $upcomingEventsCount = Event::where('status', 'published')
            ->where('start_date', '>=', now()->toDateString())
            ->count();

        $ticketsCount = Ticket::count();

        // $orderCount = Order::count();
        $userCount = User::count();

        // Set home page SEO using SEOTools
        SeoService::setHomeSEO();

        return Inertia::render('Home', [
            'upcomingEvents' => $upcomingEvents,
            'stats' => [
                'totalEvents' => $totalEvents,
                'upcomingEvents' => $upcomingEventsCount,
                'totalTicketsSold' => $ticketsCount,
                'happyCustomers' => (int) $userCount + (int) $ticketsCount, // add total
            ],
            'seo' => SeoService::homePage(),
        ]);
    }
}
