# Test Strategy: Suptix core ticketing flows

| | |
|---|---|
| Module | 7020SCN Software Quality Assurance and Testing |
| System under test | Suptix (Laravel 12, Vue 3 and Inertia event ticketing platform) |
| Author | Richard Kweku Aikins (17626923) |
| Status | Version 1.0, written before the main test implementation |
| Companion document | [TEST_PLAN.md](TEST_PLAN.md) |

## 1. Purpose

This document sets out how quality is assured for the parts of Suptix on which revenue and trust depend. It states what is tested, at which level, with which technique, in which environment, to what standard, and how results are reported. The detailed cases, schedule and acceptance thresholds are in the test plan.

## 2. Scope

Three business-critical flows are in scope.

| Flow | Concern | Main classes |
|---|---|---|
| F1 Purchase and pricing | Correct totals and discounts, no overselling | `TicketPurchaseController`, `DiscountController`, `Discount`, `Order`, `Ticket`, `TicketType` |
| F2 Payment confirmation | Reliable payment confirmation, trust in gateway events | `PaymentController`, `PaystackService`, `PaystackController`, `PaystackBalanceService`, `Payment` |
| F3 Ticket validation and entry | One admission per ticket, organiser isolation | `TicketController`, `CheckInController`, `Ticket` |

Out of scope: the mobile API v2, the Filament administration panel, framework wiring (Providers, Console, Exceptions) and the features removed before measurement (RSVP, waitlist, event credits, contact, demo, newsletter, event reports and reviews, SEO tooling, event partners, sessions and speakers).

## 3. Test objectives

1. Show that totals, discounts and stock are correct at the boundaries (quantity, discount value, usage limit, sales window).
2. Show that stock cannot be oversold and that a ticket cannot be admitted twice.
3. Show that an order is confirmed only by a genuine, matching payment, and that forged, missing or repeated gateway events cannot change order state wrongly.
4. Show that one organiser cannot read or change another organiser's tickets.
5. Measure response time and query counts for checkout and ticket scanning.
6. Make every check repeatable by an automated pipeline.

## 4. Risk-based prioritisation

Effort follows impact and likelihood. Priority 1 cases cover money or admission; priority 2 cases cover wrong results without financial loss; priority 3 are quality or edge cases.

| Risk | Impact | Flow | Priority |
|---|---|---|---|
| Order confirmed without matching payment | High (money) | F2 | 1 |
| Ticket admitted twice | High (admission, revenue) | F3 | 1 |
| Oversold stock | High | F1 | 1 |
| Wrong total or discount | High (money) | F1 | 1 |
| Personal data exposed to unauthenticated callers | High (privacy) | F3 | 1 |
| Repeated webhook double-credits balances | High | F2 | 1 |
| Abandoned orders hold stock | Medium | F1 | 2 |
| Slow gate scanning | Medium | F3 | 2 |
| Style and maintainability | Low | All | 3 |

## 5. Test levels

| Level | Purpose | Location | Tooling |
|---|---|---|---|
| Unit | Pure logic with no HTTP: discount arithmetic, fee split, signature check, balance maths | `tests/Unit` | Pest 4 |
| Feature (integration) | Request through routes, middleware, controller and database, external services faked | `tests/Feature` | Pest 4, `Http::fake`, `Mail::fake`, `Notification::fake` |
| Concurrency | Races on stock, repeated webhooks, simultaneous scans (simulated with stale model copies; see limitations) | `tests/Feature` | Pest 4 |
| Performance | Response time and query count | `tests/Feature` | Pest 4, `DB::enableQueryLog` |
| End-to-end (browser) | A real browser drives purchase, discount and payment confirmation | `tests/Browser` | Pest 4 browser plugin, Playwright (Chromium) |
| Manual, gateway test mode | Test-card payment on Paystack's hosted checkout with a real callback and a real signed webhook | Test log | Paystack test keys, ngrok tunnel to the local application |
| Static analysis | Types and style without running the code | CI | PHPStan with Larastan, Laravel Pint, ESLint, vue-tsc |
| Mutation | Whether tests fail when code is deliberately broken | CI | Pest mutation testing |

The automated levels follow the test pyramid proportions: many fast unit and feature tests, few browser tests.

## 6. Test design techniques (ISO/IEC/IEEE 29119-4)

