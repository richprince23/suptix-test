# Ticket Scanning API Testing Guide

This document provides examples for testing the ticket scanning endpoints to ensure they work correctly.

## Setup

1. Start the Laravel development server:
```bash
composer run dev
```

2. Ensure you have test data in your database (tickets, events, organizers)

## API Endpoints Testing

### 1. QR Code Scanning (Public Endpoint)

**Endpoint:** `POST /api/tickets/scan-qr`

**Test Cases:**

```bash
# Test with JSON QR data
curl -X POST http://localhost:8000/api/tickets/scan-qr \
  -H "Content-Type: application/json" \
  -d '{
    "qr_data": "{\"ticket_number\":\"TKT-ABC123DEF456\",\"attendee_name\":\"John Doe\",\"price\":\"50.00\"}"
  }'

# Test with plain ticket number
curl -X POST http://localhost:8000/api/tickets/scan-qr \
  -H "Content-Type: application/json" \
  -d '{
    "qr_data": "TKT-ABC123DEF456"
  }'

# Test with invalid QR data
curl -X POST http://localhost:8000/api/tickets/scan-qr \
  -H "Content-Type: application/json" \
  -d '{
    "qr_data": "invalid-data"
  }'
```

**Expected Responses:**

Success (200):
```json
{
  "success": true,
  "message": "QR code scanned successfully",
  "data": {
    "qr_data": {...},
    "ticket": {
      "id": 1,
      "ticket_number": "TKT-ABC123DEF456",
      "attendee_name": "John Doe",
      "status": "active",
      "event": {...}
    }
  }
}
```

Error (400/404):
```json
{
  "success": false,
  "message": "Ticket not found in database"
}
```

### 2. Ticket Verification (Authenticated)

**Endpoint:** `POST /api/organizers/{organizer}/events/{event}/tickets/verify`

**Authentication Required:** Bearer token

```bash
# Get auth token first
TOKEN=$(curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"organizer@example.com","password":"password"}' \
  | jq -r '.token')

# Test ticket number verification
curl -X POST http://localhost:8000/api/organizers/1/events/1/tickets/verify \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "ticket_number": "TKT-ABC123DEF456",
    "verification_method": "manual"
  }'

# Test QR data verification
curl -X POST http://localhost:8000/api/organizers/1/events/1/tickets/verify \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "qr_data": "{\"ticket_number\":\"TKT-ABC123DEF456\"}",
    "verification_method": "qr_scan"
  }'
```

### 3. Mark Ticket as Used

**Endpoint:** `PUT /api/organizers/{organizer}/events/{event}/tickets/{ticket}`

```bash
curl -X PUT http://localhost:8000/api/organizers/1/events/1/tickets/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

**Alternative Endpoint:** `PATCH /api/tickets/{ticket}/mark-used`

```bash
curl -X PATCH http://localhost:8000/api/tickets/1/mark-used \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 4. Get Event Statistics

**Endpoint:** `GET /api/organizers/{organizer}/events/{event}/statistics`

```bash
curl -X GET http://localhost:8000/api/organizers/1/events/1/statistics \
  -H "Authorization: Bearer $TOKEN"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "total_tickets": 100,
    "used_tickets": 25,
    "active_tickets": 70,
    "cancelled_tickets": 5,
    "usage_rate": 25.0
  }
}
```

### 5. Get Ticket Entry History

**Endpoint:** `GET /api/organizers/{organizer}/events/{event}/tickets/entries`

```bash
curl -X GET "http://localhost:8000/api/organizers/1/events/1/tickets/entries?limit=10&offset=0" \
  -H "Authorization: Bearer $TOKEN"
```

## Flutter App Integration Tests

### Scanner Service Tests

```dart
// Test QR scanning
final result = await scannerService.scanTicket(
  qrData: 'TKT-ABC123DEF456',
  eventId: 1,
  organizerId: 1,
);

assert(result.isSuccess);
assert(result.ticket != null);

// Test manual verification
final result2 = await scannerService.verifyTicketNumber(
  ticketNumber: 'TKT-ABC123DEF456',
  eventId: 1,
  organizerId: 1,
);

assert(result2.isSuccess);
```

### Ticket Service Tests

```dart
// Test mark as used
final response = await ticketService.markTicketAsUsed(1, 1, 1);
assert(response['success'] == true);

// Test fallback endpoint
final response2 = await ticketService.markTicketAsUsedByTicketId(1);
assert(response2['success'] == true);
```

## Common Error Cases to Test

1. **Invalid ticket number:** Should return 404 with appropriate message
2. **Already used ticket:** Should return 400 with "already used" message
3. **Wrong event/organizer:** Should return 403 with authorization message
4. **Unauthenticated requests:** Should return 401 for protected endpoints
5. **Invalid JSON in QR code:** Should return 400 with parsing error
6. **Network timeout:** Flutter app should handle gracefully with offline mode

## Testing Offline Mode

1. Scan tickets while online to cache them
2. Disconnect from network
3. Try to scan cached tickets - should work offline
4. Try to mark tickets as used - should queue for sync
5. Reconnect and verify sync works properly

## Performance Testing

Test with multiple concurrent requests to ensure the API can handle load:

```bash
# Use Apache Bench for load testing
ab -n 100 -c 10 -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/organizers/1/events/1/tickets
```

## Database Verification

After running tests, verify in the database:

```sql
-- Check ticket status updates
SELECT ticket_number, status, used_at FROM tickets WHERE status = 'used';

-- Check event statistics
SELECT 
    e.title,
    COUNT(t.id) as total_tickets,
    SUM(CASE WHEN t.status = 'used' THEN 1 ELSE 0 END) as used_tickets,
    SUM(CASE WHEN t.status = 'active' THEN 1 ELSE 0 END) as active_tickets
FROM events e 
LEFT JOIN tickets t ON e.id = t.event_id 
GROUP BY e.id, e.title;
```

## Expected Behavior Summary

✅ **QR Code Scanning:**
- JSON QR codes work
- Plain ticket numbers work
- Invalid data returns proper errors

✅ **Ticket Verification:**
- Authenticated users can verify tickets for their events
- Proper authorization checks in place
- Detailed ticket information returned

✅ **Mark as Used:**
- Both endpoints work (`PUT` and `PATCH`)
- Tickets can only be marked once
- Proper status updates

✅ **Offline Support:**
- Cached tickets work offline
- Changes sync when back online
- No data loss during network issues

✅ **Error Handling:**
- Meaningful error messages
- Proper HTTP status codes
- Graceful degradation