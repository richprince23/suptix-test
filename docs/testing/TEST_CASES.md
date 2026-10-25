# Test case catalogue

54 cases for the three in-scope flows: F1 purchase and pricing, F2 payment confirmation, F3 ticket validation and entry, plus four cross-cutting checks (X). Each case is traced to the automated tests that check it. Results are from the run recorded in [TEST_RESULTS.md](TEST_RESULTS.md).

**Priority.** 1 = release-blocking (a failure blocks sign-off). 2 = high priority. 3 = normal.

**Result.** Pass = every mapped test passes. Pass (sim.) = passes in a simulated race, because transactions hide uncommitted rows from other processes, so a true parallel race is not reproduced. Partial = only part of the case is covered. Not tested = no automated test exists.

**Automated test IDs** are the IDs in the test names (for example `it('F2-16 does not confirm the order when the paid amount is lower...')` in `tests/Feature/Payment/PaymentCallbackTest.php`). They are numbered separately from the catalogue IDs.

Mobile API v2, Filament Admin, Providers, Console and Exceptions are out of scope.

## F1 Purchase and pricing

| ID | Pri | Scenario | Expected result | Automated tests | Result |
|---|---|---|---|---|---|
| F1-01 | 2 | Valid purchase | Order created with correct items and total | F1-01 | Pass |
| F1-02 | 2 | Quantity boundaries | Zero, negative and over-limit quantities rejected; no order or stock change | F1-05 to F1-08 | Pass |
| F1-03 | 2 | Price and total | Total matches price times quantity | F1-32, F1-40 | Pass |
| F1-04 | 2 | Discount applied | Discount and payable total match the formula | F1-41, F1-45 | Pass |
| F1-05 | 2 | Expired discount | Rejected; total unchanged | F1-51, F1-64 | Pass |
| F1-06 | 2 | Exhausted discount | Rejected at the usage limit; count never exceeds the limit | F1-50, F1-65 | Pass |
| F1-07 | 2 | Ineligible discount | Rejected when it belongs to another event or is inactive | F1-49, F1-62 | Pass |
| F1-08 | 2 | Discount boundaries | Cap, 100% and rounding outcomes match the rules | F1-43 to F1-47, F1-67 | Pass |
| F1-09 | 1 | Sold-out ticket | Purchase rejected; no sale beyond stock | F1-29 | Pass |
| F1-10 | 1 | Last available ticket | One unit sold; stock ends at zero | F1-30 | Pass |
| F1-11 | 1 | Concurrent purchases | No overselling when buyers compete for the last units | F1-70, P-06 | Pass (sim.) |
| F1-12 | 2 | Duplicate submit | No duplicate logical order | None | Not tested |
| F1-13 | 2 | Invalid references | Safe validation response; no order or side effect | F1-02 to F1-04, F1-12, F1-28 | Pass |
| F1-14 | 2 | Order and ticket consistency | Order quantity equals created tickets | F1-01, F1-14, F1-32 | Pass |
| F1-15 | 2 | Stock after failure | Stock restored or untouched per the reservation policy | F1-33, F4-01, F4-02 | Pass |
| F1-16 | 2 | Fee and rounding | Fee and total match the approved calculation | F1-47, F2-02, F2-62 | Pass |

## F2 Payment confirmation

| ID | Pri | Scenario | Expected result | Automated tests | Result |
|---|---|---|---|---|---|
| F2-01 | 1 | Valid verified payment | Order reaches the paid state once | F2-13, F2-30 | Pass |
| F2-02 | 1 | Invalid webhook signature | Rejected; no order, payment or ticket change | F2-34, F2-35, F2-37 | Pass |
| F2-03 | 1 | Duplicate success webhook | Idempotent; no duplicate tickets or credits | F2-31, F2-40 | Pass |
| F2-04 | 1 | Amount mismatch | Payment not accepted as successful | F2-16, F2-41 | Pass |
| F2-05 | 1 | Reference mismatch | Payment not confirmed | F2-17, F2-32 | Pass |
| F2-06 | 2 | Currency mismatch | Rejected or flagged | None | Not tested |
| F2-07 | 1 | Failed payment | Order not paid; no valid ticket issued | F2-11, F2-12 | Pass |
| F2-08 | 2 | Pending payment | Order stays in the non-paid state | F2-12, F2-65 | Pass |
| F2-09 | 1 | Callback without verification | Return URL alone cannot confirm payment | F2-11, F2-14 | Pass |
| F2-10 | 2 | Provider timeout or error | Safe failure; no false paid state | F2-96, F2-109 | Pass |
| F2-11 | 2 | Out-of-order confirmation | Final state consistent whatever the arrival order | F2-18, F2-31 | Partial |
| F2-12 | 1 | Ticket issue after payment | Correct number of tickets, none early | F2-13, F2-15 | Pass |
| F2-13 | 2 | Ticket delivery dispatch | Confirmation sent to the right recipient | F2-19 | Pass |
| F2-14 | 2 | Repeated verification | No duplicate transition or tickets | F2-18, F2-106 | Pass |
| F2-15 | 2 | Unknown reference | Safe rejection; no unrelated order changed | F2-32 | Pass |
| F2-16 | 2 | Balance service responses | Valid, invalid and unavailable responses handled; no secret leaked | F2-80 to F2-88, F2-123 | Pass |
| F2-17 | 2 | Initialisation failure | No false paid state; no payment left behind | F2-06, F2-07, F2-104 | Pass |
| F2-18 | 2 | Late success after cancellation | Explicit late-payment policy applied | None | Not tested |

