# Test Plan: Suptix core ticketing flows

| | |
|---|---|
| Module | 7020SCN Software Quality Assurance and Testing |
| Author | Richard Kweku Aikins (17626923) |
| Status | Version 1.0, written before the main test implementation |
| Strategy | [TEST_STRATEGY.md](TEST_STRATEGY.md) |
| Branch | `slim-core` |
| Due date | 26 October 2026, 18:00 |

## 1. Introduction

This plan applies the strategy to Suptix. It lists the test items, the features to be and not to be tested, pass and fail criteria, deliverables, environment, schedule, risks and the test cases (Appendix A). The "Prediction before execution" column in the catalogue records what the author expected from reading the code, before any test was run. It is kept so that the final results can be compared with the predictions.

## 2. Test items

| Class | Executable lines | Baseline lines covered |
|---|---|---|
| `TicketPurchaseController` | 196 | 0 |
| `DiscountController` | 56 | 0 |
| `Discount` | 10 | 0 |
| `Order` | 20 | 6 |
| `Ticket` | 18 | 3 |
| `TicketType` | 30 | 0 |
| `PaymentController` | 180 | 0 |
| `PaystackService` | 480 | 0 |
| `PaystackController` | 113 | 0 |
| `PaystackBalanceService` | 257 | 0 |
| `Payment` | 2 | 0 |
| `TicketController` | 503 | 0 |
| `CheckInController` | 20 | 0 |
| **Total in scope** | **1,885** | **9 (0.5%)** |

Baseline: commit `da57882`, clean test database, Pest 4, pcov. Source: `docs/baseline/clover-cleandb.xml`.

## 3. Features to be tested and not tested

To be tested: ticket purchase validation, pricing and discounts, stock reservation, payment initialisation, payment callback, signed webhook handling, balance recording, ticket state changes, QR and ticket-number lookup, organiser authorisation, check-in page access, abandoned-order clean-up, and checkout and scan performance.

Not tested: mobile API v2, Filament administration screens, organiser sign-up and payment-info approval (except where they affect the flows), SMS provider internals, e-mail template layout, PDF ticket rendering, and live-mode Paystack behaviour.

## 4. Approach

As described in the strategy: risk-based, with the levels, techniques, environment and doubles set out there. Each test carries its case identifier in its name (for example `F1-07`), so every result can be traced to a row of Appendix A.

## 5. Pass and fail criteria

A test passes when its assertions hold. A failing test is classified as a code defect, a test defect or an environment fault. The release criteria are the exit targets in section 8 of the strategy. A suspected defect is only recorded if a test proves it.

## 6. Suspension and resumption

Testing is suspended if the test database cannot be migrated, if the suite cannot run because of a dependency or environment fault, or if a change breaks more than a few unrelated tests. It resumes when the cause is fixed and the baseline run is repeated.

## 7. Deliverables

| Deliverable | Location |
|---|---|
| Test strategy and plan | `docs/testing/` |
| Automated tests | `tests/Unit`, `tests/Feature`, `tests/Browser` |
| Model factories | `database/factories` |
| Baseline and final coverage and test output | `docs/baseline/`, `docs/final/` |
| Defect log with fix commits | Commit history and the coursework report |
| CI workflow | `.github/workflows/` |
| Manual gateway test log | Coursework report |

## 8. Environment

See section 7 of the strategy. Tests are run with `php artisan test` (the Browser suite needs Chromium installed through Playwright) and coverage with `php -d pcov.enabled=1 -d pcov.directory=app artisan test --coverage-clover <file>`.

## 9. Schedule

| Date | Work |
|---|---|
| 21 to 22 Oct | Priority 1 tests for F1 and F2 (validation, totals and discounts, stock, callback, webhook decision table) |
| 22 to 23 Oct | Priority 1 tests for F3 and concurrency simulations; classify each failure; fix proven defects, one commit each |
| 23 to 24 Oct | PHPStan and mutation testing, performance cases, CI workflow, manual gateway runs |
| 24 to 25 Oct | Final coverage and mutation run saved under `docs/final/`; tag baseline and final commits |
| 25 to 26 Oct | Report writing, proofreading and submission |

## 10. Risks and contingencies

| Risk | Contingency |
|---|---|
| Not enough time for every case | Priority 1 first; priority 3 cases are dropped before priority 2 |
| 80% coverage unreachable for the two largest classes | Report the shortfall and reason; do not inflate coverage with assertion-free tests |
| Mutation testing too slow | Run on in-scope paths only, as a separate job |
| Paystack test mode unavailable or tunnel down | Rely on the faked suite and note the gap |
| A fix breaks other behaviour | Regression test with each fix; full suite run before each commit |
| Inherited failing tests mask new failures | Triage them first; separate them in reporting |

