# SMS Confirmation Setup with Hubtel

This document outlines the SMS confirmation functionality implemented for order notifications.

## Overview

SMS confirmations are automatically sent when:
1. **Orders are confirmed** - After successful payment via Paystack
2. **Admin creates orders** - When orders are created through the Filament admin panel

## Components

### 1. Hubtel SMS Service (`app/Services/HubtelSmsService.php`)
- Handles communication with Hubtel SMS API
- Formats Ghana phone numbers properly (+233 format)
- Provides error handling and logging
- Includes balance checking functionality

### 2. SMS Notification (`app/Notifications/OrderConfirmationSms.php`)
- Laravel notification class for order confirmations
- Builds SMS message with order details
- Handles queuing for background processing

### 3. Custom SMS Channel (`app/Channels/SmsChannel.php`)
- Custom notification channel for SMS delivery
- Registered in `AppServiceProvider`

## Configuration

### Environment Variables
Add these to your `.env` file:

```env
# Hubtel SMS Configuration
HUBTEL_BASE_URL=https://sms.hubtel.com
HUBTEL_CLIENT_ID=your_client_id_here
HUBTEL_CLIENT_SECRET=your_client_secret_here
HUBTEL_SENDER_ID=Suptix
```

### Services Configuration
Already configured in `config/services.php` with Hubtel settings.

## Integration Points

### 1. Payment Confirmation
**File:** `app/Services/PaystackService.php`
**Location:** Line ~588 in `processSuccessfulPayment()` method

SMS notification is sent automatically after:
- Payment is verified and processed
- Order status is updated to 'confirmed'
- Confirmation emails are sent

### 2. Admin Order Creation
**File:** `app/Filament/Resources/OrderResource/Pages/CreateOrder.php`
**Location:** `afterCreate()` method

SMS notification is sent when:
- Admin creates an order through the dashboard
- Order has a valid buyer_phone number
- Email confirmation is also sent

## SMS Message Format

```
🎫 Suptix Order Confirmed!

Event: [Event Title]
Order: [Order Number]
Tickets: [Number of Tickets]
Amount: GHS [Total Amount]
Date: [Event Date and Time]

Your tickets will be sent via email. Search your order at: [URL]

Thank you for choosing Suptix!
```

## Phone Number Formatting

The service automatically handles Ghana phone number formats:
- `0xxxxxxxxx` → `+233xxxxxxxxx`
- `xxxxxxxxx` → `+233xxxxxxxxx`  
- `233xxxxxxxxx` → `+233xxxxxxxxx`
- `+233xxxxxxxxx` → `+233xxxxxxxxx` (no change)

## Testing

### Test SMS Command
```bash
php artisan sms:test 0201234567 "Test message"
```

### Manual Testing
```php
use App\Services\HubtelSmsService;

$smsService = app(HubtelSmsService::class);
$result = $smsService->sendSms('0201234567', 'Test message');
```

## Logging

All SMS activities are logged with detailed information:
- Success/failure status
- Phone numbers
- Message IDs from Hubtel
- Error details
- Order information

Logs can be found in Laravel logs with the following contexts:
- `Hubtel SMS sent successfully`
- `Hubtel SMS request failed`
- `Order confirmation SMS queued`
- `Failed to queue order confirmation SMS`

## Error Handling

The system gracefully handles:
- Invalid phone number formats
- Hubtel API failures
- Network connectivity issues
- Missing configuration
- Queue processing failures

Orders are never blocked by SMS failures - emails still work independently.

## Queuing

SMS notifications are queued using Laravel's notification system:
- Implements `ShouldQueue` interface
- Processed by queue workers
- Failures are logged but don't affect order processing

## Security

- Credentials stored in environment variables
- Phone numbers formatted and validated
- API responses logged for debugging
- No sensitive data exposed in SMS content

## Getting Hubtel Credentials

1. Sign up at [Hubtel Developer Portal](https://developers.hubtel.com/)
2. Create a new application
3. Get your Client ID and Client Secret
4. Add them to your `.env` file
5. Test with the provided test command

## Troubleshooting

1. **SMS not sending**
   - Check Hubtel credentials in `.env`
   - Verify phone number format
   - Check Laravel logs for errors
   - Ensure queue workers are running

2. **Invalid phone number**
   - Must be Ghana format
   - 9-10 digits for local numbers
   - Starts with valid Ghana prefix

3. **Queue not processing**
   - Run `php artisan queue:work`
   - Check `QUEUE_CONNECTION=database` in `.env`
   - Verify database queue tables exist

## Future Enhancements

- Support for international phone numbers
- SMS templates for different event types
- Delivery status tracking
- SMS unsubscribe functionality
- Bulk SMS for event reminders