| Technique | Applied to |
|---|---|
| Equivalence partitioning | Valid and invalid purchase requests, discount types, event statuses |
| Boundary value analysis | Quantity 0, 1, 10 and 11; stock equal to and one above remaining; discount 0%, 100% and above 100%; usage limit minus one and equal; 24-hour purchase window; clean-up timeout |
| Decision table | Webhook outcome from signature validity, reference and payment status; discount acceptance from active, event, dates and usage |
| State transition | Ticket (`pending`, `active`, `used`, `cancelled`) and order (`pending`, `confirmed`, `cancelled`, `refunded`) |
| Negative and security testing | Missing or forged signature, other-organiser access, mass assignment, SQL metacharacters, honeypot |
| Concurrency and idempotency | Last ticket bought twice, duplicate webhook, double scan |
| Performance testing | Query counts and latency percentiles |

## 7. Environment, data and doubles

| Item | Decision |
|---|---|
| Runtime | PHP 8.4, Laravel 12, Pest 4 on PHPUnit 12 |
| Database | MySQL 8.4; a dedicated `suptix_test` database set in `phpunit.xml` |
| Isolation | `DatabaseTransactions` in every test (project rule: never `RefreshDatabase`), so each test rolls back its own data |
| Test data | Model factories with named states for Organizer, Event, TicketType, Discount, Order, Ticket, Payment and PaymentInfo |
| Coverage driver | pcov |
| Doubles | `Http::fake` for Paystack and SMS, `Mail::fake`, `Notification::fake`; placeholder gateway keys in `phpunit.xml` |
| Gateway test mode | Paystack test keys kept in the local environment file, never committed; the local application is exposed through an ngrok tunnel and the tunnel address is registered as the test webhook URL on the Paystack dashboard |

## 8. Entry and exit criteria

Entry: scope fixed; test database and factories available; baseline measured and committed.

Exit (targets, from the report's Table 8):

| Measure | Baseline | Target |
|---|---|---|
| Line coverage, in-scope classes | 9 of 1,885 (0.5%) | At least 80% |
| Concurrent purchase test | No test | 0 tickets oversold |
| Webhook decision table | No test | 100% of rows pass |
| Simultaneous scan test | No test | Exactly 1 success |
| Mutation score on F1 to F3 | Not measured | At least 60% |
| Static analysis | Not run | PHPStan level 5 and 0 Pint and ESLint errors in in-scope files; no new errors elsewhere |
| Tests passing | 56 of 62 | 100% |
| Scan response time (95th percentile) | Not measured | Threshold set after the first measurement |

## 9. Defect management

Every failing test is classified as a defect in the code, a defect in the test, or an environment fault. Each confirmed code defect is logged with an identifier, flow, description, the test that exposed it, root cause, fix commit and severity, and is fixed in its own commit together with a regression test. Severity: High means money or access is wrong; Medium means a wrong result or error without financial loss; Low means quality or maintainability. A suspected defect is reported only if a test proves it; one that does not reproduce is recorded as investigated and found correct.

## 10. Automation and reporting

A GitHub Actions workflow runs on pushes and pull requests to `master`, `dev` and `staging`. It provisions MySQL, builds the front end, applies migrations and runs Pint (check mode), PHPStan, the test suites with coverage and, as a separate job, mutation testing. Gates fail the build. Coverage, clover XML and test output are kept under `docs/baseline/` (before) and `docs/final/` (after) and the baseline and final commits are tagged.

## 11. Metrics

Tests written and passing per flow and level; line and method coverage per in-scope class; mutation score; defects found per flow and severity; defect density per thousand in-scope lines; defect detection source (reading, testing, static analysis); query counts and latency percentiles; static analysis error counts before and after.

## 12. Standards alignment

The process is a tailored ISO/IEC/IEEE 29119 test process (planning, design, execution, reporting). Quality characteristics follow ISO/IEC 25010:2023: functional suitability (F1), reliability and security (F2, F3), performance efficiency (scanning and checkout) and maintainability (static analysis).

## 13. Limitations

- Concurrency tests are simulations. Transactions hide uncommitted rows from other connections, so true parallel requests cannot be run inside the transactional suite. The tests prove that an atomic guard exists, not that production traffic cannot race.
- The automated suite fakes Paystack. The real gateway is exercised only by the manual test-mode runs. Live mode with real money is never exercised.
- Performance figures come from a local machine and are indicative only.
- Mutation testing is slow and runs as a separate job on in-scope paths only.
- The front end has many inherited lint errors, so its gate compares against a recorded baseline.

## 14. Tool use declaration

The author used an AI coding assistant (Claude Code) to draft this strategy, the test plan and parts of the test code. Every test result reported in the coursework comes from a command run against the repository, and the author is responsible for checking all generated material.