## 11. Roles

One tester and developer (Richard Kweku Aikins). An AI coding assistant is used for drafting and is declared in the report; all results are produced by running commands in the repository.

## 12. Traceability: goals to cases

| Goal (report Table 8) | Cases |
|---|---|
| Totals are correct | F1-40 to F1-55, F1-60 to F1-67 |
| No overselling | F1-29 to F1-33, F1-70, E-01 |
| Payment confirmed only when genuine | F2-13, F2-16, F2-30 to F2-38, F2-41 |
| One admission per ticket | F3-01 to F3-08, F3-30 to F3-32 |
| Fast gate entry | P-03, P-04 |
| Tests detect faults | Mutation run over all of the above |
| Maintainable code | Static analysis (PHPStan, Pint, ESLint) |
| Healthy suite | D (inherited failing tests) |

## Appendix A. Test case catalogue

Case identifiers: F1 to F3 flows, F4 stock release and clean-up, E browser, M manual gateway run, X cross-cutting security, P performance, D inherited failing tests. Technique: EP equivalence partitioning, BVA boundary value analysis, DT decision table, ST state transition, NEG negative, CON concurrency, SEC security, PERF performance, IDEM idempotency. Level: U unit, F feature, C concurrency, P performance. Priority: 1 must write, 2 should write, 3 if time allows.

### A.1 F1 Purchase and pricing

Code: `TicketPurchaseController::purchase`, `DiscountController`, `TicketType`, `Event::isPurchasable`, `Order`, `Ticket`.

#### A.1.1 Request validation

| ID | Case | Technique | Level | Pri | Prediction before execution |
|---|---|---|---|---|---|
| F1-01 | Valid single ticket purchase creates one order (status `pending`), one ticket (status `pending`), decrements type quantity by 1, then redirects to the Paystack URL (Paystack faked) | EP | F | 1 | Pass |
| F1-02 | Missing `buyer_email` is rejected, no order created | NEG | F | 2 | Pass |
| F1-03 | Invalid `buyer_email` format is rejected | EP | F | 2 | Pass |
| F1-04 | `tickets` empty array rejected | BVA | F | 2 | Pass |
| F1-05 | Quantity 0 rejected | BVA | F | 1 | Pass |
| F1-06 | Quantity 1 accepted (lower boundary) | BVA | F | 1 | Pass |
| F1-07 | Quantity 10 accepted (upper boundary) | BVA | F | 1 | Pass |
| F1-08 | Quantity 11 rejected | BVA | F | 1 | Pass |
| F1-09 | Attendee count differs from quantity (2 requested, 1 attendee) rejected, no stock change | NEG | F | 1 | Pass |
| F1-10 | Attendee name missing rejected | NEG | F | 2 | Pass |
| F1-11 | Non-empty `honeypot` field rejected with the generic message, no order created | SEC | F | 1 | Pass |
| F1-12 | Unknown `ticket_type_id` rejected | NEG | F | 2 | Pass |
| F1-13 | Custom field value over 1000 characters rejected | BVA | F | 3 | Pass |
| F1-14 | Custom field responses are stored as `TicketResponse` rows | EP | F | 3 | Pass |
| F1-15 | Purchase sent as a normal form post (`post()`, quantity as the string `"2"`) is accepted like `postJson()` | EP | F | 2 | DEFECT? (attendee count compared with `!==`) |

#### A.1.2 Event and ticket-type rules

