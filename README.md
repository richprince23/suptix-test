# Suptix

Suptix is a modern, scalable event management and ticketing platform designed for event organizers and attendees. Built with a Laravel 12 backend and a Vue 3 / Inertia.js frontend, Suptix delivers end-to-end event discovery, multi-tier ticket sales, integrated African payment processing via Paystack, automated QR code generation and validation, and a multi-tenant organizer admin panel powered by Filament v3.

---

## Table of Contents

- [Project Status](#project-status)
- [Core Features](#core-features)
- [Technology Stack](#technology-stack)
- [Architecture Overview](#architecture-overview)
- [Getting Started](#getting-started)
- [Running the Application](#running-the-application)
- [Testing and Quality Assurance](#testing-and-quality-assurance)
  - [Automated Tests](#automated-tests)
  - [Watching the browser tests run](#watching-the-browser-tests-run)
  - [Coverage, static analysis and mutation testing](#coverage-static-analysis-and-mutation-testing)
  - [Test results](#test-results)
  - [Quality documentation](#quality-documentation)
  - [Code Formatting and Linting](#code-formatting-and-linting)
- [Production Deployment](#production-deployment)
- [API Documentation and Guides](#api-documentation-and-guides)
- [Appendices](#appendices)
  - [Appendix A: Documentation and evidence index](#appendix-a-documentation-and-evidence-index)
  - [Appendix B: Command reference](#appendix-b-command-reference)
  - [Appendix C: In-scope classes and coverage](#appendix-c-in-scope-classes-and-coverage)
  - [Appendix D: Earlier recorded figures](#appendix-d-earlier-recorded-figures)
  - [Appendix E: Defect summary](#appendix-e-defect-summary)
  - [Appendix F: Test ID prefixes](#appendix-f-test-id-prefixes)
- [License](#license)

Every command used in this project is listed in [dev-notes.md](dev-notes.md).

---

## Project Status

- **Phase 1: Project Setup and Development** - Completed
- **Phase 2: Testing and Quality Assurance** - In Progress
- **Phase 3: Deployment** - To Be Implemented
- **Phase 4: Maintenance and Improvements** - To Be Implemented

---

## Core Features

### Event Discovery and Management
- Event creation with rich descriptions, cover banners, schedules, and locations.
- Categorization across diverse event types (Educational, Corporate, Social, Cultural, Sports).
- Event lifecycle management (Draft, Published, Live, Canceled, Past).
- Location integration with Google Maps and venue management.

### Ticketing
- Multi-tier ticketing with configurable pricing, capacity caps, and availability windows.
- Dynamic discount code validation and redemption.
- Automated QR code issuance for all confirmed orders.
- Direct PDF ticket generation (DomPDF / client-side) and automated transactional email dispatch.

### African Payment Processing (Paystack)
- Seamless checkout experience powered by Paystack.
- Automated split-payments and subaccount transfers for organizers.
- Robust webhook verification and idempotency handling for payment confirmations.
- Bank account verification and balance synchronisation for organizer payouts.

### Check-in and Mobile Scanning API
- Dedicated REST API (v2 and mobile routes) secured with Laravel Sanctum.
- High-performance QR code verification and manual ticket entry validation.
- Real-time attendee check-in logging and duplicate scan prevention.
- Lightweight connectivity probe endpoint (`/api/ping`) for mobile scanning hardware/apps.
- Comprehensive event check-in statistics and entry reporting.

### Multi-Tenant Organizer Administration
- Back-office dashboard powered by Filament v3.
- Organizer-based tenancy isolating events, tickets, revenues, and attendees.
- Granular role and permission management via Spatie Laravel Permission.
- Excel data export for attendees and financial reports.

### Multi-Channel Communication
- Transactional SMS notifications supported via Hubtel and GiantSMS providers with fallback.
- Transactional emails for order confirmations, ticket deliveries, and event updates.

---

## Technology Stack

### Backend
- Framework: Laravel 12.x
- Language: PHP 8.4+
- Authentication: Laravel Sanctum and web session guards
- Admin Panel: Filament v3
- Database & ORM: SQLite (local) / MySQL (production) with Eloquent ORM
- Background Queues: Database / Redis queue worker
- Testing Framework: Pest 4 (PHPUnit 12) with the browser plugin (Playwright), Larastan (PHPStan level 5), Pest mutation testing

### Frontend
- Framework: Vue 3 (Composition API)
- Client-Server Bridge: Inertia.js v2
- Language: TypeScript
- Styling: Tailwind CSS v4 and Reka UI
- Build Tool: Vite 6 with SSR support
- Icons: HugeIcons / Phosphor Icons

### Integrations and Services
- Payments: Paystack API & Webhooks
- SMS Gateways: Hubtel SMS API, GiantSMS API
- Mapping: Google Maps API
- Cloud Storage: AWS S3 / Local Disk

---

## Architecture Overview

```
suptix/
├── app/
│   ├── Filament/          # Filament v3 admin resources and pages
│   ├── Http/
│   │   ├── Controllers/   # Web and API controllers
│   │   └── Middleware/    # HTTP middleware (auth, sanitization, honeypot)
│   ├── Models/            # Eloquent data models (Event, Order, Ticket, Organizer, etc.)
│   └── Services/          # Domain services (Payment, SMS, TicketService)
├── config/                # Application configuration files
├── database/
│   ├── factories/         # Model factories for testing and seeding
│   ├── migrations/        # Database schema definitions
│   └── seeders/           # Initial and mock data seeders
├── resources/
│   ├── js/
│   │   ├── components/    # Reusable Vue components and design system
│   │   ├── layouts/       # Inertia page layouts
│   │   └── pages/         # Inertia view pages
│   └── views/             # Blade root layouts and email templates
├── routes/
│   ├── api.php            # Mobile and external REST endpoints
│   ├── web.php            # Web client routes and Inertia endpoints
│   └── ticket-purchase.php# Ticket order and checkout workflows
└── tests/
    ├── Browser/           # End-to-end tests in a real browser (Pest browser plugin)
    ├── Feature/           # Feature integration tests (Pest): Purchase, Payment, Tickets, Orders, Models, Performance
    └── Unit/              # Isolated unit tests
```

---

## Getting Started

### Prerequisites
- PHP 8.2 or higher (PHP 8.4 recommended)
- Composer 2.x
- Node.js 20.x or higher and NPM
- SQLite (default for development) or MySQL 8.0+

### Installation

1. Clone the repository:
   ```bash
   git clone git@github.coventry.ac.uk:Aikinsr/suptix.git
   cd suptix
   ```

2. Install backend dependencies:
   ```bash
   composer install
   ```

3. Install frontend dependencies:
   ```bash
   npm install
   ```

4. Configure environment settings:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. Configure database and credentials in `.env`:
   ```env
   APP_NAME=Suptix
   APP_URL=http://localhost:8000

   DB_CONNECTION=sqlite
   # For MySQL:
   # DB_CONNECTION=mysql
   # DB_HOST=127.0.0.1
   # DB_PORT=3306
   # DB_DATABASE=suptix
   # DB_USERNAME=root
   # DB_PASSWORD=

   # Paystack Settings
   PAYSTACK_PUBLIC_KEY=pk_test_xxxx
   PAYSTACK_SECRET_KEY=sk_test_xxxx
   PAYSTACK_PLATFORM_FEE_PERCENTAGE=7

   # SMS Provider (hubtel or giant_sms)
   SMS_PRIMARY_PROVIDER=hubtel
   HUBTEL_CLIENT_ID=
   HUBTEL_CLIENT_SECRET=
   HUBTEL_SENDER_ID=Suptix
   ```

6. Run database migrations and seed default data:
   ```bash
   touch database/database.sqlite
   php artisan migrate --seed
   ```

7. Create the storage symlink:
   ```bash
   php artisan storage:link
   ```

---

## Running the Application

### Concurrent Development Server
Start all required processes (web server, queue listener, Pail log watcher, and Vite dev server) concurrently:
```bash
composer run dev
```

### Running Individual Services
If preferred, services can be launched in separate terminal sessions:

- Laravel Web Server:
  ```bash
  php artisan serve
  ```

- Vite Asset Server:
  ```bash
  npm run dev
  ```

- Background Queue Worker:
  ```bash
  php artisan queue:listen --tries=1
  ```

- Log Streamer (Laravel Pail):
  ```bash
  php artisan pail --timeout=0
  ```

---

## Testing and Quality Assurance

### Automated Tests
All test, browser, coverage, mutation and static analysis commands are in [dev-notes.md](dev-notes.md) ([running tests](dev-notes.md#3-running-tests), [browser tests](dev-notes.md#4-browser-tests), [coverage](dev-notes.md#5-coverage)).

The suite runs against a dedicated MySQL database, `suptix_test` (set in `phpunit.xml`), and every test uses `DatabaseTransactions`, never `RefreshDatabase`. Create the database and migrate it once:
```bash
php artisan tinker --execute="DB::statement('CREATE DATABASE IF NOT EXISTS suptix_test');"
DB_DATABASE=suptix_test php artisan migrate
```
Install the browser used by the end-to-end tests once (`npm install` then):
```bash
npx playwright install chromium
```
Run the tests:
```bash
php artisan test                          # all suites: Unit, Feature, Browser
php artisan test --testsuite=Unit,Feature # without the browser tests
```

### Watching the browser tests run
The browser tests run headless and fast by default (and in CI). To watch them like a Selenium demo, with a visible browser, keystroke-by-keystroke typing and a pause after each click:
```bash
composer run test:browser:watch
# the same thing by hand:
BROWSER_DEMO=1 ./vendor/bin/pest tests/Browser --headed
# one test only:
BROWSER_DEMO=1 ./vendor/bin/pest tests/Browser/TicketPurchaseFlowTest.php --filter="lets a guest" --headed
```
#### Paying on the real Paystack test checkout
`tests/Browser/PaystackTestModeCheckoutTest.php` goes through the real Paystack test environment instead of the stand-in checkout page: the app calls Paystack's API with the `sk_test_` key from `.env`, the browser lands on Paystack's hosted checkout, pays with Paystack's published test card and returns to the application. No money moves. It is skipped unless `PAYSTACK_LIVE=1`, and always skipped if the key in `.env` is not a test key.
```bash
composer run test:browser:paystack
```
Paystack's checkout sits behind a Cloudflare "Verify you are human" check. The test does not touch it: when it appears, tick the box in the browser window yourself and the test carries on (it waits up to two minutes). It needs at least one subaccount in your Paystack test account, and it will not run in CI.

`--headed` cannot be combined with `--parallel`. Use `composer run test:browser` for the normal headless run.

### Coverage, static analysis and mutation testing
Full command lists: [coverage](dev-notes.md#5-coverage), [static analysis and style](dev-notes.md#6-static-analysis-style-and-type-checks), [mutation testing](dev-notes.md#7-mutation-testing).

```bash
# line coverage (pcov), then the in-scope gate used in CI (at least 80%)
php -d pcov.enabled=1 -d pcov.directory=app artisan test --coverage-clover coverage/clover.xml
php scripts/check-scope-coverage.php coverage/clover.xml 80

# static analysis (PHPStan level 5 on the in-scope classes, see phpstan.neon)
./vendor/bin/phpstan analyse

# mutation testing on one class (covered lines only)
php -d pcov.enabled=1 -d pcov.directory=app ./vendor/bin/pest --mutate --covered-only --class='App\Models\Discount' --testsuite=Unit,Feature
```

### Test results

Final run of the case study on branch `original-scope` (25 October 2026): all suites (Unit, Feature, Browser), PHP 8.4.15, Laravel 12.70.0, Pest 4.7.8, MySQL 8.4.5 database `suptix_test`, PCOV. The baseline is the inherited suite (commit `a36bd12`, case-study tests disabled). Full tables are in [docs/testing/TEST_RESULTS.md](docs/testing/TEST_RESULTS.md); the raw output is in [docs/testing/evidence/](docs/testing/evidence/).

| Measure | Baseline | After |
|---|---:|---:|
| Tests | 135 | 423 |
| Passed | 114 | 422 |
| Failed | 21 | 0 |
| Skipped | 0 | 1 (live Paystack, opt-in) |
| In-scope line coverage | 0.5% (9 of 1,882) | 91.4% (1,720 of 1,881) |
| Run time | 21.0 s | 79.5 s |

- **New tests:** 288 (76 purchase and pricing, 87 payment confirmation, 50 ticket entry, 67 contracts, models, clean-up and performance, 8 browser).
- **Browser tests:** 7 passed, 1 skipped (`PaystackTestModeCheckoutTest`, needs `PAYSTACK_LIVE=1`).
- **Baseline failures:** the 21 inherited failing tests (authentication, dashboard, event partner logos and RSVP) were repaired; the causes and fixes are in [TEST_RESULTS.md](docs/testing/TEST_RESULTS.md#the-21-baseline-failures-and-how-they-were-fixed), and the output from before the fixes is in [failing-tests-before-fixes.txt](docs/testing/evidence/failing-tests-before-fixes.txt).
- **Test cases:** 54 cases, 18 of Priority 1, all passing. Four Priority 2 cases are not tested and two are partial ([TEST_CASES.md](docs/testing/TEST_CASES.md)).
- **Defects:** 29 found and fixed (4 Critical, 6 High, 14 Medium, 5 Low) with the test that exposed each ([DEFECT_LOG.md](docs/defects/DEFECT_LOG.md)).
- **Recorded in earlier runs, not repeated:** mutation score 74.0% ([MUTATION.md](docs/final/MUTATION.md)), performance figures ([performance.md](docs/final/performance.md)) and CI runs ([CI_EVIDENCE.md](docs/final/CI_EVIDENCE.md)).
- **Release recommendation:** Conditional go.

In-scope line coverage by class (final run, [in-scope-coverage.txt](docs/testing/evidence/in-scope-coverage.txt)):

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

Commands to reproduce these results: [run the tests](dev-notes.md#3-running-tests), [measure coverage](dev-notes.md#5-coverage), [reproduce the baseline](dev-notes.md#9-reproducing-the-baseline) and [regenerate the evidence files](dev-notes.md#10-regenerating-the-evidence-files).

### Quality documentation
- [Test strategy](docs/testing/TEST_STRATEGY.md) and [test plan](docs/testing/TEST_PLAN.md).
- [Test case catalogue](docs/testing/TEST_CASES.md): 54 cases for the three flows, each traced to its automated tests and result.
- [Test results](docs/testing/TEST_RESULTS.md): run record, before and after figures, coverage by class and sign-off, with the raw run output in [docs/testing/evidence/](docs/testing/evidence/).
- [Defect log](docs/defects/DEFECT_LOG.md) and [the test run taken before any fix](docs/defects/pre-fix-test-run.txt).
- Baseline coverage: [coverage-summary.txt](coverage-summary.txt) (whole application, text report), [docs/baseline/](docs/baseline/) (clover files, HTML report and run output).
- Earlier final measurements: [docs/final/](docs/final/) (see [Appendix D](#appendix-d-earlier-recorded-figures)).
- [Developer notes and every command](dev-notes.md).
- System design diagrams: [System_Design.mmd](docs/design/System_Design.mmd) and [System_Design_Sequence.mmd](docs/design/System_Design_Sequence.mmd).
- The GitHub Actions workflow in [.github/workflows/ci.yml](.github/workflows/ci.yml) runs style, static analysis, tests with coverage and mutation testing; the scope gate is [scripts/check-scope-coverage.php](scripts/check-scope-coverage.php).

### Code Formatting and Linting
- Format PHP code with Laravel Pint (use `./vendor/bin/pint --test` to check without changing files):
  ```bash
  php artisan pint
  ```
- Lint frontend assets (`npm run lint` fixes files; `npx eslint <files> --no-fix` only checks):
  ```bash
  npm run lint
  ```
- Format frontend code with Prettier:
  ```bash
  npm run format
  npm run format:check
  ```
- Run TypeScript type checks:
  ```bash
  npx vue-tsc --noEmit
  ```

---

## Production Deployment

### Production Asset Compilation
Compile frontend assets for production:
```bash
npm run build
```
For Server-Side Rendering (SSR) deployment:
```bash
npm run build:ssr
```

### Containerization
The repository includes a ready-to-use `Dockerfile` and `docker-compose.yml`:
```bash
docker compose up -d --build
```
Additionally, `nixpacks.toml` is provided for container platforms such as Railway and Coolify.

---

## API Documentation and Guides

Additional reference documentation is available in the repository:
- `API_V2_DOCUMENTATION.md` - Sanctum token authentication and Mobile API v2 endpoints.
- `MOBILE_API_DOCUMENTATION.md` - Mobile verification and check-in workflows.
- `FLUTTER_SCANNING_APP_GUIDE.md` - Architecture and integration guide for Flutter scanning clients.
- `SMS_SETUP.md` - Setup and failover configuration for Hubtel and GiantSMS.

---

## Appendices

### Appendix A: Documentation and evidence index

| File | What it holds |
|---|---|
| [dev-notes.md](dev-notes.md) | Every command, grouped by task, plus the branch strategy |
| [docs/testing/TEST_STRATEGY.md](docs/testing/TEST_STRATEGY.md) | Test strategy |
| [docs/testing/TEST_PLAN.md](docs/testing/TEST_PLAN.md) | Test plan and the detailed case list used to write the tests |
| [docs/testing/TEST_CASES.md](docs/testing/TEST_CASES.md) | 54-case catalogue with automated test IDs and results |
| [docs/testing/TEST_RESULTS.md](docs/testing/TEST_RESULTS.md) | Run record, results, baseline failures and sign-off |
| [docs/testing/evidence/full-run-after-fixes.txt](docs/testing/evidence/full-run-after-fixes.txt) | Output of the final full run (422 passed, 1 skipped) |
| [docs/testing/evidence/failing-tests-before-fixes.txt](docs/testing/evidence/failing-tests-before-fixes.txt) | The 21 failing tests before the fixes |
| [docs/testing/evidence/in-scope-coverage.txt](docs/testing/evidence/in-scope-coverage.txt) | In-scope coverage by class, final run |
| [docs/defects/DEFECT_LOG.md](docs/defects/DEFECT_LOG.md) | 29 defects with cause, fix and retest, plus test and environment defects |
| [docs/defects/pre-fix-test-run.txt](docs/defects/pre-fix-test-run.txt) | Test run taken before any fix |
| [coverage-summary.txt](coverage-summary.txt) | Baseline coverage text report for the whole application |
| [docs/baseline/coverage-summary.txt](docs/baseline/coverage-summary.txt) | The same report, kept with the baseline files |
| [docs/baseline/original-run.txt](docs/baseline/original-run.txt) | Baseline test run output |
| [docs/baseline/original-scope-coverage.txt](docs/baseline/original-scope-coverage.txt) | Baseline in-scope coverage (9 of 1,929 lines at that time) |
| [docs/baseline/clover-original.xml](docs/baseline/clover-original.xml), [clover.xml](docs/baseline/clover.xml), [clover-cleandb.xml](docs/baseline/clover-cleandb.xml), [clover-baseline.xml](docs/baseline/clover-baseline.xml) | Clover coverage files from the baseline runs |
| [docs/baseline/html/](docs/baseline/html/) | HTML coverage report of the baseline |
| [docs/final/SUMMARY.md](docs/final/SUMMARY.md) | Before and after table from the earlier final run |
| [docs/final/test-run.txt](docs/final/test-run.txt), [clover.xml](docs/final/clover.xml), [scope-coverage.txt](docs/final/scope-coverage.txt) | Output and coverage of the earlier final run |
| [docs/final/MUTATION.md](docs/final/MUTATION.md), [mutation-final.txt](docs/final/mutation-final.txt), [mutation-run1-summary.txt](docs/final/mutation-run1-summary.txt) | Mutation testing results |
| [docs/final/performance.md](docs/final/performance.md) | Query counts and response times |
| [docs/final/CI_EVIDENCE.md](docs/final/CI_EVIDENCE.md) | GitHub Actions runs used as evidence |
| [docs/final/phpstan.txt](docs/final/phpstan.txt), [pint.txt](docs/final/pint.txt), [frontend.txt](docs/final/frontend.txt) | Static analysis, style and front-end lint results |
| [docs/design/System_Design.mmd](docs/design/System_Design.mmd), [System_Design_Sequence.mmd](docs/design/System_Design_Sequence.mmd) | Mermaid sources of the design diagrams |
| [phpunit.xml](phpunit.xml), [phpstan.neon](phpstan.neon), [.github/workflows/ci.yml](.github/workflows/ci.yml) | Test, analysis and pipeline configuration |
| [scripts/check-scope-coverage.php](scripts/check-scope-coverage.php) | In-scope coverage gate |

### Appendix B: Command reference

The full list, with explanations, is in [dev-notes.md](dev-notes.md).

| Task | Command | Details |
|---|---|---|
| Create the test database | `DB_DATABASE=suptix_test php artisan migrate` | [dev-notes §2](dev-notes.md#2-test-database) |
| Run every suite | `php artisan test` or `composer run test` | [dev-notes §3](dev-notes.md#3-running-tests) |
| Run Unit and Feature only | `php artisan test --testsuite=Unit,Feature` | [dev-notes §3](dev-notes.md#3-running-tests) |
| Run one test by ID | `./vendor/bin/pest --filter="F2-16"` | [dev-notes §3](dev-notes.md#3-running-tests) |
| Install the browser | `npx playwright install chromium` | [dev-notes §4](dev-notes.md#4-browser-tests) |
| Run browser tests | `composer run test:browser` | [dev-notes §4](dev-notes.md#4-browser-tests) |
| Coverage, all suites | `php -d pcov.enabled=1 -d pcov.directory=app ./vendor/bin/pest --coverage-clover=coverage/clover.xml` | [dev-notes §5](dev-notes.md#5-coverage) |
| In-scope coverage gate | `php scripts/check-scope-coverage.php coverage/clover.xml 80` | [dev-notes §5](dev-notes.md#5-coverage) |
| Static analysis | `./vendor/bin/phpstan analyse` | [dev-notes §6](dev-notes.md#6-static-analysis-style-and-type-checks) |
| Style check | `./vendor/bin/pint --test` | [dev-notes §6](dev-notes.md#6-static-analysis-style-and-type-checks) |
| Mutation testing | `./vendor/bin/pest --mutate --covered-only --class='App\Models\Discount'` | [dev-notes §7](dev-notes.md#7-mutation-testing) |
| Reproduce the baseline | `git switch --detach a36bd12` then run the suite | [dev-notes §9](dev-notes.md#9-reproducing-the-baseline) |
| Rebuild the assets | `npm run build` | [dev-notes §11](dev-notes.md#11-build-and-containers) |

### Appendix C: In-scope classes and coverage

| Flow | Classes |
|---|---|
| F1 Purchase and pricing | `TicketPurchaseController`, `DiscountController`, `Discount`, `Order`, `Ticket`, `TicketType` |
| F2 Payment confirmation | `PaymentController`, `PaystackService`, `PaystackController`, `PaystackBalanceService`, `Payment` |
| F3 Ticket validation and entry | `TicketController`, `CheckInController`, `Ticket` |

Out of scope: Mobile API v2, Filament Admin, and the Providers, Console and Exceptions directories. The per-class coverage table is in [Test results](#test-results). The scope is set in [phpunit.xml](phpunit.xml) and [phpstan.neon](phpstan.neon).

### Appendix D: Earlier recorded figures

[docs/final/](docs/final/) records an earlier final run on the `slim-core` branch: 350 tests (349 passed, 1 skipped), in-scope coverage 92.3% (1,709 of 1,852 lines) and a mutation score of 74.0%. The results in [Test results](#test-results) come from the later run on `original-scope`, so the totals differ slightly. The mutation, performance and CI figures were not repeated on `original-scope`.

### Appendix E: Defect summary

| Severity | Count | Examples |
|---|---:|---|
| Critical | 4 | Any paid amount confirmed an order (DEF-05); used or unpaid tickets scanned as valid (DEF-13); two simultaneous check-ins both succeeded (DEF-16); users verified other organisers' tickets (DEF-25) |
| High | 6 | Expired discount accepted (DEF-02); balances not summing to the amount paid (DEF-12, DEF-23); attendee email shown to guests (DEF-14); PDF ticket QR scan failed (DEF-27); event page blank (DEF-29) |
| Medium | 14 | Fractional platform fee (DEF-08); shared payment reference (DEF-09); ticket list cost 1,007 queries (DEF-19) |
| Low | 5 | Quantity sent as a string rejected (DEF-04); dead `is_used` attribute (DEF-17) |

By flow: F1 7, F2 11, F3 9, general 2. Severity follows the study's severity guide; the log itself records the four Critical defects as High. The full log is [docs/defects/DEFECT_LOG.md](docs/defects/DEFECT_LOG.md).

### Appendix F: Test ID prefixes

| Prefix | Meaning |
|---|---|
| F1, F2, F3 | Purchase and pricing, payment confirmation, ticket validation and entry |
| F4 | Order clean-up and stock integrity (reported under F1) |
| X | Cross-cutting security checks |
| P | Performance and scalability |
| E | Browser end-to-end tests |
| D | Inherited failing tests |

The catalogue IDs in [TEST_CASES.md](docs/testing/TEST_CASES.md) (for example F2-04) are separate from the IDs in the test names (for example F2-16); the catalogue lists which tests cover each case.

---

## License

This project is licensed under the MIT License.
