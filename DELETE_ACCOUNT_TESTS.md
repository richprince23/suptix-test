# Delete Account API Tests

## Quick Test Instructions

### Prerequisites
1. Have a test user account set up
2. Get authentication token
3. Ensure test data exists

### 1. Get Authentication Token
```bash
# Replace with your test credentials
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password"
  }'
```

Save the token from the response.

### 2. Test Account Deletion Info
```bash
# Replace YOUR_TOKEN with actual token
curl -X GET "http://localhost:8000/api/account/deletion-info" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Expected Response Structure:**
```json
{
  "success": true,
  "data": {
    "user": { ... },
    "organizers": [ ... ],
    "owned_organizers": [ ... ],
    "personal_data": { ... },
    "can_delete": true/false,
    "deletion_info": { ... }
  }
}
```

### 3. Test Account Deletion (Dry Run)
```bash
# Test with invalid password first
curl -X DELETE "http://localhost:8000/api/account" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "password": "wrong_password",
    "confirmation": "DELETE_MY_ACCOUNT"
  }'
```

**Expected Response:** 400 error with invalid password message

### 4. Test Account Deletion (Valid)
⚠️ **WARNING: This will actually delete the account!**

```bash
# Only run this with a test account you want to delete
curl -X DELETE "http://localhost:8000/api/account" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "password": "correct_password",
    "confirmation": "DELETE_MY_ACCOUNT"
  }'
```

**Expected Response:** 200 success with deletion confirmation

## Test Cases Checklist

### ✅ Account Deletion Info Endpoint
- [ ] Returns user information correctly
- [ ] Lists organizers user belongs to
- [ ] Identifies organizers user owns
- [ ] Shows personal data counts
- [ ] Correctly calculates `can_delete` status
- [ ] Provides clear deletion information

### ✅ Account Deletion Endpoint
- [ ] Requires authentication
- [ ] Validates password correctly
- [ ] Validates confirmation string
- [ ] Blocks deletion if user owns organizers
- [ ] Successfully deletes account when allowed
- [ ] Anonymizes personal data in orders/tickets
- [ ] Removes user from organizer memberships
- [ ] Deletes all user tokens

### ✅ Error Handling
- [ ] Returns 401 for unauthenticated requests
- [ ] Returns 400 for invalid password
- [ ] Returns 400 for wrong confirmation
- [ ] Returns 400 when user owns organizers
- [ ] Handles database errors gracefully

### ✅ Data Integrity
- [ ] Database transaction rolls back on failure
- [ ] No partial deletions occur
- [ ] Business data remains intact
- [ ] Personal data is properly anonymized

## Flutter App Testing

### Setup Test Environment
1. Ensure your Flutter app has the account service configured
2. Add the AccountProvider to your provider tree
3. Create test screens that use the account deletion functionality

### Test Scenarios
1. **View Deletion Info**: Navigate to account deletion page and verify information displays correctly
2. **Attempt Deletion**: Try deleting account with wrong password, verify error handling
3. **Successful Deletion**: Complete account deletion flow with test account
4. **Post-Deletion**: Verify user is logged out and can no longer access authenticated features

## Database Verification

After testing, verify the database state:

### Check User Deletion
```sql
-- User should be completely removed
SELECT * FROM users WHERE email = 'test@example.com';
-- Should return 0 rows
```

### Check Data Anonymization
```sql
-- Orders should be anonymized
SELECT buyer_email, buyer_phone FROM orders 
WHERE buyer_email LIKE 'deleted-user-%@deleted.local';

-- Tickets should be anonymized  
SELECT attendee_name, attendee_email FROM tickets 
WHERE attendee_email LIKE 'deleted-user-%@deleted.local';
```

### Check Token Cleanup
```sql
-- Personal access tokens should be deleted
SELECT * FROM personal_access_tokens WHERE tokenable_id = [USER_ID];
-- Should return 0 rows
```

### Check Organizer Relationships
```sql
-- User should be removed from organizer_user pivot table
SELECT * FROM organizer_user WHERE user_id = [USER_ID];
-- Should return 0 rows
```

## Troubleshooting

### Common Issues

1. **500 Error on Deletion Info**
   - Check if user has proper organizer relationships
   - Verify database relationships are set up correctly

2. **Transaction Failures**
   - Check database constraints
   - Verify foreign key relationships
   - Check for any triggers that might interfere

3. **Partial Deletions**
   - Ensure all deletion code is within the transaction
   - Check for any exceptions not being caught

4. **Flutter Provider Issues**
   - Verify AccountProvider is properly registered
   - Check API service is injected correctly
   - Ensure proper error handling in UI

### Debug Commands

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check database queries (in tinker)
php artisan tinker
>>> DB::enableQueryLog();
>>> // Run your test
>>> DB::getQueryLog();
```

## Security Testing

### Authentication Tests
- [ ] Endpoint rejects requests without token
- [ ] Endpoint rejects requests with invalid token
- [ ] Endpoint rejects requests with expired token

### Authorization Tests
- [ ] User can only delete their own account
- [ ] No privilege escalation possible

### Input Validation Tests
- [ ] SQL injection attempts are blocked
- [ ] XSS attempts are sanitized
- [ ] Invalid JSON is handled gracefully

## Performance Testing

### Load Testing
- Test multiple concurrent deletion requests
- Verify database transaction handling under load
- Check for deadlocks or race conditions

### Memory Testing
- Monitor memory usage during deletion process
- Check for memory leaks in transaction handling

## Production Readiness Checklist

- [ ] All tests pass
- [ ] Error handling is comprehensive
- [ ] Database transactions work correctly
- [ ] Data anonymization is complete
- [ ] Security validations are in place
- [ ] Logging is properly implemented
- [ ] Flutter UI handles all error cases
- [ ] Documentation is complete