| ID | Case | Technique | Level | Pri | Prediction before execution |
|---|---|---|---|---|---|
| F1-20 | Event status `draft` cannot be purchased | DT | F | 1 | Pass |
| F1-21 | Event status `canceled` cannot be purchased | DT | F | 1 | Pass |
| F1-22 | Status `published` and `live` can be purchased | DT | F | 1 | Pass |
| F1-23 | Event with no `end_time` that started more than 24 hours ago is not purchasable (boundary: 23h59 yes, 24h00 no) | BVA | U | 2 | Pass |
| F1-24 | Event with `end_time` in the past is not purchasable | BVA | U | 2 | Pass |
| F1-25 | Ticket type `sales_start` in the future rejects with "have not started" | BVA | F | 1 | Pass |
| F1-26 | Ticket type `sales_end` in the past rejects with "have ended" | BVA | F | 1 | Pass |
| F1-27 | Sales window exactly open (start = now) is accepted | BVA | U | 3 | ? |
| F1-28 | Ticket type belonging to a different event is rejected | NEG | F | 1 | Pass |
| F1-29 | Ticket type with remaining quantity 0 is rejected | BVA | F | 1 | Pass |
| F1-30 | Quantity equal to remaining stock accepted, stock becomes 0 | BVA | F | 1 | Pass |
| F1-31 | Quantity equal to remaining stock + 1 rejected, stock unchanged | BVA | F | 1 | Pass |
| F1-32 | Two ticket types in one order: both reserved, total = sum of price x quantity | EP | F | 1 | Pass |
| F1-33 | If the second ticket type fails (not enough stock) the first is not decremented (transaction rolled back) | NEG | F | 1 | Pass |
| F1-34 | `available_quantity` adds back cancelled tickets (unit test on `TicketType`) | EP | U | 2 | Pass |
| F1-35 | Free ticket (price 0): order total 0, behaviour of payment step is defined (no Paystack call with zero amount, or it fails cleanly) | BVA | F | 2 | DEFECT? |

#### A.1.3 Totals and discounts (purchase path)

| ID | Case | Technique | Level | Pri | Prediction before execution |
|---|---|---|---|---|---|
| F1-40 | No discount: `total_amount` = sum of ticket prices, `discount_amount` 0 | EP | F | 1 | Pass |
| F1-41 | 10% discount on GHS 100: discount 10, total 90, `subtotal_amount` 100 | EP | F | 1 | Pass |
| F1-42 | 0% discount: total unchanged | BVA | U | 2 | Pass |
| F1-43 | 100% discount: total 0 | BVA | F | 2 | ? |
| F1-44 | Percentage of 150 on a 100 order: `discount_amount` is capped at 100, `subtotal_amount` is 100, total 0 | BVA | F | 1 | DEFECT? (stored `discount_amount` and `subtotal_amount` are 150) |
| F1-45 | Fixed discount smaller than subtotal: total = subtotal - value | EP | F | 1 | Pass |
| F1-46 | Fixed discount larger than subtotal: discount capped at subtotal, total 0 | BVA | F | 1 | Pass |
| F1-47 | Percentage that gives a fractional amount (for example 33.33% of 10.00): stored value rounded to 2 decimals consistently | BVA | U | 2 | DEFECT? (no rounding in code) |
| F1-48 | Inactive discount rejected | NEG | F | 1 | Pass |
| F1-49 | Discount for another event rejected | NEG | F | 1 | Pass |
| F1-50 | Discount with `used_count` = `usage_limit` rejected (boundary: limit - 1 accepted, limit rejected) | BVA | F | 1 | Pass |
| F1-51 | Expired discount (`valid_until` in the past) rejected at purchase | DT | F | 1 | DEFECT? (`validateDiscount` checks dates, `purchase` does not) |
| F1-52 | Not-yet-valid discount (`valid_from` in the future) rejected at purchase | DT | F | 1 | DEFECT? |
| F1-53 | `used_count` increments by 1 after a successful order | EP | F | 2 | Pass |
| F1-54 | `used_count` is not consumed by an order that is later cleaned up unpaid | ST | F | 2 | DEFECT? (see F4-05) |
| F1-55 | Client cannot alter price: submit a manipulated price field, the order uses the database price | SEC | F | 1 | Pass |

#### A.1.4 Discount validation endpoint (`DiscountController::validateDiscount`)

| ID | Case | Technique | Level | Pri | Prediction before execution |
|---|---|---|---|---|---|
| F1-60 | Valid code returns `valid: true`, amount and total | EP | F | 1 | Pass |
| F1-61 | Unknown code returns 422 "Invalid discount code" | NEG | F | 1 | Pass |
| F1-62 | Code for another event returns 422 | NEG | F | 2 | Pass |
| F1-63 | Not yet active returns 422 | BVA | F | 2 | Pass |
| F1-64 | Expired returns 422 | BVA | F | 2 | Pass |
| F1-65 | Usage limit reached returns 422 | BVA | F | 2 | Pass |
| F1-66 | Negative `subtotal` rejected by validation | BVA | F | 3 | Pass |
| F1-67 | Percentage and fixed calculations (unit test the private method through the endpoint with a data set of 6 to 8 rows) | EP/BVA | F | 1 | ? |

#### A.1.5 Concurrency and stock

These are simulations (see section 2.2), not parallel runs.

