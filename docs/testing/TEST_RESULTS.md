# Test results

Results of the case-study test run on the three in-scope flows (F1 purchase and pricing, F2 payment confirmation, F3 ticket validation and entry). The cases themselves, with their traceability to automated tests, are in [TEST_CASES.md](TEST_CASES.md).

## Run record

| Item | Value |
|---|---|
| Date | 25 October 2026 (full run after all fixes) |
| Branch and commit | `original-scope`, `02b7156`, which restores the case-study tests from `tests/_disabled` and holds the `EventDetails.vue` import fix (DEF-29) and the test fixes below. The final run was made on this tree just before it was committed. |
| Environment | PHP 8.4.15, Laravel 12.70.0, Pest 4.7.8, MySQL 8.4.5 (database `suptix_test`), PCOV |
| Provider mode | Fakes; test keys only |
| Command | `php -d pcov.enabled=1 -d pcov.directory=app ./vendor/bin/pest --coverage-clover=clover.xml` (all suites: Unit, Feature, Browser) |

The baseline is the inherited suite (the case-study tests moved to `tests/_disabled`, so no browser tests) run on 24 October 2026 with the Unit and Feature suites and the same coverage scope.

## Summary

| Measure | Baseline | After |
|---|---:|---:|
| Tests | 135 | 423 |
| Passed | 114 | 422 |
| Failed | 21 | 0 |
| Skipped | 0 | 1 |
| In-scope line coverage | 0.5% (9 / 1,882) | 91.4% (1,720 / 1,881) |
| Run time | 21.0 s | 79.5 s |

The baseline had 21 failing tests, all inherited and outside the three flows. They are fixed in the final run (see below), so the final run has no failures. The after figure covers all three suites: Unit, Feature and Browser (422 passed, 1 skipped, 2,013 assertions). Skipped tests are not counted as passed.

## New tests by group

| Group | New tests |
|---|---:|
| F1 purchase, discounts, purchase pages | 76 |
| F2 initialisation, callback, webhook, balances, signature unit tests | 87 |
| F3 ticket entry and organiser endpoints | 50 |
| Contracts, models, clean-up, performance | 67 |
| Browser (7 run, 1 skipped) | 8 |
| Total | 288 |

## In-scope line coverage by class

| Class | Covered / statements | Coverage |
|---|---:|---:|
| `CheckInController` | 23 / 23 | 100.0% |
| `DiscountController` | 51 / 51 | 100.0% |
| `PaystackController` | 44 / 44 | 100.0% |
| `Order` | 20 / 20 | 100.0% |
| `Payment` | 2 / 2 | 100.0% |
| `TicketType` | 30 / 30 | 100.0% |
| `TicketPurchaseController` | 194 / 199 | 97.5% |
| `Ticket` | 19 / 20 | 95.0% |
| `PaystackBalanceService` | 239 / 256 | 93.4% |
| `PaystackService` | 451 / 484 | 93.2% |
| `PaymentController` | 180 / 202 | 89.1% |
| `Discount` | 18 / 21 | 85.7% |
| `TicketController` | 449 / 529 | 84.9% |
| Total | 1,720 / 1,881 | 91.4% |

## The 21 baseline failures and how they were fixed

All 21 were inherited tests outside the three flows. Each was diagnosed before it was changed. Before the fixes, the nine files below ran with 21 failed and 24 passed (`evidence/failing-tests-before-fixes.txt`). After the fixes they pass, and the full run has no failures (`evidence/full-run-after-fixes.txt`).

