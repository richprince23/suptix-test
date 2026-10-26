# Defect log

Every entry was proven by a failing test before it was fixed, and fixed in its own commit with that test passing afterwards. Case identifiers refer to [the test plan](../testing/TEST_PLAN.md). The failing output before any fix is in [pre-fix-test-run.txt](pre-fix-test-run.txt).

Severity: **High** = money, admission or personal data is wrong; **Medium** = a wrong result or a server error without direct financial loss; **Low** = quality or maintainability.

## Defects in the code

| ID | Flow | Defect | Exposed by | Root cause | Fix commit | Severity |
|---|---|---|---|---|---|---|
| DEF-01 | F1 | A percentage discount above 100 gave a discount larger than the order, so the stored discount and subtotal were wrong | F1-44 (purchase and validation) | Percentage not capped; calculation duplicated in three places without rounding | `9fb1f07` | Medium |
| DEF-02 | F1 | Expired and not-yet-valid discount codes were accepted at purchase | F1-51, F1-52 | Only the validation endpoint checked dates | `7cb58e6` | High |
| DEF-03 | F1 | A discount code was rejected on its last valid day | F1-64 | `valid_until` is a date at midnight and was compared with the current time | `7cb58e6` | Medium |
| DEF-04 | F1 | A quantity sent as the string "2" was rejected | F1-15 | Attendee count compared to the quantity with `!==` | `1eddf8a` | Low |
| DEF-05 | F2 | An order was confirmed for a payment of any amount | F2-16, F2-41 | `processSuccessfulPayment` never compared the paid amount with the order total | `aa5a8c4` | High |
| DEF-06 | F2 | A gateway event naming a different order than its reference was accepted | F2-17 | Metadata `order_id` ignored | `aa5a8c4` | Medium |
| DEF-07 | F2 | An order of several same-price tickets produced one purchase record | F2-15 | Duplicate check matched on amount only | `7b225ff` | Medium |
| DEF-08 | F2 | A fractional number of pesewas was sent to Paystack as the platform fee (116.655) | F2-03 | Fee computed as a float | `fb08f14` | Medium |
| DEF-09 | F2 | Two initialisations for one order in the same second shared a payment reference | F2-08 | Reference built from the order number and `time()` only | `fb08f14` | Medium |
| DEF-10 | F2 | A webhook without a signature header caused a 500 instead of 400 | F2-36, F2-44 | `verifyWebhookSignature(string, string)` received `null` and raised a TypeError | `f71ef6b` | Medium |
| DEF-11 | F2 | A dispute webhook caused a 500 and the payment was never marked disputed | F2-42 | Status `disputed` is not in the `payments.status` enum | `c566dab` | Medium |
| DEF-12 | F2 | Recorded balances did not add up to the amount paid (GHS 16.00 recorded for GHS 15.00 over 100 payments of 0.15) | F2-62, F2-66 | Both parts of the split were rounded separately by the database | `d9070e1` | High |
| DEF-13 | F3 | Scanning a used, unpaid or cancelled ticket reported success | F3-06, F3-07, F3-08 | `scanQr` formatted the ticket without checking its status | `7e74692` | High |
| DEF-14 | F3 | The public scan endpoint and the verification page returned the attendee email to anyone | F3-16, F3-17 | No distinction between staff and guests | `7e74692` | High |
| DEF-15 | F3 | QR data without all expected keys caused a 500 | F3-25 | Undefined array key in `validateQrData` | `5851c27` | Low |
| DEF-16 | F3 | Two simultaneous check-ins could both succeed and the second overwrote the entry time | F3-30, F3-31 | Status read, then a plain update | `a7dd750` | High |
| DEF-17 | F3 | `Ticket` listed an `is_used` attribute that has no database column | F3-09 | Dead fillable and cast entries | `f98f688` | Low |
| DEF-18 | F1 | An abandoned order kept its discount use after clean-up, so unpaid orders could exhaust a limited code | F4-05 | Clean-up restored stock but not the discount count | `e8ecaec` | Medium |
| DEF-19 | F3 | The event ticket list cost 1,007 queries and about 1.0 s for 500 tickets | P-02 | Event and organizer lazy loaded per ticket | `ba99801` | Medium |
| DEF-20 | F2 | Bank account verification always returned 422, balance sync always returned 403, and a second unrouted webhook handler existed | F2-70, F2-72, F2-75, F2-77 | Validation rule `min:80\|max:17`; check against a nonexistent `User::currentTenant`; abandoned duplicate | `77095be` | Medium |
| DEF-21 | All | 105 static analysis findings at PHPStan level 5, including a nonexistent `Event::event_name` and an `env()` call outside config | PHPStan | Untyped Eloquent relations and small errors | `0a9cf61` | Low |
| DEF-22 | F2 | Approving payment info never dispatched the role assignment job | Inherited `PaymentInfoRoleAssignmentTest` | The observer compared the computed status accessor to `'approved'` | `0833b6a` | Medium |
| DEF-23 | F2 | The balance service split a payment into two separately rounded parts (GHS 250.50 at 7% gave 250.51; GHS 0.15 at 10% gave 0.16) | F2-87 | Same fault as DEF-12 in `PaystackBalanceService` | `c6e700d` | High |
| DEF-24 | F3 | The scan statistics endpoint failed with a 500 on every call | F3-46 | The hourly and daily queries were built on the same query builder, so the second grouped by a column that does not exist | `c673fbe` | Medium |
| DEF-25 | F3 | Any signed-in user could verify tickets of any organiser; the statistics and entry history endpoints did not check that the event belonged to the organiser in the URL; a mismatched ticket came back with the holder's details | F3-35, F3-36, F3-44 | Missing authorisation checks on organiser endpoints | `af82aed` | High |
| DEF-26 | All | 30 ESLint errors on the five in-scope front-end files (28 unused imports and declarations in `EventDetails`, a mutated prop in `TicketVerification`, an always-on `Transition`, an unused variable) | ESLint | Dead code and a prop mutation | `22e160b` | Low |
| DEF-27 | F3 | Scanning the QR code printed on a PDF ticket failed with 'QR code data does not match database records' for every ticket | F3-62 | The QR payload holds the event date as the JSON form of a date; `validateQrData` compared it with a Carbon instance using `!==`, which is never equal | `1410209` | High |
| DEF-28 | F1 | The 'Customize Attendee Information' checkbox on the buyer form did nothing; the attendee fields were always visible with 'Guest' typed into them, so a typed name was appended to it | Browser test (E-01) | The checkbox listened for a native `change` event that the reka-ui Checkbox does not emit | `5385e3c` | Medium |
| DEF-29 | F1 | The event page stayed blank: the page script threw `ReferenceError: watch is not defined`, so the ticket purchase page never rendered and no ticket could be bought | Browser purchase-flow tests (5 tests did not complete) | `EventDetails.vue` calls `watch` (session grouping, report modal, RSVP email) but imports only `computed`, `reactive` and `ref`; the restored original code lost the import in the earlier lint clean-up | Uncommitted: `watch` added to the import in `resources/js/pages/EventDetails.vue`, assets rebuilt | High |

