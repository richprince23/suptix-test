# Suptix Mobile API Documentation

## Overview

This documentation provides comprehensive guidance for developing a mobile application that integrates with the Suptix event management platform. Suptix is a Laravel-based system using the TALL stack with a robust API for mobile applications.

## Base Information

- **Base URL**: `{your-domain}/api/v2`
- **Authentication**: Bearer Token (Laravel Sanctum)
- **Content Type**: `application/json`
- **Rate Limiting**: Standard Laravel rate limiting applies

## Authentication Flow

### 1. User Registration

**Endpoint**: `POST /auth/register`

**Request Body**:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+233123456789",
  "password": "password123",
  "password_confirmation": "password123",
  "honeypot": ""
}
```

**Response** (201):
```json
{
  "status": "success",
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+233123456789"
    },
    "token": "1|abc123def456..."
  }
}
```

### 2. User Login

**Endpoint**: `POST /auth/login`

**Request Body**:
```json
{
  "email": "john@example.com",
  "password": "password123",
  "honeypot": ""
}
```

**Response** (200):
```json
{
  "status": "success",
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+233123456789"
    },
    "token": "1|abc123def456..."
  }
}
```

### 3. Token Refresh

**Endpoint**: `POST /auth/refresh`
**Headers**: `Authorization: Bearer {token}`

**Response** (200):
```json
{
  "status": "success",
  "message": "Token refreshed successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+233123456789"
    },
    "token": "1|new_token_here..."
  }
}
```

### 4. Logout

**Endpoint**: `POST /auth/logout`
**Headers**: `Authorization: Bearer {token}`

**Response** (200):
```json
{
  "status": "success",
  "message": "Logged out successfully",
  "data": null
}
```

### 5. Account Deletion

**Endpoint**: `DELETE /auth/account`
**Headers**: `Authorization: Bearer {token}`

**Request Body**:
```json
{
  "password": "current_password"
}
```

**Response** (200):
```json
{
  "status": "success",
  "message": "Account deleted successfully",
  "data": null
}
```

## Events API

### 1. Get Events List

**Endpoint**: `GET /events`

**Query Parameters**:
- `search` (string): Search in title, description, tags
- `type` (string): Filter by event type
- `organizer_id` (integer): Filter by organizer
- `location` (string): Filter by venue location
- `per_page` (integer): Results per page (default: 15)

**Example**: `GET /events?search=music&location=accra&per_page=10`

**Response** (200):
```json
{
  "status": "success",
  "message": "Events retrieved successfully",
  "data": {
    "events": [
      {
        "id": 1,
        "code": "EVT12345",
        "slug": "amazing-music-concert",
        "title": "Amazing Music Concert",
        "description": "A night of amazing music...",
        "cover_image_url": "https://example.com/storage/images/event1.jpg",
        "start_date": "2024-12-25",
        "start_time": "19:00:00",
        "end_date": "2024-12-25",
        "end_time": "23:00:00",
        "status": "published",
        "tags": ["music", "concert", "entertainment"],
        "mode": "in-person",
        "event_type": "concert",
        "organizer": {
          "id": 1,
          "name": "Music Events Ltd",
          "logo_url": "https://example.com/storage/logos/org1.jpg"
        },
        "venue": {
          "id": 1,
          "name": "National Theatre",
          "address": "123 Main Street",
          "city": "Accra",
          "state": "Greater Accra",
          "country": "Ghana"
        }
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 15,
      "total": 75,
      "has_more": true
    }
  }
}
```

### 2. Get Single Event

**Endpoint**: `GET /events/{event}`
**Note**: `{event}` can be ID, slug, or event code

**Response** (200):
```json
{
  "status": "success",
  "message": "Event details retrieved successfully",
  "data": {
    "id": 1,
    "code": "EVT12345",
    "slug": "amazing-music-concert",
    "title": "Amazing Music Concert",
    "description": "A detailed description of the event...",
    "cover_image_url": "https://example.com/storage/images/event1.jpg",
    "start_date": "2024-12-25",
    "start_time": "19:00:00",
    "end_date": "2024-12-25",
    "end_time": "23:00:00",
    "status": "published",
    "tags": ["music", "concert", "entertainment"],
    "mode": "in-person",
    "event_type": "concert",
    "is_rsvp": false,
    "platform_name": null,
    "online_link": null,
    "is_public_link": false,
    "organizer": {
      "id": 1,
      "name": "Music Events Ltd",
      "email": "info@musicevents.com",
      "phone": "+233123456789",
      "logo_url": "https://example.com/storage/logos/org1.jpg",
      "banner_url": "https://example.com/storage/banners/org1.jpg",
      "description": "Premier music event organizer",
      "website": "https://musicevents.com"
    },
    "venue": {
      "id": 1,
      "name": "National Theatre",
      "address": "123 Main Street",
      "city": "Accra",
      "state": "Greater Accra",
      "country": "Ghana"
    },
    "ticket_types": [
      {
        "id": 1,
        "name": "Regular",
        "description": "Standard admission ticket",
        "price": 50.00,
        "available_quantity": 150,
        "total_capacity": 200,
        "sold_tickets_count": 50,
        "sales_start": "2024-11-01T00:00:00Z",
        "sales_end": "2024-12-24T23:59:59Z"
      }
    ]
  }
}
```

### 3. Get Event Tickets

**Endpoint**: `GET /events/{event}/tickets`

**Response** (200):
```json
{
  "status": "success",
  "message": "Ticket types retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Regular",
      "description": "Standard admission ticket",
      "price": 50.00,
      "available_quantity": 150,
      "total_capacity": 200,
      "sold_tickets_count": 50,
      "sales_start": "2024-11-01T00:00:00Z",
      "sales_end": "2024-12-24T23:59:59Z"
    },
    {
      "id": 2,
      "name": "VIP",
      "description": "VIP access with premium seating",
      "price": 100.00,
      "available_quantity": 20,
      "total_capacity": 50,
      "sold_tickets_count": 30,
      "sales_start": "2024-11-01T00:00:00Z",
      "sales_end": "2024-12-24T23:59:59Z"
    }
  ]
}
```

## User Profile API

All user profile endpoints require authentication.

### 1. Get User Profile

**Endpoint**: `GET /user/profile`
**Headers**: `Authorization: Bearer {token}`

**Response** (200):
```json
{
  "status": "success",
  "message": "Profile retrieved successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+233123456789",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### 2. Update User Profile

**Endpoint**: `PUT /user/profile`
**Headers**: `Authorization: Bearer {token}`

**Request Body**:
```json
{
  "name": "John Updated",
  "phone": "+233987654321"
}
```

**Response** (200):
```json
{
  "status": "success",
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "John Updated",
    "email": "john@example.com",
    "phone": "+233987654321",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### 3. Get User Orders

**Endpoint**: `GET /user/orders`
**Headers**: `Authorization: Bearer {token}`

**Response** (200):
```json
{
  "status": "success",
  "message": "Orders retrieved successfully",
  "data": [
    {
      "id": 1,
      "order_number": "ORD-ABC123DEF4",
      "event": {
        "id": 1,
        "title": "Amazing Music Concert",
        "cover_image_url": "https://example.com/storage/images/event1.jpg"
      },
      "total_amount": 150.00,
      "status": "confirmed",
      "purchased_at": "2024-11-15T14:30:00Z",
      "total_tickets": 3
    }
  ]
}
```

### 4. Get User Tickets

**Endpoint**: `GET /user/tickets`
**Headers**: `Authorization: Bearer {token}`

**Response** (200):
```json
{
  "status": "success",
  "message": "Tickets retrieved successfully",
  "data": [
    {
      "id": 1,
      "ticket_number": "TKT-XYZ789",
      "qr_code": "https://example.com/qr/TKT-XYZ789",
      "status": "active",
      "event": {
        "id": 1,
        "title": "Amazing Music Concert",
        "start_date": "2024-12-25",
        "start_time": "19:00:00",
        "venue": {
          "name": "National Theatre",
          "address": "123 Main Street, Accra"
        }
      },
      "ticket_type": {
        "name": "Regular",
        "price": 50.00
      },
      "order": {
        "order_number": "ORD-ABC123DEF4"
      }
    }
  ]
}
```

## Order Management API

### 1. Get Orders

**Endpoint**: `GET /orders`
**Headers**: `Authorization: Bearer {token}`

**Response**: Same as user orders above

### 2. Get Single Order

**Endpoint**: `GET /orders/{order}`
**Headers**: `Authorization: Bearer {token}`

**Response** (200):
```json
{
  "status": "success",
  "message": "Order retrieved successfully",
  "data": {
    "id": 1,
    "order_number": "ORD-ABC123DEF4",
    "buyer_email": "john@example.com",
    "buyer_phone": "+233123456789",
    "subtotal_amount": 150.00,
    "discount_amount": 0.00,
    "total_amount": 150.00,
    "status": "confirmed",
    "payment_method": "paystack",
    "payment_reference": "PST_xyz123",
    "purchased_at": "2024-11-15T14:30:00Z",
    "event": {
      "id": 1,
      "title": "Amazing Music Concert",
      "cover_image_url": "https://example.com/storage/images/event1.jpg"
    },
    "tickets": [
      {
        "id": 1,
        "ticket_number": "TKT-XYZ789",
        "status": "active",
        "ticket_type": {
          "name": "Regular",
          "price": 50.00
        }
      }
    ]
  }
}
```

### 3. Create Order

**Endpoint**: `POST /orders`
**Headers**: `Authorization: Bearer {token}`

**Request Body**:
```json
{
  "event_id": 1,
  "buyer_email": "john@example.com",
  "buyer_phone": "+233123456789",
  "tickets": [
    {
      "ticket_type_id": 1,
      "quantity": 2
    },
    {
      "ticket_type_id": 2,
      "quantity": 1
    }
  ],
  "discount_code": "EARLYBIRD",
  "payment_method": "paystack"
}
```

**Response** (201):
```json
{
  "status": "success",
  "message": "Order created successfully",
  "data": {
    "id": 1,
    "order_number": "ORD-ABC123DEF4",
    "total_amount": 150.00,
    "status": "pending",
    "payment_url": "https://checkout.paystack.com/xyz123"
  }
}
```

## Ticket Management API

### 1. Get Tickets

**Endpoint**: `GET /tickets`
**Headers**: `Authorization: Bearer {token}`

**Response**: Same as user tickets above

### 2. Get Single Ticket

**Endpoint**: `GET /tickets/{ticket}`
**Headers**: `Authorization: Bearer {token}`

**Response** (200):
```json
{
  "status": "success",
  "message": "Ticket retrieved successfully",
  "data": {
    "id": 1,
    "ticket_number": "TKT-XYZ789",
    "qr_code": "https://example.com/qr/TKT-XYZ789",
    "status": "active",
    "used_at": null,
    "event": {
      "id": 1,
      "title": "Amazing Music Concert",
      "start_date": "2024-12-25",
      "start_time": "19:00:00",
      "end_date": "2024-12-25",
      "end_time": "23:00:00",
      "venue": {
        "name": "National Theatre",
        "address": "123 Main Street",
        "city": "Accra",
        "state": "Greater Accra"
      }
    },
    "ticket_type": {
      "name": "Regular",
      "description": "Standard admission ticket",
      "price": 50.00
    },
    "order": {
      "order_number": "ORD-ABC123DEF4",
      "buyer_email": "john@example.com"
    }
  }
}
```

### 3. Scan Ticket (QR Code)

**Endpoint**: `POST /tickets/scan`
**Headers**: `Authorization: Bearer {token}`

**Request Body**:
```json
{
  "qr_data": "TKT-XYZ789-QR-DATA",
  "event_id": 1
}
```

**Response** (200):
```json
{
  "status": "success",
  "message": "Ticket scanned successfully",
  "data": {
    "ticket": {
      "id": 1,
      "ticket_number": "TKT-XYZ789",
      "status": "active",
      "attendee_name": "John Doe",
      "ticket_type": {
        "name": "Regular"
      }
    },
    "valid": true,
    "can_check_in": true,
    "already_used": false
  }
}
```

### 4. Verify Ticket by Number

**Endpoint**: `POST /tickets/verify`
**Headers**: `Authorization: Bearer {token}`

**Request Body**:
```json
{
  "ticket_number": "TKT-XYZ789",
  "event_id": 1
}
```

**Response**: Same as scan ticket above

### 5. Check-in Ticket

**Endpoint**: `PUT /tickets/{ticket}/check-in`
**Headers**: `Authorization: Bearer {token}`

**Response** (200):
```json
{
  "status": "success",
  "message": "Ticket checked in successfully",
  "data": {
    "ticket": {
      "id": 1,
      "ticket_number": "TKT-XYZ789",
      "status": "used",
      "used_at": "2024-12-25T19:15:00Z"
    }
  }
}
```

## Organizer API

These endpoints are for organizer app features.

### 1. Get Organizer Profile

**Endpoint**: `GET /organizer/profile`
**Headers**: `Authorization: Bearer {token}`

**Response** (200):
```json
{
  "status": "success",
  "message": "Organizer profile retrieved successfully",
  "data": {
    "id": 1,
    "name": "Music Events Ltd",
    "email": "info@musicevents.com",
    "phone": "+233123456789",
    "address": "456 Event Street",
    "city": "Accra",
    "state": "Greater Accra",
    "country": "Ghana",
    "description": "Premier music event organizer",
    "website": "https://musicevents.com",
    "logo_url": "https://example.com/storage/logos/org1.jpg",
    "banner_url": "https://example.com/storage/banners/org1.jpg"
  }
}
```

### 2. Get Organizer Events

**Endpoint**: `GET /organizer/events`
**Headers**: `Authorization: Bearer {token}`

**Response** (200):
```json
{
  "status": "success",
  "message": "Events retrieved successfully",
  "data": [
    {
      "id": 1,
      "title": "Amazing Music Concert",
      "status": "published",
      "start_date": "2024-12-25",
      "start_time": "19:00:00",
      "total_tickets_sold": 180,
      "total_revenue": 9000.00,
      "tickets_checked_in": 0
    }
  ]
}
```

### 3. Get Single Event (Organizer View)

**Endpoint**: `GET /organizer/events/{event}`
**Headers**: `Authorization: Bearer {token}`

**Response**: Detailed event with organizer-specific statistics

### 4. Get Event Statistics

**Endpoint**: `GET /organizer/events/{event}/statistics`
**Headers**: `Authorization: Bearer {token}`

**Response** (200):
```json
{
  "status": "success",
  "message": "Event statistics retrieved successfully",
  "data": {
    "total_tickets": 250,
    "sold_tickets": 180,
    "available_tickets": 70,
    "checked_in_tickets": 0,
    "total_revenue": 9000.00,
    "ticket_types": [
      {
        "name": "Regular",
        "sold": 150,
        "available": 50,
        "revenue": 7500.00
      },
      {
        "name": "VIP",
        "sold": 30,
        "available": 20,
        "revenue": 1500.00
      }
    ]
  }
}
```

### 5. Get Event Tickets

**Endpoint**: `GET /organizer/events/{event}/tickets`
**Headers**: `Authorization: Bearer {token}`

**Response**: List of all tickets for the event with detailed information

### 6. Get Event Orders

**Endpoint**: `GET /organizer/events/{event}/orders`
**Headers**: `Authorization: Bearer {token}`

**Response**: List of all orders for the event

### 7. Check-in Ticket (Organizer)

**Endpoint**: `PUT /organizer/events/{event}/tickets/{ticket}/check-in`
**Headers**: `Authorization: Bearer {token}`

**Response**: Same as regular check-in endpoint

## Payment Integration

The platform uses Paystack for payment processing. Mobile apps should:

1. Create orders through the API
2. Use the returned `payment_url` to redirect users to Paystack checkout
3. Handle payment callbacks through webhooks (backend only)
4. Verify payment status through order endpoints

### Paystack Utilities

#### Get Banks List

**Endpoint**: `GET /paystack/banks?country=ghana`

**Response** (200):
```json
{
  "success": true,
  "banks": [
    {
      "id": 1,
      "name": "Access Bank",
      "code": "044",
      "country": "ghana"
    }
  ]
}
```

#### Verify Bank Account

**Endpoint**: `POST /paystack/verify-account`

**Request Body**:
```json
{
  "account_number": "1234567890",
  "bank_code": "044"
}
```

**Response** (200):
```json
{
  "success": true,
  "account_name": "John Doe",
  "account_number": "1234567890"
}
```

## Error Handling

All API responses follow a consistent format:

### Success Response Format
```json
{
  "status": "success",
  "message": "Description of what happened",
  "data": { /* Response data */ }
}
```

### Error Response Format
```json
{
  "status": "error",
  "message": "Error description",
  "errors": { /* Validation errors if applicable */ }
}
```

### Common HTTP Status Codes

- `200`: Success
- `201`: Created
- `400`: Bad Request
- `401`: Unauthorized (invalid/missing token)
- `403`: Forbidden (insufficient permissions)
- `404`: Not Found
- `422`: Validation Error
- `500`: Internal Server Error

### Validation Error Example
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

## Data Models

### Event Model
```json
{
  "id": "integer",
  "code": "string (8 chars, uppercase)",
  "slug": "string (SEO-friendly URL)",
  "title": "string",
  "description": "text",
  "cover_image_url": "string|null",
  "start_date": "date (Y-m-d)",
  "start_time": "time (H:i:s)",
  "end_date": "date|null",
  "end_time": "time|null",
  "status": "enum: draft|published|live|past|canceled",
  "tags": "array of strings",
  "mode": "enum: in-person|online|hybrid",
  "event_type": "string",
  "is_rsvp": "boolean",
  "platform_name": "string|null",
  "online_link": "string|null",
  "is_public_link": "boolean"
}
```

### User Model
```json
{
  "id": "integer",
  "name": "string",
  "email": "string (unique)",
  "phone": "string|null",
  "is_system_admin": "boolean",
  "created_at": "datetime",
  "updated_at": "datetime"
}
```

### Ticket Model
```json
{
  "id": "integer",
  "ticket_number": "string (unique)",
  "qr_code": "string (URL to QR image)",
  "status": "enum: active|used|cancelled",
  "used_at": "datetime|null",
  "attendee_name": "string",
  "attendee_email": "string",
  "attendee_phone": "string|null"
}
```

### Order Model
```json
{
  "id": "integer",
  "order_number": "string (unique, ORD-XXXXXXXXXX)",
  "buyer_email": "string",
  "buyer_phone": "string|null",
  "subtotal_amount": "decimal(10,2)",
  "discount_amount": "decimal(10,2)",
  "total_amount": "decimal(10,2)",
  "status": "enum: pending|confirmed|cancelled",
  "payment_method": "string",
  "payment_reference": "string|null",
  "purchased_at": "datetime|null"
}
```

## Security Considerations

### Authentication
- Use Bearer tokens for all authenticated requests
- Tokens are generated via Laravel Sanctum
- Tokens should be stored securely on the device
- Implement automatic token refresh

### Honeypot Protection
- All forms include a `honeypot` field that must be empty
- This helps prevent automated spam submissions
- Always include `"honeypot": ""` in form submissions

### Rate Limiting
- API endpoints are rate-limited per Laravel's default configuration
- Implement proper retry logic with exponential backoff
- Handle 429 (Too Many Requests) responses gracefully

### Data Validation
- All input is validated server-side
- Client-side validation should mirror server-side rules
- Handle validation errors gracefully with user-friendly messages

## Best Practices for Mobile Development

### Flutter Development Pattern
Based on the project instructions, use this pattern for Flutter features:
```
provider > feature_service > api_service + custom_interceptor + error_handling
```

### Recommended Architecture
1. **Provider**: State management using Flutter Provider
2. **Feature Service**: Business logic layer
3. **API Service**: HTTP client for API communication
4. **Custom Interceptor**: Handle authentication, logging, errors
5. **Error Handling**: Centralized error management

### Caching Strategy
- Cache event lists and details for offline viewing
- Cache user profile and tickets
- Implement proper cache invalidation
- Use local storage for authentication tokens

### Image Handling
- Images are served via Laravel's Storage system
- Implement proper image caching and lazy loading
- Handle different image sizes and formats
- Fallback for missing images

### Offline Support
- Cache critical data locally
- Queue API calls when offline
- Sync data when connection is restored
- Provide clear offline indicators

## Testing

### API Testing
- All endpoints include comprehensive tests
- Use the provided test commands: `composer run test`
- Tests cover authentication, validation, and business logic

### Mobile Testing
- Test with various network conditions
- Validate offline functionality
- Test payment flows thoroughly
- Verify QR code scanning accuracy

## Development Workflow

1. **Setup**: Clone repository and run `composer run dev`
2. **Testing**: Use `composer run test` for backend tests
3. **Code Style**: Run `php artisan pint` for PHP formatting
4. **Database**: Use migrations and seeders for consistent data
5. **Documentation**: Update this file when adding new endpoints

## Admin Panel Integration

The Suptix platform includes a comprehensive Filament admin panel for organizers. While mobile apps primarily use the API endpoints, there are some admin panel specific features that may be relevant for mobile development:

### Admin Panel Access
- **Base URL**: `{your-domain}/admin`
- **Multi-tenant**: Organizer-based tenancy
- **Authentication**: Web-based authentication (separate from API tokens)

### Key Admin Features for Mobile Context

#### 1. Check-In Management
The admin panel includes a "Start Check-In" page that organizers can use:
- **Route**: `/admin/start-check-in` (accessible via web only)
- **Purpose**: Allows organizers to select an event and start check-in sessions
- **Mobile Integration**: Mobile apps should use the API endpoints for ticket verification and check-in instead

#### 2. Real-time Dashboard
- Event statistics and analytics
- Recent orders and ticket sales
- Upcoming events overview
- Quick actions for event management

#### 3. Event Management
- Create, edit, and manage events through Filament resources
- Ticket type management with relation managers
- File upload handling for event covers and organizer assets

### Mobile App Considerations for Admin Features

1. **Separate User Flows**: Admin panel is web-based, while mobile apps use API
2. **Role Differentiation**: Consider different mobile app experiences for regular users vs organizers
3. **Data Synchronization**: API endpoints provide the same data as admin panel
4. **Offline Capabilities**: Mobile apps can cache admin data for offline access

## Filament Resource API Mapping

While Filament resources are web-based, the underlying data is accessible via the V2 API:

| Filament Resource | API Endpoint | Purpose |
|-------------------|--------------|---------|
| EventResource | `/api/v2/events` | Event management |
| OrderResource | `/api/v2/orders` | Order management |
| TicketResource | `/api/v2/tickets` | Ticket operations |
| VenueResource | No direct API | Managed through events |
| PaymentInfoResource | `/paystack/*` | Payment management |
| CheckInResource | `/api/v2/tickets/*/check-in` | Check-in operations |

## Support and Contact

For technical support or questions about the API:
- Review the main project documentation in `CLAUDE.md`
- Check existing tests for implementation examples
- Follow Laravel best practices for any customizations
- For admin panel specific questions, refer to Filament documentation

---

This documentation covers the complete API surface for mobile app development. Each endpoint includes request/response examples and follows consistent patterns for easy implementation. The API provides full access to all platform features while maintaining separation from the web-based admin panel.