## F3 Ticket validation and entry

| ID | Pri | Scenario | Expected result | Automated tests | Result |
|---|---|---|---|---|---|
| F3-01 | 1 | Valid unused ticket | Accepted; check-in recorded | F3-01, F3-05, F3-34 | Pass |
| F3-02 | 1 | Duplicate scan | Second scan refused; no second entry | F3-02, F3-30, F3-31 | Pass |
| F3-03 | 1 | Invalid QR | Rejected; no state change | F3-22, F3-23, F3-25, F3-34 | Pass |
| F3-04 | 1 | Wrong-event ticket | Rejected; ticket not marked used | F3-33, F3-36 | Pass |
| F3-05 | 2 | Already-used ticket | Refused or reported as used | F3-02, F3-06 | Pass |
| F3-06 | 2 | Cancelled or unpaid ticket | Refused | F3-03, F3-04, F3-08 | Pass |
| F3-07 | 1 | Cross-organiser access | Denied; ticket and check-in unchanged | F3-11, F3-13, F3-35 | Pass |
| F3-08 | 2 | Unauthenticated request | Rejected; no state change | F3-10, F3-40 | Pass |
| F3-09 | 2 | Insufficient permission | Denied; no change | F3-11, F3-47 | Pass |
| F3-10 | 1 | Simultaneous scans | At most one succeeds | F3-30, F3-31 | Pass (sim.) |
| F3-11 | 2 | Manual ticket number | Valid number found; invalid or ambiguous safely refused | F3-14, F3-15, F3-20, F3-27 | Pass |
| F3-12 | 2 | `markAsUsed` transition | State changes once with details saved | F3-01, F3-63 | Pass |
| F3-13 | 2 | Repeated `markAsUsed` | No duplicate entry or changed timestamp | F3-02 | Pass |
| F3-14 | 2 | Missing input | Validation response; no server error | F3-26 | Pass |
| F3-15 | 2 | Check-in data consistency | Only valid, authorised check-ins change records and counts | F3-43, F3-45, F3-46 | Pass |
| F3-16 | 3 | Error response privacy | No unrelated attendee data in errors | F3-16, F3-17, F3-36 | Pass |

## Cross-cutting

| ID | Pri | Scenario | Expected result | Automated tests | Result |
|---|---|---|---|---|---|
| X-01 | 1 | Retry is duplicate-safe | State stays consistent after a retry | F2-18, F2-31, F2-40 | Pass |
| X-02 | 2 | Failure leaves no partial state | No contradictory payment, order or ticket state | F1-33, F2-06, F2-07 | Partial |
| X-03 | 2 | Error logs useful and free of secrets | Diagnostics available; no secrets logged | None | Not tested |
| X-04 | 2 | Coverage exclusions checked | Exclusions match `phpunit.xml`; coverage described as in-scope only | `phpunit.xml` review, coverage report | Pass |

## Summary

| Flow | Cases | Priority 1 | Priority 2 | Priority 3 | Pass | Partial | Not tested |
|---|---:|---:|---:|---:|---:|---:|---:|
| F1 | 16 | 3 | 13 | 0 | 15 | 0 | 1 |
| F2 | 18 | 8 | 10 | 0 | 15 | 1 | 2 |
| F3 | 16 | 6 | 9 | 1 | 16 | 0 | 0 |
| Cross-cutting | 4 | 1 | 3 | 0 | 2 | 1 | 1 |
| Total | 54 | 18 | 35 | 1 | 48 | 2 | 4 |

All 18 Priority 1 cases pass. "Pass" includes the two simulated-race cases. Four Priority 2 cases (F1-12, F2-06, F2-18, X-03) have no test and two (F2-11, X-02) are partial; they are recorded as sign-off exceptions, not passes.