| ID | Case | Technique | Level | Pri | Prediction before execution |
|---|---|---|---|---|---|
| F1-70 | Last ticket, two sequential requests: first succeeds, second is rejected, stock ends at 0 (proves the conditional decrement; the row lock is not contended inside one test) | CON | F | 1 | Pass |
| F1-71 | The database constraint on `ticket_types.quantity` blocks negative stock if code is bypassed | SEC | F | 3 | Pass |
| F1-72 | Discount with limit 1, two buyers: only one receives it (needs the fix to re-check under lock) | CON | F | 2 | DEFECT? |

---

### A.2 F2 Payment confirmation

Code: `PaymentController`, `PaystackService`, `PaystackController`, `PaymentInfo::recordTransaction`, `PaystackBalanceService`.

#### A.2.1 Initialise payment

Fake Paystack with `Http::fake(['api.paystack.co/*' => Http::response([...])])`.

| ID | Case | Technique | Level | Pri | Prediction before execution |
|---|---|---|---|---|---|
| F2-01 | Initialise for a pending order creates a `Payment` (status `pending`) and sets `payment_reference` on the order | EP | F | 1 | Pass |
| F2-02 | Fee split: 5% charge on GHS 100 sends `amount` 10000 and `transaction_charge` 500 (pesewas) | BVA | U | 1 | Pass |
| F2-03 | Fee split with fractional result (for example 3.5% of GHS 33.33): transaction charge is an integer number of pesewas | BVA | U | 1 | DEFECT? (float sent to Paystack) |
| F2-04 | Already `confirmed` order returns 400 "already been paid" | ST | F | 1 | Pass |
| F2-05 | Organiser without payment info or subaccount code returns 400 | NEG | F | 1 | Pass |
| F2-06 | Paystack returns an error: response `success: false`, no `Payment` row left behind | NEG | F | 1 | Pass |
| F2-07 | Paystack timeout or exception handled, generic error, order untouched | NEG | F | 2 | Pass |
| F2-08 | Calling initialise twice in the same second for the same order gives two different references | IDEM | U | 2 | DEFECT? (reference uses `time()`) |
| F2-09 | `bearer` taken from the organiser's `transaction_fee_payer` | EP | U | 3 | Pass |

#### A.2.2 Callback (`payment/callback`)

| ID | Case | Technique | Level | Pri | Prediction before execution |
|---|---|---|---|---|---|
| F2-10 | No `reference` query parameter redirects to failed page | NEG | F | 1 | Pass |
| F2-11 | Verification call fails: redirect to failed page, order unchanged | NEG | F | 1 | Pass |
| F2-12 | Paystack says status `failed` or `abandoned`: redirect to failed page, order stays `pending` | DT | F | 1 | Pass |
| F2-13 | Paystack says `success`: order `confirmed`, `purchased_at` set, payment `completed`, tickets `active`, redirect to success page | ST | F | 1 | Pass |
| F2-14 | Success page returns 404 for an order that is not confirmed | NEG | F | 1 | Pass |
| F2-15 | One `purchases` row per ticket after confirmation (order of 3 tickets at the same price gives 3 rows) | EP | F | 1 | DEFECT? (duplicate check matches on amount only, so same-price tickets collapse into one row) |
| F2-16 | Paid amount from Paystack must equal order total: a success response for a lower amount does not confirm the order | DT | F | 1 | DEFECT? (no amount comparison in `processSuccessfulPayment`) |
| F2-17 | Reference that belongs to a different order than the one in metadata is rejected | SEC | F | 2 | DEFECT? |
| F2-18 | Callback replayed twice: second call does not duplicate purchases, balances or emails | IDEM | F | 1 | ? |
| F2-19 | Confirmation email queued once, SMS notification queued only when a phone number exists (`Mail::fake`, `Notification::fake`) | EP | F | 2 | Pass |

#### A.2.3 Webhook (`POST paystack/webhook`)

Decision table over signature, reference and payment status:

| ID | Signature | Reference | Payment status | Expected | Pri | Prediction before execution |
|---|---|---|---|---|---|---|
| F2-30 | Valid | Known | pending | 200, payment `completed`, order `confirmed` | 1 | Pass |
| F2-31 | Valid | Known | completed | 200, no second processing, balances unchanged | 1 | Pass |
| F2-32 | Valid | Unknown | n/a | 404 "Payment not found" | 1 | Pass |
| F2-33 | Valid | Missing from payload | n/a | 400 "Missing reference" | 1 | Pass |
| F2-34 | Invalid (wrong secret) | Known | pending | 400, nothing changes | 1 | Pass |
| F2-35 | Invalid (payload tampered after signing) | Known | pending | 400 | 1 | Pass |
| F2-36 | Header missing | Known | pending | 400, not a server error | 1 | DEFECT? (`null` passed to a `string` parameter) |
| F2-37 | Empty string signature | Known | pending | 400 | 2 | Pass |
| F2-38 | Valid, unhandled event type (for example `invoice.create`) | n/a | n/a | 200 "Event not handled" | 2 | Pass |

