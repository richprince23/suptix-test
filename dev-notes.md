# Suptix

## Instructions & Guidelines

Repo Structure
- master branch is the live branch
- dev branch is for local development
- staging branch is for testing dev branch and hotfixing
- feature/individual branches for individual or feature based dev.
PS: This is where we work and merge upwards

This file lists every command used to run, test, measure and document the project. Results are in [docs/testing/TEST_RESULTS.md](docs/testing/TEST_RESULTS.md); the README links here from its [Testing and Quality Assurance](README.md#testing-and-quality-assurance) section and [Appendix B](README.md#appendix-b-command-reference).

## Contents
1. [Setup and development server](#1-setup-and-development-server)
2. [Test database](#2-test-database)
3. [Running tests](#3-running-tests)
4. [Browser tests](#4-browser-tests)
5. [Coverage](#5-coverage)
6. [Static analysis, style and type checks](#6-static-analysis-style-and-type-checks)
7. [Mutation testing](#7-mutation-testing)
8. [Performance measurements](#8-performance-measurements)
9. [Reproducing the baseline](#9-reproducing-the-baseline)
10. [Regenerating the evidence files](#10-regenerating-the-evidence-files)
11. [Build and containers](#11-build-and-containers)
12. [Paystack](#12-paystack)

## 1. Setup and development server
```bash
composer install
npm install
cp .env.example .env && php artisan key:generate
php artisan migrate
composer run dev           # server, queue, logs and vite together
composer run dev:ssr       # the same with server-side rendering
php artisan serve          # server only
php artisan queue:listen --tries=1
php artisan pail --timeout=0
npm run dev
php artisan config:clear
```

## 2. Test database
Tests use `suptix_test` (set in `phpunit.xml`) and `DatabaseTransactions`, never the development database. Create and migrate it once:
```bash
php artisan tinker --execute="DB::statement('CREATE DATABASE IF NOT EXISTS suptix_test');"
DB_DATABASE=suptix_test php artisan migrate
```

## 3. Running tests
```bash
composer run test                                  # config:clear, then every suite
php artisan test                                   # Unit, Feature and Browser
php artisan test --testsuite=Unit,Feature          # without the browser tests
php artisan test --testsuite=Browser               # browser tests only
php artisan test tests/Feature/Payment             # one folder
php artisan test tests/Feature/Payment/PaystackWebhookTest.php   # one file
./vendor/bin/pest --filter="F2-16"                 # one test by its ID
./vendor/bin/pest --colors=never                   # plain output with no colour codes
```
Test IDs (F1-xx, F2-xx, F3-xx, F4-xx, P-xx, X-xx, E-xx) are listed in [docs/testing/TEST_CASES.md](docs/testing/TEST_CASES.md) and [docs/testing/TEST_PLAN.md](docs/testing/TEST_PLAN.md).

## 4. Browser tests
```bash
npx playwright install chromium                    # once; the browser lives in ~/Library/Caches/ms-playwright
composer run test:browser                          # headless
composer run test:browser:watch                    # visible browser, slow typing
BROWSER_DEMO=1 ./vendor/bin/pest tests/Browser --headed
BROWSER_DEMO=1 ./vendor/bin/pest tests/Browser/TicketPurchaseFlowTest.php --filter="lets a guest" --headed
composer run test:browser:paystack                 # real Paystack test checkout (skipped without PAYSTACK_LIVE=1)
PAYSTACK_LIVE=1 BROWSER_DEMO=1 ./vendor/bin/pest tests/Browser/PaystackTestModeCheckoutTest.php --headed
```
If the browser tests fail with `PlaywrightOutdatedException`, run `npx playwright install chromium` again. If the event page stays blank, rebuild the assets with `npm run build`.

## 5. Coverage
In-scope coverage covers the 13 classes listed in `phpunit.xml`, not the whole application.
```bash
# HTML report, all suites
php -d pcov.enabled=1 -d pcov.directory=app artisan test --coverage --coverage-html coverage 2>&1 | tail -80

# Clover file, then the in-scope gate used in CI (at least 80%)
mkdir -p coverage
php -d pcov.enabled=1 -d pcov.directory=app artisan test --coverage-clover coverage/clover.xml
php scripts/check-scope-coverage.php coverage/clover.xml 80

# The same run through Pest, all suites, as used for the final results
php -d pcov.enabled=1 -d pcov.directory=app ./vendor/bin/pest --coverage-clover=coverage/clover.xml
```
Per-class table from a clover file:
```bash
php -r '
$x = simplexml_load_file("coverage/clover.xml");
$classes = [
    "TicketPurchaseController.php",
    "DiscountController.php",
    "PaymentController.php",
    "PaystackController.php",
    "TicketController.php",
    "CheckInController.php",
    "PaystackService.php",
    "PaystackBalanceService.php",
    "Discount.php",
    "Order.php",
    "Ticket.php",
    "TicketType.php",
    "Payment.php",
];
$total = $covered = 0;
printf("%-36s %12s %12s %10s\n", "CLASS", "COVERED", "STATEMENTS", "COVERAGE");
echo str_repeat("-", 74), "\n";
foreach ($x->xpath("//file") as $f) {
    $name = basename((string) $f["name"]);
    if (!in_array($name, $classes)) continue;
    $m = $f->metrics;
    $c = (int) $m["coveredstatements"];
    $s = (int) $m["statements"];
    printf("%-36s %12d %12d %9.2f%%\n",
        $name, $c, $s, $s ? 100 * $c / $s : 0);
    $covered += $c;
    $total += $s;
}
echo str_repeat("-", 74), "\n";
printf("%-36s %12d %12d %9.2f%%\n",
    "TOTAL", $covered, $total, $total ? 100 * $covered / $total : 0);
'
```
`phpunit.xml` limits the clover file to the 13 in-scope files, so the table needs no path filter. The output of the final run is in [docs/testing/evidence/in-scope-coverage.txt](docs/testing/evidence/in-scope-coverage.txt).

## 6. Static analysis, style and type checks
```bash
./vendor/bin/phpstan analyse                       # level 5 on the in-scope classes (phpstan.neon)
./vendor/bin/phpstan analyse --memory-limit=1G --no-progress
./vendor/bin/pint <files>                         # format the named PHP files (without a path it rewrites the whole repository)
# check only, on the files the study covers (the whole repository has older style issues outside the scope)
./vendor/bin/pint --test app/Http/Controllers/TicketPurchaseController.php app/Http/Controllers/DiscountController.php \
  app/Http/Controllers/PaymentController.php app/Http/Controllers/PaystackController.php app/Http/Controllers/TicketController.php \
  app/Http/Controllers/CheckInController.php app/Models/Discount.php app/Models/Order.php app/Models/Payment.php app/Models/Ticket.php \
  app/Models/TicketType.php app/Services/PaystackService.php app/Services/PaystackBalanceService.php database/factories tests
npm run lint                                       # ESLint, fixes files
npx eslint resources/js/pages/EventDetails.vue resources/js/pages/TicketBuyerForm.vue resources/js/pages/TicketVerification.vue \
  resources/js/pages/AttendeeCheckIn.vue resources/js/components/TicketSelector.vue --no-fix --max-warnings 0   # check only, as in CI
npm run format                                     # Prettier
npm run format:check
npx vue-tsc --noEmit                               # type check; CI allows at most 26 errors, the current count is 21
```

## 7. Mutation testing
```bash
# one class, covered lines only
php -d pcov.enabled=1 -d pcov.directory=app ./vendor/bin/pest --mutate --covered-only --class='App\Models\Discount' --testsuite=Unit,Feature

# one file by path
php -d pcov.enabled=1 -d pcov.directory=app ./vendor/bin/pest --mutate --covered-only --parallel --path=app/Models/Discount.php --testsuite=Unit,Feature

# all 13 in-scope files with the 60% gate used in CI
php -d pcov.enabled=1 -d pcov.directory=app ./vendor/bin/pest --mutate --covered-only --parallel --min=60 --testsuite=Unit,Feature \
  --path='app/Http/Controllers/TicketPurchaseController.php,app/Http/Controllers/DiscountController.php,app/Http/Controllers/PaymentController.php,app/Http/Controllers/PaystackController.php,app/Http/Controllers/TicketController.php,app/Http/Controllers/CheckInController.php,app/Models/Discount.php,app/Models/Order.php,app/Models/Payment.php,app/Models/Ticket.php,app/Models/TicketType.php,app/Services/PaystackService.php,app/Services/PaystackBalanceService.php'
```
Results: [docs/final/MUTATION.md](docs/final/MUTATION.md).

## 8. Performance measurements
The performance cases (P-01 to P-06) run with the Feature suite and append their figures to a log:
```bash
./vendor/bin/pest tests/Feature/Performance
tail storage/logs/performance.jsonl
```
Results: [docs/final/performance.md](docs/final/performance.md).

## 9. Reproducing the baseline
The baseline is the commit that holds only the inherited tests (the case-study tests sit in `tests/_disabled`) with the in-scope coverage setting in `phpunit.xml`:
```bash
git switch --detach a36bd12
php -d pcov.enabled=1 -d pcov.directory=app ./vendor/bin/pest --testsuite=Unit,Feature --coverage-clover=coverage/clover.xml
git switch original-scope
```
The stored output is in [docs/baseline/](docs/baseline/) (`original-run.txt`, `original-scope-coverage.txt`, `coverage-summary.txt`, `clover-original.xml`).

## 10. Regenerating the evidence files
```bash
php -d pcov.enabled=1 -d pcov.directory=app ./vendor/bin/pest --colors=never --coverage-clover=coverage/clover.xml > docs/testing/evidence/full-run-after-fixes.txt
php scripts/check-scope-coverage.php coverage/clover.xml 80
./vendor/bin/pest --colors=never tests/Feature/Auth tests/Feature/DashboardTest.php tests/Feature/EventPartnerTest.php tests/Feature/RsvpHoneypotTest.php tests/Feature/RsvpWaitlistTest.php
```
`docs/testing/evidence/failing-tests-before-fixes.txt` was taken from the last command before the nine files were fixed, so it cannot be reproduced on the current code.

## 11. Build and containers
```bash
npm run build                                      # production assets (also writes bootstrap/ssr)
npm run build:ssr
php artisan ziggy:generate
docker compose up -d --build
```

## 12. Paystack
```bash
php artisan paystack:sync-subaccounts              # pull fresh subaccount data from Paystack
php artisan paystack:sync-subaccounts --dry-run    # test without changing anything
php artisan orders:cleanup-incomplete --timeout=15 # remove unpaid orders older than 15 minutes
php artisan orders:cleanup-incomplete --dry-run
```
