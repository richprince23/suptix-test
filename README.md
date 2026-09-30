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
- Search-engine-optimized event landing pages with dynamic meta tags, OpenGraph tags, and sitemaps.
- Location integration with Google Maps and venue management.

### Ticketing and RSVP Engine
- Multi-tier ticketing with configurable pricing, capacity caps, and availability windows.
- Dynamic discount code validation and redemption.
- Automated QR code issuance for all confirmed orders.
- Direct PDF ticket generation (DomPDF / client-side) and automated transactional email dispatch.
- RSVP credit purchasing and quota tracking for free or guest-list events.

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
- Newsletter subscription and self-service unsubscription management.

---

## Technology Stack

### Backend
- Framework: Laravel 12.x
- Language: PHP 8.4+
- Authentication: Laravel Sanctum and web session guards
- Admin Panel: Filament v3
- Database & ORM: SQLite (local) / MySQL (production) with Eloquent ORM
- Background Queues: Database / Redis queue worker
- Testing Framework: Pest PHP 3.x

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
│   └── Services/          # Domain services (Payment, SEO, SMS, TicketService)
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
│   ├── ticket-purchase.php# Ticket order and checkout workflows
│   └── settings.php       # Account and profile settings
└── tests/
    ├── Feature/           # Feature integration tests (Pest)
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
Run the test suite using Pest PHP:
```bash
composer run test
```
Or run directly through Artisan:
```bash
php artisan test
php artisan test --coverage
```

### Code Formatting and Linting
- Format PHP code with Laravel Pint:
  ```bash
  php artisan pint
  ```
- Lint frontend assets:
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
- `SEO_IMPLEMENTATION.md` - Schema and meta tag specifications.

---

## License

This project is licensed under the MIT License.