Further webhook cases:

| ID | Case | Technique | Level | Pri | Prediction before execution |
|---|---|---|---|---|---|
| F2-40 | Two identical `charge.success` webhooks: balances credited once (simulation) | IDEM | F | 1 | DEFECT? (status check and update are not in one locked transaction) |
| F2-41 | `charge.success` with paid amount not equal to order total does not confirm the order | DT | F | 1 | DEFECT? (same as F2-16) |
| F2-42 | `charge.dispute.create` marks the payment/order as disputed and does not delete tickets (read `handleChargeDispute` first, then fix the expected result) | ST | F | 2 | ? |
| F2-43 | `transfer.success` and `transfer.failed` update the matching record (read handlers first) | ST | F | 3 | ? |
| F2-44 | Webhook route is exempt from CSRF but still rejects requests without a signature | SEC | F | 1 | Pass |
| F2-45 | Webhook for an order whose tickets were already cleaned up unpaid: defined behaviour (payment recorded, tickets restored, or refund flagged) | ST | F | 2 | DEFECT? |

#### A.2.4 Signature unit tests (`PaystackService::verifyWebhookSignature`)

| ID | Case | Technique | Level | Pri | Prediction before execution |
|---|---|---|---|---|---|
| F2-50 | Correct HMAC-SHA512 of payload returns true | EP | U | 1 | Pass |
| F2-51 | One changed byte in payload returns false | BVA | U | 1 | Pass |
| F2-52 | Same payload, signature in uppercase hex | EP | U | 3 | ? |
| F2-53 | Empty payload with matching signature | BVA | U | 3 | Pass |
| F2-54 | Comparison is constant time (`hash_equals` is used; assert by reading the code, not by test) | SEC | n/a | 3 | Note only |

#### A.2.5 Balances (`PaymentInfo::recordTransaction`, `PaystackBalanceService`)

Read `PaystackBalanceService::calculateSeparateBalances` and `calculateSingleTransactionBalance` before writing these; adjust expected values to the documented intent.

| ID | Case | Technique | Level | Pri | Prediction before execution |
|---|---|---|---|---|---|
| F2-60 | With `fees_split` from Paystack, subaccount and integration amounts are taken from it (pesewas divided by 100) | EP | U | 1 | Pass |
| F2-61 | Without `fees_split`, split uses `percentage_charge`: 5% of 200 gives system 10, subaccount 190 | EP | U | 1 | Pass |
| F2-62 | Subaccount plus system amount equals the transaction amount | EP | U | 1 | ? |
| F2-63 | Balances accumulate over two transactions; counters (`total`, `successful`) increment | EP | U | 1 | Pass |
| F2-64 | `percentage_charge` 0 and 100 (boundaries) | BVA | U | 2 | ? |
| F2-65 | Status `pending` increments the pending counter, not successful | DT | U | 2 | Pass |
| F2-66 | Float accuracy: 0.1 + 0.2 style amounts do not drift after 100 recorded transactions | BVA | U | 2 | DEFECT? (floats) |
| F2-67 | `calculateSeparateBalances` over a list of transactions gives the same totals as summing `recordTransaction` | EP | U | 2 | ? |

#### A.2.6 Bank account verification (`PaystackController::verifyAccount`)

| ID | Case | Technique | Level | Pri | Prediction before execution |
|---|---|---|---|---|---|
| F2-70 | Valid account number returns 200 with the account name (Paystack faked); the rule `min:80\|max:17` can never pass | EP | F | 1 | DEFECT? (any account number returns 422) |

---

### A.3 F3 Ticket validation and entry

Code: `TicketController` (`verify`, `verifyByNumber`, `markAsUsed`, `scanQr`, `validateQrData`), `Ticket::markAsUsed`, `CheckInController`.

#### A.3.1 State machine

Ticket statuses seen in code: `pending`, `active`, `used`, `cancelled`. Test every pair:

