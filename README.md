# Suptix: Software Quality Assurance Case Study

This repository is a case study in software quality assurance and testing (module 7020SCN). It takes Suptix, a Laravel 12 and Vue 3 event ticketing platform, and applies a risk-based test process to the three flows that carry revenue, payment trust and event entry. It holds the application, the automated tests, the test documentation and the evidence behind the report.

---

## Table of Contents

- [Case Study Overview](#case-study-overview)
- [Headline Results](#headline-results)
- [Setup and Testing Guide for Markers](#setup-and-testing-guide-for-markers)
- [Testing and Quality Assurance](#testing-and-quality-assurance)
  - [Running the tests](#running-the-tests)
  - [Test results](#test-results)
  - [Quality documentation](#quality-documentation)
  - [Continuous integration](#continuous-integration)
- [Repository Map](#repository-map)
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

## Case Study Overview

**Aim.** Measure the quality of the three business-critical flows, close the gaps with targeted automated tests, and show the gain with measured results.

| Flow | What it protects | In-scope classes |
|---|---|---|
| F1 Purchase and pricing | Correct charges, no overselling | `TicketPurchaseController`, `DiscountController`, `Discount`, `Order`, `Ticket`, `TicketType` |
| F2 Payment confirmation | Reliable, secure payment and ticket delivery | `PaymentController`, `PaystackController`, `PaystackService`, `PaystackBalanceService`, `Payment` |
| F3 Ticket validation and entry | One entry per ticket, organiser separation | `TicketController`, `CheckInController`, `Ticket` |

**Out of scope:** Mobile API v2, the Filament admin panel, and the Providers, Console and Exceptions directories. Coverage is reported for the 13 in-scope classes only, never for the whole application.

**Approach.** The work follows the ISO/IEC 29119 document set: a [test strategy](docs/testing/TEST_STRATEGY.md), a [test plan](docs/testing/TEST_PLAN.md), a [54-case catalogue](docs/testing/TEST_CASES.md) and an execution record ([results](docs/testing/TEST_RESULTS.md)). Tests are ranked by risk (Priority 1 blocks sign-off), run against an isolated database with faked Paystack, mail and queue services, and gated in CI. Concurrency tests are simulations, because transactions hide uncommitted rows from other processes.

**Branches.** `original-scope` holds the full study. The baseline is commit `a36bd12` (the inherited tests only). `master` on the Coventry repository follows `original-scope`.

---

## Headline Results

Final run on `original-scope`, all suites (details in [Test results](#test-results)):

| Measure | Baseline | After |
|---|---:|---:|
| Tests passed | 114 of 135 | 422 of 423 (1 skipped) |
| Tests failed | 21 | 0 |
| In-scope line coverage | 0.5% | 91.4% |
| Defects found and fixed | n/a | 29 |

---

## Setup and Testing Guide for Markers

This guide takes a clean machine to a full test run. It needs about 15 minutes plus the downloads. Commands are for macOS or Linux; PowerShell equivalents are given where they differ. The expected output of each step is shown, so you can tell at once whether it worked.

### What you need

| Tool | Version | Notes |
|---|---|---|
| Git | any | to clone the repository |
| PHP | 8.4 | extensions Composer asks for (for example `mbstring`, `intl`, `gd`, `zip`, `bcmath`, `pdo_mysql`); `pcov` is needed only for the coverage step |
| Composer | 2.x | |
| Node.js and npm | 22 | |
| MySQL | 8.4 | or Docker, which supplies one (step 4) |

### Step 1: Get the code
```bash
git clone https://github.coventry.ac.uk/Aikinsr/suptix.git
cd suptix
git switch master        # the default branch; it holds the finished work
```

### Step 2: Install dependencies
```bash
composer install
npm ci
```
Expected: both finish without errors.

### Step 3: Create the environment file
```bash
cp .env.example .env
php artisan key:generate
```

### Step 4: Start a MySQL database
The tests need a MySQL database called `suptix_test`. Use Docker (option A) or a MySQL you already run (option B).

**Option A: Docker**
```bash
docker run -d --name suptix-mysql -e MYSQL_ROOT_PASSWORD=secret -e MYSQL_DATABASE=suptix_test -p 3306:3306 mysql:8.4
```
Wait about 30 seconds for MySQL to start (`docker logs suptix-mysql` ends with "ready for connections").

**Option B: your own MySQL**
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS suptix_test"
```

### Step 5: Tell the tests how to connect
The repository's `.env.testing` holds the author's own local database password, so give the tests your credentials through environment variables for this terminal session. They take priority over the file. Use the password you set in step 4 (`secret` for option A).

macOS or Linux:
```bash
export DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=3306 DB_DATABASE=suptix_test DB_USERNAME=root DB_PASSWORD=secret
```
PowerShell:
```powershell
$env:DB_CONNECTION="mysql"; $env:DB_HOST="127.0.0.1"; $env:DB_PORT="3306"; $env:DB_DATABASE="suptix_test"; $env:DB_USERNAME="root"; $env:DB_PASSWORD="secret"
```
`DB_DATABASE` must be `suptix_test`. The tests never use any other database.

### Step 6: Create the tables
```bash
php artisan migrate --env=testing --force
```
Expected: a list of migrations marked `DONE`.

### Step 7: Build the front end and install the test browser
```bash
php artisan ziggy:generate
npm run build
npx playwright install chromium      # about 200 MB; only the browser tests need it
```

### Step 8: Run all the tests
```bash
php artisan test
```
Expected (about 1 to 2 minutes):
```
Tests:    1 skipped, 422 passed (2013 assertions)
```
The skipped test is the live Paystack checkout, which needs a Paystack account and is switched off on purpose. To skip the browser tests, run `php artisan test --testsuite=Unit,Feature`, which gives `Tests:    415 passed (1970 assertions)`.

### Step 9: Check the in-scope coverage
This needs the `pcov` PHP extension (`php -m | grep pcov`).
```bash
mkdir -p coverage
php -d pcov.enabled=1 -d pcov.directory=app ./vendor/bin/pest --coverage-clover=coverage/clover.xml
php scripts/check-scope-coverage.php coverage/clover.xml 80
```
Expected last line:
```
In-scope line coverage: 1720 / 1881 = 91.4% (threshold 80%)
```
The script lists the 13 in-scope classes with their coverage and exits with an error below 80%.

### Step 10: Run the static checks
```bash
./vendor/bin/phpstan analyse                      # expected: [OK] No errors
npx vue-tsc --noEmit | grep -c "error TS"         # expected: 21 (the limit is 26)
```
The style and lint commands are in [dev-notes.md](dev-notes.md#6-static-analysis-style-and-type-checks): Pint passes on the in-scope files and ESLint passes on the five in-scope pages.

### Step 11 (optional): Look at the evidence and the baseline
- The recorded output of the final run is in [docs/testing/evidence/](docs/testing/evidence/), and the results are summarised under [Test results](#test-results) below.
- To see the baseline (the inherited tests only, 21 failing and 0.5% in-scope coverage), follow [dev-notes.md](dev-notes.md#9-reproducing-the-baseline).
- Mutation testing takes about ten minutes: [dev-notes.md](dev-notes.md#7-mutation-testing).

### If something goes wrong

| Symptom | Cause and fix |
|---|---|
| `Access denied for user 'root'` | The variables from step 5 are not set in this terminal, or the password is wrong. Set them again |
| `SQLSTATE ... Connection refused` | MySQL is not running or not on port 3306; check `docker ps` or your MySQL service |
| `PlaywrightOutdatedException` or browser tests fail at start | Run `npx playwright install chromium` again |
| The purchase browser tests hang or the page is blank | Run `php artisan ziggy:generate` and `npm run build`, then rerun |
| `Clover file not found` or coverage near 0% | `pcov` is missing or not enabled; install it, and use the exact command in step 9 |
| `Unable to locate file in Vite manifest` | Run `npm run build` |

---

## Testing and Quality Assurance

### Running the tests
```bash
php artisan test                                   # Unit, Feature and Browser
php artisan test --testsuite=Unit,Feature          # without the browser tests
php -d pcov.enabled=1 -d pcov.directory=app ./vendor/bin/pest --coverage-clover=coverage/clover.xml
php scripts/check-scope-coverage.php coverage/clover.xml 80     # in-scope gate
./vendor/bin/phpstan analyse                       # static analysis, level 5
```
All test, browser, coverage, mutation and analysis commands are in [dev-notes.md](dev-notes.md) ([running tests](dev-notes.md#3-running-tests), [browser tests](dev-notes.md#4-browser-tests), [coverage](dev-notes.md#5-coverage), [static analysis and style](dev-notes.md#6-static-analysis-style-and-type-checks), [mutation testing](dev-notes.md#7-mutation-testing)). Tests use the `suptix_test` database and `DatabaseTransactions`, never `RefreshDatabase`.

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
- [Report tiny draft](docs/report/Suptix_SQA_Report_Tiny_Draft.docx): the chapter structure and writing guide the report follows.
- System design diagrams: [System_Design.mmd](docs/design/System_Design.mmd) and [System_Design_Sequence.mmd](docs/design/System_Design_Sequence.mmd).
- The CI pipeline and its gates are described in [Continuous integration](#continuous-integration).

### Continuous integration
[.github/workflows/ci.yml](.github/workflows/ci.yml) runs on pushes to `master`, `staging` and `original-scope`, on pull requests into `master`, and on manual dispatch. It has three jobs:

| Job | Checks | Gate |
|---|---|---|
| Style and static analysis | Pint, PHPStan level 5, ESLint on the in-scope pages, vue-tsc | Any failure fails the build; vue-tsc may not exceed 26 errors |
| Tests and coverage | Migrate the test database, run every suite with PCOV | In-scope line coverage of at least 80% ([script](scripts/check-scope-coverage.php)) |
| Mutation testing | Pest mutation tests on the 13 in-scope files (pushes and manual runs only) | Mutation score of at least 60% |

Recorded CI runs are in [docs/final/CI_EVIDENCE.md](docs/final/CI_EVIDENCE.md).

---

## Repository Map

| Path | Contents |
|---|---|
| `app/` | The application; the 13 in-scope classes are listed in [Appendix C](#appendix-c-in-scope-classes-and-coverage) |
| `tests/Unit`, `tests/Feature`, `tests/Browser` | Automated tests (Pest 4) |
| `docs/testing/` | Test strategy, plan, case catalogue, results and the raw run output |
| `docs/defects/` | Defect log and the test run taken before any fix |
| `docs/baseline/`, `docs/final/` | Baseline coverage files and earlier final measurements |
| `docs/design/`, `docs/report/` | Design diagram sources and the report tiny draft |
| `scripts/check-scope-coverage.php` | In-scope coverage gate |
| `phpunit.xml`, `phpstan.neon` | Test and analysis configuration (scope set to the 13 classes) |
| `.github/workflows/ci.yml` | CI pipeline |
| `dev-notes.md` | Every command, grouped by task |

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
| [docs/report/Suptix_SQA_Report_Tiny_Draft.docx](docs/report/Suptix_SQA_Report_Tiny_Draft.docx) | Tiny draft: the chapter-by-chapter writing guide the report follows (chapters 1 to 5) |
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
| Style check (in-scope files) | see the command in dev-notes | [dev-notes §6](dev-notes.md#6-static-analysis-style-and-type-checks) |
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

[docs/final/](docs/final/) holds the figures from the earlier final run, taken before the original features were restored: 350 tests (349 passed, 1 skipped), in-scope coverage 92.3% (1,709 of 1,852 lines) and a mutation score of 74.0%. The results in [Test results](#test-results) come from the later run, so the totals differ slightly. The mutation, performance and CI figures were not repeated on the later run, so they are cited from `docs/final/`.

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
