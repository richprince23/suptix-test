# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Suptix is a Laravel-based event management system using the TALL stack (Tailwind, Alpine, Laravel, Livewire) with Vue.js frontend via Inertia.js. It provides event creation, ticket management, and organizer management features through a Filament admin panel.

## Development Commands

### Backend (Laravel/PHP)
- **Start development server**: `composer run dev` (runs concurrent processes: server, queue, logs, vite)
- **Run tests**: `composer run test` or `php artisan test`
- **Code formatting**: `php artisan pint`
- **Database migrations**: `php artisan migrate`
- **Generate application key**: `php artisan key:generate`
- **Clear config cache**: `php artisan config:clear`
- **Queue processing**: `php artisan queue:listen --tries=1`
- **Log monitoring**: `php artisan pail --timeout=0`
- **Sync Paystack subaccounts**: `php artisan paystack:sync-subaccounts` (pull fresh data from Paystack)
- **Dry-run Paystack sync**: `php artisan paystack:sync-subaccounts --dry-run` (test without making changes)

### Frontend (Vue.js/TypeScript)
- **Development mode**: `npm run dev`
- **Build for production**: `npm run build`
- **Build with SSR**: `npm run build:ssr`
- **Lint and fix**: `npm run lint`
- **Format code**: `npm run format`
- **Check formatting**: `npm run format:check`
- **Type checking**: `vue-tsc --noEmit`

### Testing
- **Run all tests**: `composer run test`
- **Run specific test file**: `php artisan test tests/Feature/DashboardTest.php`
- **Run with coverage**: `php artisan test --coverage`

## Architecture Overview

### Core Models and Relationships
- **Event**: Central entity with relationships to Organizer, Venue, and TicketType
- **Organizer**: Manages events, has many-to-many relationship with Users
- **Venue**: Location information for events
- **TicketType**: Different ticket categories for events
- **User**: Authentication and user management

### Key Directories
- `app/Models/`: Eloquent models with relationships
- `app/Filament/Resources/`: Admin panel resources for CRUD operations
- `app/Http/Controllers/`: API and web controllers
- `resources/js/components/`: Vue.js components and UI library
- `resources/js/pages/`: Inertia.js page components
- `database/migrations/`: Database schema definitions
- `tests/`: Pest testing framework tests

### Frontend Architecture
- **Inertia.js**: Bridges Laravel backend with Vue.js frontend
- **Vue 3 with Composition API**: Modern Vue.js patterns
- **Tailwind CSS**: Utility-first CSS framework
- **Reka UI**: Vue component library for UI elements
- **TypeScript**: Type-safe JavaScript

### Admin Panel
- **Filament v3**: Laravel admin panel framework
- **Multi-tenancy**: Organizer-based tenancy system
- **Resource management**: CRUD operations for Events, Venues, Users, Ticket Types
- **File uploads**: Image handling for event covers
- **Form validation**: Built-in validation and error handling

## Key Features

### Event Management
- Event creation with cover images, dates, times, and descriptions
- Event type enumeration with contextual information
- Tag-based categorization
- Status management (draft, published, live, canceled)
- Automatic event code generation for URLs

### Ticket Management
- Multiple ticket types per event
- Ticket pricing and availability
- Relation managers in Filament for ticket CRUD

### Multi-tenancy
- Organizer-based tenant isolation
- Automatic organizer assignment to events
- User-organizer relationship management

## Development Guidelines

### Code Style
- Follow PSR-12 coding standards for PHP
- Use Laravel Pint for PHP code formatting
- Use ESLint and Prettier for JavaScript/TypeScript
- Maintain consistent Vue.js component structure

### Database
- Use migrations for all schema changes
- Follow Laravel naming conventions for tables and columns
- Use Eloquent relationships instead of raw queries
- Utilize factories and seeders for test data

### Testing
- Use Pest testing framework for PHP tests
- Write feature tests for user-facing functionality
- Include authentication and authorization tests
- Test Filament resources and forms

### Security
- Implement proper authorization using Laravel policies
- Use CSRF protection on all forms
- Validate file uploads (images limited to 5MB)
- Follow Laravel security best practices
- Add honeypot to all forms

## Branch Strategy

- **master**: Production/live branch
- **dev**: Local development branch
- **staging**: Testing and hotfix branch
- **feature/**: Individual feature branches (merge upward)

## Common Patterns

### Filament Resources
- Use sections and fieldsets for form organization
- Implement reactive form fields where needed
- Use relationship selects for foreign keys
- Include helper text for complex fields

### Vue Components
- Use composition API with TypeScript
- Implement proper prop typing
- Use Inertia.js for page navigation
- Follow component naming conventions

### Model Relationships
- Define inverse relationships for data integrity
- Use proper foreign key constraints
- Implement model observers for automatic field population
- Use accessors for computed properties like URLs

### Paystack Integration
- **PaymentInfo model**: Manages subaccounts with automatic Paystack synchronization
- **Automatic sync**: Updates and deletions sync with Paystack via model observers
- **Balance tracking**: Separate subaccount and system admin balance tracking
- **Sync command**: Use `php artisan paystack:sync-subaccounts` to pull fresh data from Paystack
- **Error handling**: Failed sync operations are logged and stored in metadata for admin review

## Memories
- remember the added directory
- ../../Suptle/suptix_scan