| ID | From | Action | Expected | Pri | Prediction before execution |
|---|---|---|---|---|---|
| F3-01 | active | mark used (authorised organiser) | `used`, `used_at` set | 1 | Pass |
| F3-02 | used | mark used again | 400, `used_at` unchanged | 1 | Pass |
| F3-03 | pending | mark used | 400 | 1 | Pass |
| F3-04 | cancelled | mark used | 400 | 1 | Pass |
| F3-05 | active | scan (`scanQr`) | success, status reported as active | 1 | Pass |
| F3-06 | used | scan | reports as already used (clear message, not a plain success) | 1 | DEFECT? (scan formats the ticket but does not check status) |
| F3-07 | pending (unpaid) | scan | rejected or flagged not valid | 1 | DEFECT? |
| F3-08 | cancelled | scan | rejected or flagged not valid | 1 | DEFECT? |
| F3-09 | `Ticket::markAsUsed` also keeps `is_used` consistent with `status` | EP | U | 2 | DEFECT? (only `status` and `used_at` are set) |

#### A.3.2 Authorisation (multi-tenancy)

| ID | Case | Technique | Level | Pri | Prediction before execution |
|---|---|---|---|---|---|
| F3-10 | `markAsUsed` unauthenticated returns 401 | SEC | F | 1 | Pass |
| F3-11 | `markAsUsed` by a user of another organiser returns 403, ticket unchanged | SEC | F | 1 | Pass |
| F3-12 | `markAsUsed` by a user belonging to two organisers works for either one's event | EP | F | 2 | Pass |
| F3-13 | `verifyByNumber` by a user of another organiser returns 403 | SEC | F | 1 | Pass |
| F3-14 | `verifyByNumber` unknown ticket returns 404 | NEG | F | 1 | Pass |
| F3-15 | `verifyByNumber` is case-insensitive on the ticket number | EP | F | 2 | Pass |
| F3-16 | `scanQr` without authentication returns attendee name, email and price | SEC | F | 1 | DEFECT? (route is public; personal data exposure, discuss in the security section) |
| F3-17 | Public `verify-ticket/{ticketNumber}` page shows attendee email to anonymous visitors | SEC | F | 2 | DEFECT? |
| F3-18 | `getEventTickets` for an organiser the user does not belong to returns 403 | SEC | F | 1 | Pass |
| F3-19 | `getEventTickets` where the event belongs to a different organiser returns 400 | SEC | F | 1 | Pass |

#### A.3.3 QR and ticket number handling

| ID | Case | Technique | Level | Pri | Prediction before execution |
|---|---|---|---|---|---|
| F3-20 | `ticket_number` only, lowercase input is upper-cased and found | EP | F | 1 | Pass |
| F3-21 | `qr_data` as JSON with matching details succeeds | EP | F | 1 | Pass |
| F3-22 | `qr_data` JSON with a different `attendee_name` returns 400 "does not match" | DT | F | 1 | Pass |
| F3-23 | `qr_data` JSON with a different price returns 400 | DT | F | 2 | Pass |
| F3-24 | `qr_data` plain text (not JSON) is treated as a ticket number | EP | F | 2 | Pass |
| F3-25 | `qr_data` JSON missing `attendee_name` or `price` key: clean 400, not a server error | NEG | F | 1 | DEFECT? (undefined array key) |
| F3-26 | Neither `qr_data` nor `ticket_number` returns 422 | NEG | F | 2 | Pass |
| F3-27 | Ticket number with spaces is trimmed | BVA | F | 3 | Pass |
| F3-28 | Generated ticket numbers are unique across 1,000 tickets (unit test on the model boot logic) | EP | U | 2 | ? |

#### A.3.4 Duplicate entry and concurrency

| ID | Case | Technique | Level | Pri | Prediction before execution |
|---|---|---|---|---|---|
| F3-30 | Two stale copies of one active ticket: exactly one `claimEntry()` succeeds (section 2.2) | CON | U | 1 | DEFECT? (read status, then plain `update()`) |
| F3-31 | `used_at` keeps the first claim's timestamp | CON | U | 1 | DEFECT? |
| F3-32 | Same check through the organiser-scoped route `PUT organizers/{o}/events/{e}/tickets/{t}` (`markTicketAsUsedForEvent`) | CON | C | 2 | DEFECT? |
| F3-33 | Ticket from event A cannot be checked in under event B route (event mismatch) | SEC | F | 1 | ? |

#### A.3.5 Check-in page and statistics

