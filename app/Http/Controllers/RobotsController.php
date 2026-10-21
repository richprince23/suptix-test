<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Generate robots.txt for search engines
     */
    public function index(): Response
    {
        $robots = "User-agent: *\n";
        $robots .= "Allow: /\n";
        $robots .= "Disallow: /admin/\n";
        $robots .= "Disallow: /api/\n";
        $robots .= "Disallow: /payment/\n";
        $robots .= "Disallow: /order-search\n";
        $robots .= "Disallow: /orders/\n";
        $robots .= "\n";
        $robots .= 'Sitemap: '.url('/sitemap.xml')."\n";

        return response($robots, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }
}
