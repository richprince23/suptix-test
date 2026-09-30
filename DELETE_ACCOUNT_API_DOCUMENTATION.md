# Delete Account API Documentation

## Overview

The Delete Account API provides mobile users with the ability to permanently delete their account and personal data in compliance with privacy regulations like GDPR. The implementation includes proper data handling, business logic validation, and user-friendly error messages.

## API Endpoints

### 1. Get Account Deletion Information

**Endpoint:** `GET /api/account/deletion-info`  
**Authentication:** Required (Bearer token)

This endpoint provides information about what data will be affected when the account is deleted.

#### Response Format:
```json
{
  "success": true,
  "data": {
    "user": {
      "name": "John Doe",
      "email": "john@example.com",
      "created_at": "2024-01-15T10:30:00.000000Z"
    },
    "organizers": [
      {
        "id": 1,
        "name": "Event Company",
        "role": "member",
        "events_count": 5,
        "total_tickets": 150
      }
    ],
    "owned_organizers": [
      {
        "id": 2,
        "name": "My Event Org",
        "events_count": 3,
        "active_tickets_count": 45
      }
    ],
    "personal_data": {
      "orders_count": 12,
      "tickets_count": 25
    },
    "can_delete": false,
    "deletion_info": {
      "what_will_be_deleted": [
        "Your user account and profile",
        "All authentication tokens",
        "Membership in organizations (if not sole owner)"
      ],
      "what_will_be_anonymized": [
        "Order information (email will be anonymized)",
        "Ticket attendee information (name and email anonymized)"
      ],
      "what_will_be_kept": [
        "Order records (for business purposes, but anonymized)",
        "Ticket records (for event history, but anonymized)",
        "Event and organization data (if you're not the sole owner)"
      ]
    }
  }
}
```

### 2. Delete Account

**Endpoint:** `DELETE /api/account`  
**Authentication:** Required (Bearer token)

Permanently deletes the user account and processes associated data according to business rules.

#### Request Body:
```json
{
  "password": "user_current_password",
  "confirmation": "DELETE_MY_ACCOUNT"
}
```

#### Validation Rules:
- `password`: Required, must match the user's current password
- `confirmation`: Required, must be exactly "DELETE_MY_ACCOUNT"

#### Success Response (200):
```json
{
  "success": true,
  "message": "Your account has been successfully deleted. All personal data has been removed or anonymized."
}
```

#### Error Responses:

**Invalid Password (400):**
```json
{
  "success": false,
  "message": "Invalid password provided"
}
```

**Cannot Delete - Sole Owner (400):**
```json
{
  "success": false,
  "message": "Cannot delete account. You are the sole owner of: Event Company. Please transfer ownership or delete these organizations first.",
  "organizers_requiring_action": [
    {
      "id": 1,
      "name": "Event Company",
      "events_count": 5,
      "active_tickets_count": 120
    }
  ]
}
```

**Validation Error (422):**
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "confirmation": ["The confirmation field must be DELETE_MY_ACCOUNT."]
  }
}
```

## Business Logic

### Account Deletion Rules

1. **Sole Owner Protection**: Users cannot delete their account if they are the sole owner of any organizer that has:
   - Active events
   - Sold tickets
   - Any business data that would be orphaned

2. **Data Anonymization**: Instead of deleting business records, personal data is anonymized:
   - Order emails become `deleted-user-{user_id}@deleted.local`
   - Ticket attendee information is replaced with "Deleted User"
   - Phone numbers are nullified

3. **Complete Cleanup**: When deletion is allowed:
   - User account is permanently deleted
   - All authentication tokens are revoked
   - User is removed from all organizer memberships
   - Personal data in orders/tickets is anonymized

### Database Transactions

The deletion process uses database transactions to ensure data integrity:
- If any step fails, all changes are rolled back
- Prevents partial deletions that could leave the system in an inconsistent state

## Flutter Integration

### Setup Provider

```dart
// In your main.dart or where you setup providers
MultiProvider(
  providers: [
    ChangeNotifierProvider(
      create: (context) => AccountProvider(
        AccountService(context.read<ApiService>()),
      ),
    ),
    // ... other providers
  ],
  child: MyApp(),
)
```

### Using in Flutter

```dart
// Navigate to account deletion page
Navigator.push(
  context,
  MaterialPageRoute(
    builder: (context) => AccountDeletionPage(),
  ),
);
```

### Service Integration

```dart
// Create account service instance
final accountService = AccountService(apiService);

// Get deletion info
final info = await accountService.getAccountDeletionInfo();

// Delete account
final result = await accountService.deleteAccount(
  password: 'user_password',
  confirmation: 'DELETE_MY_ACCOUNT',
);
```

## Testing

### Manual Testing

1. **Test deletion info endpoint:**
```bash
curl -X GET "http://localhost:8000/api/account/deletion-info" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

2. **Test account deletion:**
```bash
curl -X DELETE "http://localhost:8000/api/account" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "password": "user_password",
    "confirmation": "DELETE_MY_ACCOUNT"
  }'
```

### Test Cases

1. **Happy Path**: Regular user with no owned organizers
2. **Blocked Deletion**: User who is sole owner of organizers
3. **Invalid Password**: Wrong password provided
4. **Invalid Confirmation**: Wrong confirmation string
5. **Database Error**: Simulate database failure during transaction

### Expected Behaviors

✅ **Successful Deletion:**
- User account removed
- Tokens revoked
- Data anonymized
- Confirmation message shown

✅ **Blocked Deletion:**
- Clear error message
- List of organizers requiring action
- Account remains intact

✅ **Validation Errors:**
- Helpful error messages
- No data changes

## Security Considerations

### Authentication
- All endpoints require valid authentication tokens
- Tokens are verified before any operations

### Authorization
- Users can only delete their own accounts
- No administrative override capabilities

### Data Protection
- Password verification prevents accidental deletions
- Explicit confirmation string prevents automated attacks
- Database transactions ensure data integrity

### Audit Trail
- Consider logging account deletion events
- Maintain anonymized records for business purposes

## Compliance Features

### GDPR Compliance
- **Right to Erasure**: Complete account deletion
- **Data Minimization**: Only necessary data is kept
- **Purpose Limitation**: Anonymized data for legitimate business purposes
- **Transparency**: Clear information about what happens to data

### Data Retention
- Business records are anonymized, not deleted
- Maintains transaction history for legal/financial purposes
- Personal identifiers are removed

## Error Handling

### Client-Side (Flutter)
- Network error handling with user-friendly messages
- Loading states during deletion process
- Confirmation dialogs prevent accidental deletions

### Server-Side (Laravel)
- Input validation with detailed error messages
- Database transaction rollbacks on failures
- Graceful error responses with appropriate HTTP status codes

## Future Enhancements

### Possible Additions
1. **Grace Period**: 30-day deletion with account recovery option
2. **Data Export**: Allow users to download their data before deletion
3. **Ownership Transfer**: Automated process for transferring organizer ownership
4. **Deletion Scheduling**: Allow users to schedule deletion for a future date
5. **Admin Override**: Allow system administrators to force deletion in special cases

### Monitoring
- Track deletion success/failure rates
- Monitor for unusual deletion patterns
- Log deletion reasons for analysis