| ID | Case | Technique | Level | Pri | Prediction before execution |
|---|---|---|---|---|---|
| F3-40 | `attendee-checkin` redirects guests to login | SEC | F | 1 | Pass |
| F3-41 | Authenticated organiser with `?event_id=` of own event gets `selectedEvent` | EP | F | 2 | Pass |
| F3-42 | `event_id` of another organiser's event gives `selectedEvent` null | SEC | F | 1 | Pass |
| F3-43 | `getEventStatistics` counts: total, used, active, cancelled match fixtures | EP | F | 2 | ? |
| F3-44 | `getTicketScanStatistics` and `getTicketEntryHistory` return only that event's data | SEC | F | 3 | ? |

---

### A.4 Stock release and clean-up (F4 prefix) Order clean-up and stock integrity

Code: `CleanupIncompleteOrders` (`php artisan orders:cleanup-incomplete`).


| ID | Case | Technique | Level | Pri | Prediction before execution |
|---|---|---|---|---|---|
| F4-01 | Pending order older than the timeout is deleted with its tickets and pending payment | ST | F | 1 | ? |
| F4-02 | Ticket type quantity is restored by the number of deleted tickets | ST | F | 1 | ? |
| F4-03 | Pending order younger than the timeout is untouched (boundary: 14m59s kept, 15m01s removed) | BVA | F | 1 | ? |
| F4-04 | Confirmed order is never deleted | ST | F | 1 | ? |
| F4-05 | Discount `used_count` is restored when its unpaid order is deleted | ST | F | 2 | DEFECT? |
| F4-06 | `--dry-run` changes nothing | EP | F | 2 | ? |
| F4-07 | Race: order confirmed by webhook at the moment clean-up runs, ticket kept | CON | C | 2 | DEFECT? |
| F4-08 | Command is scheduled (check `bootstrap/app.php` schedule or `routes/console.php`); if not, that is a finding | n/a | n/a | 2 | ? |

---

### A.5 E Browser end-to-end tests (Pest 4 browser plugin)

Location: `tests/Browser`. These drive a real headless browser (Playwright) against the application's own HTTP server, in the same process as the test, so factories and `DatabaseTransactions` still apply. Paystack is faked with `Http::fake`; its `authorization_url` points back at the application's callback route, so the whole purchase, payment and confirmation path runs without an external page. Server-side rendering is disabled in the test (`config(['inertia.ssr.enabled' => false])`) because the SSR call would otherwise go through the faked HTTP client.

| ID | Case | Level | Pri | Status |
|---|---|---|---|---|
| E-01 | Guest selects two tickets, enters buyer and attendee details, "pays" (faked), sees the confirmation; order confirmed, two active tickets, stock reduced by 2, payment completed | E2E | 1 | Written (`TicketPurchaseFlowTest`) |
| E-02 | Valid percentage discount code applied on the event page lowers the total shown | E2E | 2 | Written |
| E-03 | Unknown discount code is rejected and the full price stays | E2E | 2 | Written |
| E-04 | Reaching the buyer form does not create an order or reserve stock | E2E | 2 | Written |
| E-05 | Gate staff signs in, opens the check-in page, scans a valid ticket (admitted), scans it again (rejected) | E2E | 1 | To write |
| E-06 | Sold-out ticket type cannot be purchased from the page | E2E | 3 | To write |

Run: `php artisan test tests/Browser`. First-time setup: `composer require pestphp/pest-plugin-browser --dev`, `npm install --save-dev playwright`, `npx playwright install chromium`. In CI add the same Playwright install step and cache `~/.cache/ms-playwright`.

---

### A.6 M Manual run against Paystack test mode


Setup (do once):
2. Expose the local app with a public HTTPS tunnel (for example Cloudflare Tunnel or ngrok) and set `APP_URL` to it. Or use the staging deployment.
3. Under Settings, API Keys & Webhooks (test mode), set the **Test Callback URL** to `{APP_URL}/payment/callback` and the **Test Webhook URL** to `{APP_URL}/paystack/webhook`.
4. Create an organiser payment-info record with a valid test subaccount code.

