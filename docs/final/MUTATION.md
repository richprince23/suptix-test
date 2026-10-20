# Mutation testing (Pest, covered lines of each in-scope class)

Run 1 (before the contract tests): 1,006 of 1,862 mutants killed, 54.0%. Run 2 (after): below.

| Class | Killed | Survived | Score |
|---|---|---|---|
| `app/Models/Discount.php` | 29 | 3 | 90.63% |
| `app/Models/Order.php` | 14 | 0 | 100.00% |
| `app/Models/Payment.php` | 0 | 0 | no mutants (relations only) |
| `app/Models/Ticket.php` | 20 | 0 | 100.00% |
| `app/Models/TicketType.php` | 16 | 0 | 100.00% |
| `app/Http/Controllers/CheckInController.php` | 14 | 0 | 100.00% |
| `app/Http/Controllers/DiscountController.php` | 57 | 12 | 82.61% |
| `app/Http/Controllers/PaystackController.php` | 42 | 0 | 100.00% |
| `app/Http/Controllers/TicketPurchaseController.php` | 157 | 34 | 82.20% |
| `app/Http/Controllers/PaymentController.php` | 121 | 49 | 71.18% |
| `app/Services/PaystackBalanceService.php` | 276 | 113 | 70.95% |
| `app/Services/PaystackService.php` | 307 | 191 | 61.65% |
| `app/Http/Controllers/TicketController.php` | 378 | 102 | 78.75% |
| **All in-scope classes** | **1431** | **504** | **74.0%** |

Command per class: `php -d pcov.enabled=1 -d pcov.directory=app ./vendor/bin/pest --mutate --covered-only --parallel --path=<file> --testsuite=Unit,Feature`.
Survivors are mostly array items inside log context and fields of responses that no test pins, plus equivalent mutants. Raw output: `mutation-final.txt`; first run summary: `mutation-run1-summary.txt`.
