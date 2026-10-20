# Suptix

Suptix is a modern, scalable event management and ticketing platform designed for event organizers and attendees. Built with a Laravel 12 backend and a Vue 3 / Inertia.js frontend, Suptix delivers end-to-end event discovery, multi-tier ticket sales, integrated African payment processing via Paystack, automated QR code generation and validation, and a multi-tenant organizer admin panel powered by Filament v3.

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
`--headed` cannot be combined with `--parallel`. Use `composer run test:browser` for the normal headless run.

### Coverage, static analysis and mutation testing
```bash
# line coverage (pcov), then the in-scope gate used in CI (at least 80%)
php -d pcov.enabled=1 -d pcov.directory=app artisan test --coverage-clover coverage/clover.xml
php scripts/check-scope-coverage.php coverage/clover.xml 80

# static analysis (PHPStan level 5 on the in-scope classes, see phpstan.neon)
./vendor/bin/phpstan analyse

# mutation testing on one class (covered lines only)
php -d pcov.enabled=1 -d pcov.directory=app ./vendor/bin/pest --mutate --covered-only --class='App\Models\Discount' --testsuite=Unit,Feature
```

### Quality documentation
- [Test strategy](docs/testing/TEST_STRATEGY.md) and [test plan](docs/testing/TEST_PLAN.md) with the case catalogue.
- [Defect log](docs/defects/DEFECT_LOG.md) and the test run taken before any fix.
- [Baseline coverage](docs/baseline/clover-cleandb.xml) and the final measurements in `docs/final/`.
- The GitHub Actions workflow in `.github/workflows/ci.yml` runs style, static analysis, tests with coverage and mutation testing.

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

## License

This project is licensed under the MIT License.