| ID | Case | Expected | Evidence |
|---|---|---|---|
| M-01 | Buy a ticket with a Paystack test card on the real hosted checkout (use the test card from Paystack's documentation) | Redirect back to the callback, order confirmed, tickets active | Screenshot of success page, order row, Paystack test transaction |
| M-02 | Confirm the signed webhook arrived (dashboard webhook log shows 200) | Webhook accepted, order not processed twice | Dashboard log screenshot, application log |
| M-03 | Abandon checkout, wait 15 minutes, run `php artisan orders:cleanup-incomplete` | Pending order and tickets removed, stock restored | Command output, row counts |
| M-04 | Replay the webhook from the dashboard | No duplicate purchase records or balance changes | Before and after balances |
| M-05 | Failed card (Paystack test card for declined payments) | Order stays pending, buyer sees the failure page | Screenshot |


---

### A.7 X Cross-cutting security

| ID | Case | Technique | Level | Pri | Prediction before execution |
|---|---|---|---|---|---|
| X-01 | Mass assignment: posting `status=confirmed` or `total_amount=0` on purchase has no effect | SEC | F | 1 | Pass |
| X-02 | Guest order lookup (`api/orders/search`) with the wrong email returns the same response as an unknown order (no enumeration) | SEC | F | 2 | ? |
| X-03 | `orders/{orderNumber}` page does not show another buyer's data without the matching email or token | SEC | F | 2 | DEFECT? |
| X-04 | Rate limiting on purchase, discount validation, scan endpoints | SEC | F | 3 | DEFECT? |
| X-05 | SQL injection strings in `ticket_number`, discount `code`, order search do not break queries (Eloquent bindings) | SEC | F | 2 | Pass |
| X-06 | `SystemAdminMiddleware` blocks non-admins, allows admins | SEC | F | 2 | ? |
| X-07 | Honeypot on every public form that still exists (purchase, order search, payment info) | SEC | F | 2 | ? |
| X-08 | Webhook route is the only CSRF-exempt route | SEC | F | 3 | Pass |

---

### A.8 P Performance and scalability

The marking rubric asks for performance metrics and benchmarking. Keep these small and repeatable.

| ID | Case | Metric | Target | Pri |
|---|---|---|---|---|
| P-01 | Purchase of 10 tickets in one order | Query count (`DB::enableQueryLog`) | Record the number; flag if it grows with ticket count (N+1) | 1 |
| P-02 | `getEventTickets` with 500 tickets | Query count and response time | Constant query count | 1 |
| P-03 | `scanQr` response time over 100 sequential scans | Median and p95 milliseconds | Median under 200 ms locally | 1 |
| P-04 | `markAsUsed` over 100 distinct tickets | Median milliseconds | Record | 2 |
| P-05 | Webhook processing time for one `charge.success` | Milliseconds | Record | 2 |
| P-06 | Oversell: N sequential buyers for stock M (M < N) | Oversold count | 0 | 1 |

Record results in a table: metric, before fix, after fix, environment (PHP 8.4, MySQL version, local machine). State that local figures are indicative only.

---

### A.9 D Existing failing tests (6 on a clean test database)

The baseline suite (clean `suptix_test` database, Pest 4) has 62 tests, 56 passing and 6 failing. Run against the shared development database, 5 more fail with `RoleAlreadyExists`, so always test on `suptix_test`: `DB_DATABASE=suptix_test php artisan test`. Pest 4 / PHPUnit 12 ignores `/** @test */`; use `#[Test]` or `it()`. Each failure is checked first for a real defect and then fixed. Re-run `php artisan test` to confirm the current count.

| Group | Count | Likely cause (from run output) | Action |
|---|---|---|---|
| `PaymentInfoRoleAssignmentTest` | 4 | Job not pushed, or other jobs pushed unexpectedly (`SendQueuedNotifications`) | Decide what the observer should dispatch; adjust test or code; log as a defect if the code is wrong |
| `PaymentInfoNotificationTest` | 1 | `sms` channel missing from the `via()` array | Check `NewPaymentInfoSubmissionNotification::via`; fix the code or the test |
| `PaystackSubaccountSyncTest` | 1 | `hasRecentSyncError()` is false after a failed sync | Payments area; likely a real defect in error recording |


## Appendix B. Defect log template

| ID | Flow | Defect | Exposed by | Root cause | Fix (commit) | Severity |
|---|---|---|---|---|---|---|

## Appendix C. Metrics table (before and after)

| Measure | Baseline (`da57882`) | Final |
|---|---|---|
| Tests (passing / failing) | 62 (56 / 6) | |
| Line coverage, whole application | 646 of 4,958 (13.0%) | |
| Method coverage, whole application | 44 of 367 (12.0%) | |
| Line coverage, in-scope classes | 9 of 1,885 (0.5%) | |
| Mutation score | Not measured | |
| PHPStan errors | Not run | |
| Pint files needing changes (whole repository) | 133 | |
| ESLint errors (whole repository) | 2,381 | |
| Defects found / fixed | 0 | |
