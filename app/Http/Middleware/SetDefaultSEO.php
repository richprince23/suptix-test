<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\TwitterCard;
use Artesaos\SEOTools\Facades\JsonLd;

class SetDefaultSEO
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip SEO setup during console commands
        if (app()->runningInConsole()) {
            return $next($request);
        }
        // Set default SEO meta tags for all pages
        try {
            SEOMeta::setTitle(config('app.name', 'Suptix'))
                ->setDescription('Ghana\'s Premier Event Management and Ticketing Platform. Discover, book, and organize amazing events across Ghana.')
                ->addKeyword(['events', 'tickets', 'ghana', 'event management', 'ticketing platform', 'concerts', 'conferences', 'entertainment'])
                ->setCanonical($request->url())
                ->addMeta('robots', 'index,follow');
        } catch (\Exception $e) {
            // Fail gracefully if URL generation fails during artisan commands
        }

        // Open Graph tags
        try {
            OpenGraph::setTitle(config('app.name', 'Suptix'))
                ->setDescription('Ghana\'s Premier Event Management and Ticketing Platform. Discover, book, and organize amazing events across Ghana.')
                ->setUrl($request->url())
                ->setType('website')
                ->setSiteName(config('app.name', 'Suptix'));

            // Twitter Card tags
            TwitterCard::setTitle(config('app.name', 'Suptix'))
                ->setSite('@SuptixGH')
                ->setDescription('Ghana\'s Premier Event Management and Ticketing Platform. Discover, book, and organize amazing events across Ghana.')
                ->setType('summary_large_image');

            // JSON-LD structured data
            JsonLd::setTitle(config('app.name', 'Suptix'))
                ->setDescription('Ghana\'s Premier Event Management and Ticketing Platform. Discover, book, and organize amazing events across Ghana.')
                ->setUrl($request->url())
                ->setType('WebSite');
        } catch (\Exception $e) {
            // Fail gracefully if URL generation fails during artisan commands
        }

        return $next($request);
    }
}