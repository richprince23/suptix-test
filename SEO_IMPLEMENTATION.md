# SEO Implementation Guide

## Overview

Suptix now includes comprehensive SEO implementation using the [Artesaos SEOTools](https://github.com/artesaos/seotools) package. This provides automated meta tags, Open Graph, Twitter Cards, and structured data (JSON-LD) for better search engine optimization.

## Features Implemented

### ✅ Core SEO Features
- **Meta Tags**: Title, description, keywords, canonical URLs, robots directive
- **Open Graph**: Facebook and social media sharing optimization
- **Twitter Cards**: Enhanced Twitter sharing with large images
- **JSON-LD Structured Data**: Rich snippets for search engines
- **Inertia.js Integration**: Works seamlessly with Vue.js frontend
- **Sitemap**: Automated XML sitemap generation
- **Robots.txt**: Search engine crawling directives

### ✅ Page-Specific SEO
- **Event Pages**: Detailed event schema with venue, pricing, dates
- **Events Listing**: Collection page with total event count
- **Home Page**: Website schema with search functionality
- **Order Pages**: Private pages with noindex directive
- **Calendar**: Event browsing by date
- **Error Pages**: Proper meta tags for 404, 500, etc.

## Configuration

### SEOTools Config (`config/seotools.php`)
```php
// Configured for Suptix with:
- Default title: "Suptix"
- Description: Ghana's Premier Event Management and Ticketing Platform
- Keywords: events, tickets, ghana, event management, etc.
- Twitter handle: @SuptixGH
- Inertia integration: enabled
```

### Middleware Setup (`app/Http/Middleware/SetDefaultSEO.php`)
- Applied to all web routes
- Sets default meta tags for every page
- Gracefully handles console commands
- Provides fallback SEO when page-specific SEO isn't set

## Usage Examples

### In Controllers

```php
use App\Services\SeoService;

// Event page
public function show(Event $event)
{
    SeoService::setEventSEO($event);
    return Inertia::render('Events/Show', ['event' => $event]);
}

// Events listing
public function index()
{
    $events = Event::paginate(20);
    SeoService::setEventsIndexSEO($events->total());
    return Inertia::render('Events/Index', ['events' => $events]);
}

// Manual SEO setup
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;

public function customPage()
{
    SEOMeta::setTitle('Custom Page - Suptix')
        ->setDescription('Custom description')
        ->addKeyword(['custom', 'page']);
    
    OpenGraph::setTitle('Custom Page')
        ->setType('website');
        
    return Inertia::render('CustomPage');
}
```

### In Blade Templates

```blade
<head>
    {{-- SEO Tools generates all meta tags --}}
    {!! SEO::generate() !!}
    
    {{-- Inertia title (will override if provided) --}}
    <title inertia>@yield('title', SEO::getTitle())</title>
</head>
```

## Available SEO Methods

### SeoService Static Methods
- `SeoService::setHomeSEO()` - Home page SEO
- `SeoService::setEventSEO($event)` - Individual event SEO
- `SeoService::setEventsIndexSEO($count)` - Events listing SEO
- `SeoService::setOrderSEO($order)` - Order details SEO

### Direct Facade Usage
```php
// Meta tags
SEOMeta::setTitle('Page Title')
    ->setDescription('Page description')
    ->addKeyword(['keyword1', 'keyword2'])
    ->setCanonical($url)
    ->addMeta('robots', 'index,follow');

// Open Graph
OpenGraph::setTitle('OG Title')
    ->setDescription('OG Description')
    ->setType('website')
    ->setUrl($url)
    ->addImage($imageUrl);

// Twitter Cards
TwitterCard::setTitle('Twitter Title')
    ->setDescription('Twitter Description')
    ->setType('summary_large_image')
    ->setImage($imageUrl);

// JSON-LD Structured Data
JsonLd::addValues([
    '@context' => 'https://schema.org',
    '@type' => 'Event',
    'name' => 'Event Name',
    // ... more structured data
]);
```

## Structured Data Examples

### Event Schema (Automatically Generated)
```json
{
    "@context": "https://schema.org",
    "@type": "Event",
    "name": "Event Title",
    "description": "Event description",
    "startDate": "2024-01-15T19:00:00+00:00",
    "endDate": "2024-01-15T23:00:00+00:00",
    "location": {
        "@type": "Place",
        "name": "Venue Name",
        "address": "Full Address"
    },
    "organizer": {
        "@type": "Organization",
        "name": "Organizer Name"
    },
    "offers": [
        {
            "@type": "Offer",
            "name": "General Admission",
            "price": "50.00",
            "priceCurrency": "GHS",
            "availability": "https://schema.org/InStock"
        }
    ]
}
```

## SEO URLs

### Automated URLs
- **Sitemap**: `/sitemap.xml` - Lists all public pages and events
- **Robots**: `/robots.txt` - Search engine crawling instructions

### Meta Tag Examples
```html
<!-- Generated automatically -->
<title>Event Title - Suptix</title>
<meta name="description" content="Join us for Event Title on January 15, 2024...">
<meta name="keywords" content="event, ticket, venue name, concert">
<meta name="robots" content="index,follow">
<link rel="canonical" href="https://suptix.com/events/event-slug">

<!-- Open Graph -->
<meta property="og:title" content="Event Title">
<meta property="og:description" content="Event description...">
<meta property="og:type" content="event">
<meta property="og:url" content="https://suptix.com/events/event-slug">
<meta property="og:image" content="https://suptix.com/storage/events/image.jpg">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Event Title">
<meta name="twitter:description" content="Event description...">
```

## Best Practices

### Do's
- ✅ Use `SeoService` methods for consistent SEO
- ✅ Always set canonical URLs
- ✅ Include relevant keywords naturally
- ✅ Use proper structured data schemas
- ✅ Set appropriate robots directives
- ✅ Include high-quality images for social sharing

### Don'ts
- ❌ Don't stuff keywords unnaturally
- ❌ Don't duplicate meta descriptions
- ❌ Don't forget to set noindex for private pages
- ❌ Don't use the same title across multiple pages
- ❌ Don't neglect mobile optimization

## Testing SEO

### Validation Tools
- **Google Rich Results Test**: Test structured data
- **Facebook Sharing Debugger**: Test Open Graph tags
- **Twitter Card Validator**: Test Twitter cards
- **SEO Browser Extensions**: Check meta tags locally

### Local Testing
```bash
# Check sitemap
curl http://localhost:8000/sitemap.xml

# Check robots.txt
curl http://localhost:8000/robots.txt

# Inspect page source for meta tags
view-source:http://localhost:8000/events/some-event
```

## Performance Considerations

- SEO middleware runs on every web request but skips console commands
- Structured data is generated dynamically but cached by the browser
- Images for social sharing should be optimized (recommended: 1200x630px)
- Sitemap includes only published/live events to avoid 404s

## Future Enhancements

### Potential Additions
- [ ] Multi-language SEO support
- [ ] Automatic image optimization for social sharing
- [ ] SEO analytics integration
- [ ] Local business schema for venues
- [ ] FAQ schema for help pages
- [ ] Breadcrumb navigation schema