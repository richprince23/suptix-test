# Custom Error Pages for Suptix

This directory contains custom error pages for common HTTP errors and exceptions. These pages provide a better user experience than Laravel's default error pages while maintaining the Suptix branding and design language.

## Available Error Pages

### HTTP Status Codes

- **401.blade.php** - Unauthorized: User needs to authenticate
- **403.blade.php** - Forbidden: User lacks permission to access resource
- **404.blade.php** - Not Found: Page or resource doesn't exist
- **419.blade.php** - CSRF Token Mismatch: Session expired or security token invalid
- **422.blade.php** - Unprocessable Entity: Validation errors in submitted data
- **429.blade.php** - Too Many Requests: Rate limiting exceeded
- **500.blade.php** - Internal Server Error: Server-side error occurred
- **503.blade.php** - Service Unavailable: Maintenance or temporary downtime

### Special Pages

- **generic.blade.php** - Fallback for any HTTP error not specifically handled
- **minimal.blade.php** - Minimal error page for when views might not be available
- **maintenance.blade.php** - Custom maintenance mode page (used with `php artisan down`)
- **layout.blade.php** - Base layout template for error pages

## Features

### Design
- Consistent with Suptix branding and color scheme
- Mobile-responsive design
- Dark mode support
- Professional gradient backgrounds
- Clear iconography and typography

### Functionality
- **Smart Navigation**: Context-aware action buttons (Home, Dashboard, Back)
- **Auto-retry**: Some errors include automatic retry mechanisms with countdown timers
- **Error Reporting**: Built-in "Report Issue" functionality via email
- **Progressive Enhancement**: JavaScript enhancements with graceful degradation

### Security & Privacy
- No sensitive information exposure
- Proper error logging without user data leakage
- CSRF protection maintained
- Rate limiting respect

## Configuration

Error pages are automatically registered in `bootstrap/app.php` with the following exception handlers:

```php
// HTTP exceptions
$exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
    // Returns custom error pages for web requests
    // Returns JSON for API requests
});

// CSRF token mismatch
$exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
    // Returns 419.blade.php for web requests
});

// Rate limiting
$exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
    // Returns 429.blade.php for web requests
});

// Authentication
$exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
    // Returns 401.blade.php for web requests
});
```

## Customization

### Adding New Error Pages
1. Create a new blade file in this directory (e.g., `418.blade.php`)
2. Extend the base layout: `@extends('errors::layout')`
3. Add the error code to the `$errorViews` array in `bootstrap/app.php`

### Modifying Existing Pages
- Update the blade templates directly
- The base layout provides consistent styling and functionality
- Use the `@section` directives to customize content

### Styling
- Main styles are in `layout.blade.php`
- Individual error pages can add specific styles using `@section('styles')`
- Responsive design breakpoints are already configured

## API Error Handling

For API requests (those expecting JSON), the system returns structured JSON responses:

```json
{
    "message": "Error description",
    "error": "error_code",
    "errors": {...} // For validation errors
}
```

## Maintenance Mode

To enable maintenance mode with the custom page:
```bash
php artisan down --render="errors.maintenance"
```

## Testing Error Pages

You can test error pages by:
1. Creating test routes that throw specific exceptions
2. Using Laravel's `abort()` helper: `abort(404, 'Custom message')`
3. Testing in different environments (web vs API)

## Performance Considerations

- Error pages are lightweight and load quickly
- Minimal dependencies (only essential CSS/JS)
- Images are SVG icons for fast rendering
- No external resource dependencies

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Progressive enhancement for older browsers
- Mobile-first responsive design
- Graceful degradation for JavaScript disabled users