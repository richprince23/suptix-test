# Suptix API v2 Documentation

This document outlines the new v2 API implementation for the Suptix mobile application using Laravel Sanctum for authentication.

## Base URL
All API endpoints are prefixed with `/api/v2`

## Authentication
The API uses Laravel Sanctum for token-based authentication. Include the token in the `Authorization` header:
```
Authorization: Bearer {token}
```

## Response Format
All API responses follow a consistent format:

### Success Response
```json
{
    "status": "success",
    "message": "Operation successful",
    "data": {
        // Response data
    }
}
```

### Error Response
```json
{
    "status": "error",
    "message": "Error description",
    "errors": {
        // Validation errors (if applicable)
    }
}
```

## Security Features
- **Honeypot Protection**: All forms include honeypot fields to prevent bot submissions
- **Input Validation**: Comprehensive validation on all endpoints
- **Token Management**: Automatic token refresh and cleanup
- **Password Hashing**: Secure password storage using Laravel's hashing

## API Endpoints

### Authentication
- `POST /auth/register` - User registration
- `POST /auth/login` - User login
- `POST /auth/logout` - User logout (authenticated)
- `POST /auth/refresh` - Token refresh (authenticated)
- `DELETE /auth/account` - Account deletion (authenticated)

### Events (Public)
- `GET /events` - List all published events
- `GET /events/{event}` - Get event details
- `GET /events/{event}/tickets` - Get event ticket types

### User Profile (Authenticated)
- `GET /user/profile` - Get user profile
- `PUT /user/profile` - Update user profile
- `GET /user/orders` - Get user orders
- `GET /user/tickets` - Get user tickets

### Orders (Authenticated)
- `GET /orders` - List user orders
- `GET /orders/{order}` - Get order details
- `POST /orders` - Create new order

### Tickets (Authenticated)
- `GET /tickets` - List user tickets
- `GET /tickets/{ticket}` - Get ticket details
- `POST /tickets/scan` - Scan QR code
- `POST /tickets/verify` - Verify ticket by number
- `PUT /tickets/{ticket}/check-in` - Check-in ticket

### Organizer Features (Authenticated)
- `GET /organizer/profile` - Get organizer profile
- `GET /organizer/events` - List organizer events
- `GET /organizer/events/{event}` - Get event details
- `GET /organizer/events/{event}/statistics` - Get event statistics
- `GET /organizer/events/{event}/tickets` - List event tickets
- `GET /organizer/events/{event}/orders` - List event orders
- `PUT /organizer/events/{event}/tickets/{ticket}/check-in` - Check-in ticket

## Key Features

### Mobile-Optimized
- Paginated responses for large datasets
- Optimized data structures for mobile consumption
- Efficient image handling with proper URLs

### Event Management
- Filter events by type, location, organizer
- Search functionality across events
- Real-time availability checking

### Ticket Handling
- QR code generation and scanning
- Ticket verification and validation
- Check-in management
- Status tracking (valid, used, cancelled)

### Order Processing
- Multi-ticket order creation
- Order status tracking
- Payment integration ready

### Organizer Tools
- Event statistics and analytics
- Attendee management
- Real-time check-in tracking
- Revenue reporting

## Data Models

### User
- Basic profile information
- Authentication credentials
- Organizer associations

### Event
- Event details and metadata
- Venue information
- Ticket types and pricing
- Status and availability

### Ticket
- Individual ticket instances
- QR code data
- Usage tracking
- Check-in history

### Order
- Purchase information
- Buyer details
- Payment status
- Associated tickets

## Error Handling
- Consistent error messages
- Proper HTTP status codes
- Validation error details
- Friendly user messages

## Testing
Comprehensive test suite covering:
- Authentication flows
- Honeypot protection
- API endpoint functionality
- Data validation
- Error scenarios

## File Structure
```
app/Http/Controllers/Api/V2/
├── BaseController.php
├── AuthController.php
├── EventController.php
├── UserController.php
├── OrderController.php
├── TicketController.php
└── OrganizerController.php

app/Http/Resources/Api/V2/
├── UserResource.php
├── EventResource.php
├── EventDetailResource.php
├── OrganizerResource.php
├── VenueResource.php
├── TicketTypeResource.php
├── TicketResource.php
├── TicketDetailResource.php
├── OrderResource.php
└── OrderDetailResource.php

routes/
└── api_v2.php

tests/Feature/Api/V2/
└── AuthTest.php
```

## Next Steps
1. Implement payment integration endpoints
2. Add push notification support
3. Enhance search and filtering capabilities
4. Add rate limiting
5. Implement API versioning strategy
6. Add comprehensive API documentation
7. Set up monitoring and analytics