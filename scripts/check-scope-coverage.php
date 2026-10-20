<?php

/*
 * Reads a PHPUnit clover file and checks the line coverage of the classes in
 * scope for the quality study (flows F1 to F3). Exits 1 below the threshold.
 *
 * Usage: php scripts/check-scope-coverage.php <clover.xml> [threshold percent]
 */

$file = $argv[1] ?? 'coverage/clover.xml';
$threshold = (float) ($argv[2] ?? 80);

$inScope = [
    'app/Http/Controllers/TicketPurchaseController.php',
    'app/Http/Controllers/DiscountController.php',
    'app/Http/Controllers/PaymentController.php',
    'app/Http/Controllers/PaystackController.php',
    'app/Http/Controllers/TicketController.php',
    'app/Http/Controllers/CheckInController.php',
    'app/Models/Discount.php',
    'app/Models/Order.php',
    'app/Models/Payment.php',
    'app/Models/Ticket.php',
    'app/Models/TicketType.php',
    'app/Services/PaystackService.php',
    'app/Services/PaystackBalanceService.php',
];

if (! is_file($file)) {
    fwrite(STDERR, "Clover file not found: {$file}\n");
    exit(2);
}

$xml = simplexml_load_file($file);
$covered = $total = 0;
$rows = [];

foreach ($xml->xpath('//file') as $node) {
    $path = (string) $node['name'];
    foreach ($inScope as $wanted) {
        if (! str_ends_with($path, $wanted)) {
            continue;
        }
        $metrics = $node->metrics;
        $statements = (int) $metrics['statements'];
        $hit = (int) $metrics['coveredstatements'];
        $covered += $hit;
        $total += $statements;
        $rows[$wanted] = [$hit, $statements];
    }
}

ksort($rows);
foreach ($rows as $path => [$hit, $statements]) {
    printf("%-58s %4d / %4d  %5.1f%%\n", $path, $hit, $statements, $statements ? 100 * $hit / $statements : 100);
}

$percent = $total ? 100 * $covered / $total : 0;
printf("\nIn-scope line coverage: %d / %d = %.1f%% (threshold %.0f%%)\n", $covered, $total, $percent, $threshold);

if (count($rows) !== count($inScope)) {
    fwrite(STDERR, 'Some in-scope files are missing from the clover report.'."\n");
    exit(2);
}

exit($percent >= $threshold ? 0 : 1);
