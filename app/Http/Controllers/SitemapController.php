<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate XML sitemap for search engines
     */
    public function index(): Response
    {
        $events = Event::whereIn('status', ['published', 'live'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        // Add homepage
        $xml .= $this->addUrl(route('home'), now(), 'daily', '1.0');

        // Add events index
        $xml .= $this->addUrl(route('events.index'), now(), 'daily', '0.9');

        // Add calendar
        $xml .= $this->addUrl(route('calendar'), now(), 'weekly', '0.8');

        // Add order search
        $xml .= $this->addUrl(route('orders.search.page'), now(), 'monthly', '0.5');

        // Add individual events
        foreach ($events as $event) {
            $xml .= $this->addUrl(
                route('events.show', $event->slug),
                $event->updated_at,
                'weekly',
                '0.8'
            );
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Add URL to sitemap XML
     */
    private function addUrl(string $url, $lastmod = null, string $changefreq = 'monthly', string $priority = '0.5'): string
    {
        $xml = "  <url>\n";
        $xml .= '    <loc>'.htmlspecialchars($url)."</loc>\n";

        if ($lastmod) {
            $xml .= '    <lastmod>'.$lastmod->toISOString()."</lastmod>\n";
        }

        $xml .= "    <changefreq>{$changefreq}</changefreq>\n";
        $xml .= "    <priority>{$priority}</priority>\n";
        $xml .= "  </url>\n";

        return $xml;
    }
}