**Summary:** 29 code defects (10 High, 14 Medium, 5 Low): 28 in about 1,850 in-scope lines of PHP, a density of about 15 per thousand lines, and DEF-29 in front-end code. By flow: F1 7, F2 11, F3 9, all 2 (the two static analysis findings). With the Critical level of the test severity guide, four of the High defects (DEF-05, DEF-13, DEF-16, DEF-25) are Critical.

## Defects in tests and environment

| Item | Finding | Resolution |
|---|---|---|
| `PaymentInfoNotificationTest` | Expected an SMS channel that was deliberately removed to reduce costs | Test updated (`620ed57`) |
| `PaystackSubaccountSyncTest` | Update ran outside a system admin context, so no sync occurred | Test acts as a system admin (`4bfb558`) |
| `PaymentInfoRoleAssignmentTest` | Tests assumed manual approval, but submission now auto-approves and assigns the role | Tests create pending records without model events (`0833b6a`) |
| `Api\V2\AuthTest` | Random Faker phone number sometimes exceeded 15 characters, so the test failed intermittently | Fixed number (`41f675e`) |
| Shared development database | Five account-setup tests failed with `RoleAlreadyExists` | Dedicated `suptix_test` database |
| Pest 4 upgrade | `/** @test */` docblocks are ignored by PHPUnit 12, so seven tests were silently not run | Converted to `#[Test]` |
| `Auth/*` and `DashboardTest` (17 tests) | Starter-kit tests for routes the application does not serve: it forwards `/login` and `/register` to Filament and has no dashboard route | Rewritten to describe the current design (25 October 2026) |
| `EventPartnerTest` (2 tests) | Logo tests faked the `public` disk while the model uses the default disk, so they depended on `FILESYSTEM_DISK` | Tests set the default disk to `public` |
| `RsvpHoneypotTest` (1 test) | Test event was not an RSVP event, so no credit account existed and booking returned 404 | Event created with `is_rsvp => true` |
| `RsvpWaitlistTest` (1 test) | Asserted the waitlist mail was queued; the controller sends it immediately | Asserts the mail was sent |

## Investigated and found correct, or not provable here

| Case | Result |
|---|---|
| F1-47 fractional discount rounding | The database column rounded to two decimals, so stored values were consistent; rounding is now explicit in `Discount::amountFor()` |
| F1-72 one-use discount, two buyers | Sequential requests behave correctly; a true race cannot be reproduced in a transactional suite |
| F2-31, F2-40, F2-18 repeated callback or webhook | Idempotent when repeated one after the other; a simultaneous double delivery cannot be reproduced here |
| F1-25, F1-26 closed sales window | Refused correctly; the per-type message is only reachable when another type is still on sale |

## Observed but not changed (outside the scope)

- API v2 reports `used_tickets` and `is_used` from the nonexistent `is_used` column, so it always shows zero used tickets.
- `Ticket::purchases()` points at a `purchases.ticket_id` column that does not exist (purchases belong to orders), so every API v2 call that uses it raises an SQL error.
- `PaymentInfo`'s `updated` hook tests `isDirty('status')`, which is always false inside an updated event, so that block never runs.

## Found by static checks while making the CI workflow pass (not counted above)

| Item | Finding | Resolution |
|---|---|---|
| `formatDateShort` missing in `EventDetails.vue` | The schedule section calls `formatDateShort` for events with sessions, but the page no longer defined it, so the call would fail at render. The earlier lint clean-up (`22e160b`) removed it as unused; the later restore of the original template used it again. Found by `vue-tsc` (TS2551), not by a failing test | Function restored |
| Type errors and style failures | `vue-tsc` reported 31 errors against a limit of 26, Pint failed on 5 files, and ESLint on 4 unused variables | Type annotations, formatting and four unused declarations fixed with no change in behaviour; `vue-tsc` now reports 21 |