| Test file | Failing | Cause | Fix |
|---|---:|---|---|
| `Auth/AuthenticationTest` | 3 | Tests were written for the starter-kit login form, but the application sends `/login` to the Filament admin login and does not load `routes/auth.php` | Tests rewritten to check the current design: `/login` redirects to Filament, the Filament login renders, the starter-kit POST is refused (405) |
| `Auth/EmailVerificationTest` | 3 | Starter-kit verification routes are not loaded (404, `verification.verify` undefined) | Rewritten: the routes are not served |
| `Auth/PasswordConfirmationTest` | 3 | Starter-kit confirm-password routes are not loaded (404) | Rewritten: the routes are not served |
| `Auth/PasswordResetTest` | 4 | Starter-kit reset routes are not loaded (404); Filament handles reset | Rewritten: routes not served, Filament reset screen renders |
| `Auth/RegistrationTest` | 2 | `/register` forwards to the Filament registration | Rewritten: redirect, Filament registration renders, starter-kit POST refused |
| `DashboardTest` | 2 | The `dashboard` route is commented out in `routes/web.php` (404) | Rewritten: the route is not served to guests or signed-in users |
| `EventPartnerTest` | 2 | The model deletes logos from the default disk, the tests faked the `public` disk; the result depended on `FILESYSTEM_DISK` | Tests set the default disk to `public` before faking it |
| `RsvpHoneypotTest` | 1 | The test event was not an RSVP event (`is_rsvp` false), so no credit account existed and the booking returned 404 | Test event created with `is_rsvp => true` |
| `RsvpWaitlistTest` | 1 | The controller sends the waitlist mail immediately (`sendNow`), but the test asserted it was queued | Test asserts the mail was sent |

The authentication and dashboard tests (17) were changed to describe what the application does now, not to restore starter-kit routes that the product has switched off on purpose. No application code was changed for these 21.

## Browser tests

| Test file | Tests | Result |
|---|---:|---|
| `GateCheckInFlowTest` | 2 | Passed |
| `TicketPurchaseFlowTest` | 5 | Passed |
| `PaystackTestModeCheckoutTest` | 1 | Skipped; set `PAYSTACK_LIVE=1` to run against the Paystack test environment |

The purchase-flow tests first did not complete. The event page stayed blank because `EventDetails.vue` calls `watch` without importing it (`ReferenceError: watch is not defined`, found by loading the page in a browser and reading its console). After adding `watch` to the import and rebuilding the assets with `npm run build`, all 7 browser tests passed (43 assertions, 15.9 s on their own). This is defect DEF-29 in `docs/defects/DEFECT_LOG.md`. Chromium was reinstalled with `npx playwright install chromium` after the browser cache was lost.

## Performance (recorded run, development laptop, indicative)

| Case | Before fixes | After fixes |
|---|---|---|
| P-02 tickets listed for an event with 500 tickets | 1,007 queries, 997 ms | 9 queries, 75 to 78 ms |
| P-03 scan of a ticket, 100 requests (95th percentile) | 10 to 11 ms | 10 to 11 ms |
| P-06 20 buyers compete for 5 tickets | 5 sold, 0 oversold | 5 sold, 0 oversold |

Source: `docs/final/performance.md`. These were not repeated for this run.

## Mutation testing (recorded run, not repeated)

1,432 of 1,935 mutants killed (74.0%) across the 13 in-scope classes, against a 60% target. Source: `docs/final/MUTATION.md`. A GitHub Actions run killed 1,417 of 1,935 (73.23%).

## Sign-off

| Check | Status |
|---|---|
| F1, F2 and F3 Priority 1 cases pass | Done |
| Priority 2 cases pass or exceptions documented | Exceptions: 4 not tested, 2 partial (see TEST_CASES.md) |
| No Critical or High defect open | Done (29 defects fixed, see `docs/defects/DEFECT_LOG.md`) |
| `phpunit.xml` exclusions checked against the scope | Done |
| Coverage reported as in-scope coverage only | Done |
| Isolated database, no production credentials | Done |
| Commit, evidence and limits recorded | Done |

Recommendation: **Conditional go**. The conditions are the six Priority 2 exceptions (four cases not tested, two partial) and the lack of true parallel tests for simultaneous webhooks and scans.

## Evidence

| File | What it shows |
|---|---|
| [evidence/full-run-after-fixes.txt](evidence/full-run-after-fixes.txt) | Full output of the final run: 422 passed, 1 skipped, 0 failed, 2,013 assertions, 79.5 s |
| [evidence/failing-tests-before-fixes.txt](evidence/failing-tests-before-fixes.txt) | The nine files with the 21 failures and their error messages, before the test fixes |
| [evidence/in-scope-coverage.txt](evidence/in-scope-coverage.txt) | In-scope line coverage by class from the final run: 1,720 of 1,881 (91.4%) |
