# Suptix
## Instructions & Guidelines

Repo Structure
- master branch is the live branch
- dev branch is for local development 
- staging branch is for testing dev branch and hotfixing
- feature/individual branches for individual or feature based dev. 
PS: This is where we work and merge upwords

php -d pcov.enabled=1 -d pcov.directory=app artisan test --coverage --coverage-html coverage 2>&1 | tail -80

composer run test:browser:watch
BROWSER_DEMO=1 ./vendor/bin/pest tests/Browser/TicketPurchaseFlowTest.php --filter="lets a guest" --headed

composer run test:browser:paystack
PAYSTACK_LIVE=1 BROWSER_DEMO=1 ./vendor/bin/pest tests/Browser/PaystackTestModeCheckoutTest.php --headed


mkdir -p coverage

php -d pcov.enabled=1 -d pcov.directory=app artisan test \
  --coverage-clover=coverage/clover.xml >/dev/null 2>&1 &&
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
    "CheckInController.php",
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
