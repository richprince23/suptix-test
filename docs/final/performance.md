# Performance measurements

Cases P-01 to P-06 in `tests/Feature/Performance/CheckoutAndScanPerformanceTest.php`. Each run appends its figures to `storage/logs/performance.jsonl`. Environment: PHP 8.4, Laravel 12, MySQL 8.4.5 on a development laptop, requests sent in-process through Laravel's test client (no network). The figures are indicative only.

| Case | Metric | Before fixes | After fixes | Target |
|---|---|---|---|---|
| P-01 | Queries to buy 1 ticket / 10 tickets | 13 / 22 | 13 / 22 | At most 2 extra queries per extra ticket (measured: 1.0) |
| P-02 | Queries to list the tickets of an event: 10 tickets / 500 tickets | 28 / 1,007 | 9 / 9 | Constant (met) |
| P-02 | Response time for 500 tickets | 997 ms | 78 ms | Record |
| P-03 | Scan of a ticket, 100 requests: median / 95th percentile | 8 ms / 11 ms | 8 ms / 11 ms | Median under 200 ms; 95th percentile under 100 ms (threshold set after the first measurement) |
| P-04 | Marking 100 distinct tickets as used: median / 95th percentile | 12 ms / 16 ms | 12 ms / 16 ms | Record |
| P-05 | One `charge.success` webhook | 47 ms | 47 ms | Record |
| P-06 | Tickets sold when 20 buyers compete for 5 tickets | 5 (0 oversold) | 5 (0 oversold) | 0 oversold (met) |

The one defect found by these cases is DEF-19 (P-02): the ticket resource read the event and organizer of every ticket lazily, costing two queries per ticket. Loading them in bulk removed it (commit `ba99801`).
