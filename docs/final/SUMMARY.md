# Before and after

Baseline: commit `da57882` (clean test database, Pest 4, pcov; `docs/baseline/clover-cleandb.xml`). Final: the commit tagged `final`. Figures below come from the files in this folder.

| Measure | Baseline | Final | Target |
|---|---|---|---|
| Tests (passing / failing) | 62 (56 / 6) | 350 (349 / 0, 1 opt-in test skipped) | 100% passing |
| Test levels | Unit and feature (none on the three flows) | Unit, feature, concurrency simulation, performance, 7 browser tests | Multi-level |
| Line coverage, in-scope classes | 9 of 1,885 (0.5%) | 1,709 of 1,852 (92.3%) | At least 80% |
| Line coverage, whole application | 646 of 4,958 (13.0%) | 2,602 of 4,926 (52.8%) | |
| Method coverage, whole application | 44 of 367 (12.0%) | 149 of 365 (40.8%) | |
| Mutation score, in-scope classes | Not measured | 1,431 of 1,935 mutants killed (74.0%) | At least 60% |
| PHPStan level 5, in-scope classes | Not run (105 errors on first run) | 0 errors | 0 errors |
| Pint, in-scope files, factories and tests | Needed changes | 0 files | 0 files |
| Pint, whole repository | 133 files | 121 files | No new errors |
| ESLint, five in-scope front-end files | 30 errors | 0 errors | 0 errors |
| ESLint, whole repository | 2,381 errors | 2,349 errors | No new errors |
| vue-tsc errors | 26 | 26 | No new errors |
| Scan check response time, 95th percentile | Not measured | 10 to 11 ms across runs (local, indicative) | Under 100 ms |
| Oversold tickets, 20 buyers for 5 tickets | Not tested | 0 | 0 |
| Defects found and fixed | 0 | 28 code defects (9 High, 14 Medium, 5 Low) | |

Files: `test-run.txt`, `clover.xml`, `scope-coverage.txt`, `phpstan.txt`, `pint.txt`, `frontend.txt`, `MUTATION.md`, `performance.md`. Defects: `../defects/DEFECT_LOG.md`.
