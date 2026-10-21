# CI evidence (GitHub Actions, repository richprince23/suptix-test)

| Run | What it shows |
|---|---|
| 39525966093 | First full run of `ci.yml` on the quality branch: style and static analysis, tests with coverage (349 passed, 1 skipped, 1,846 assertions, in-scope coverage gate above 80%) and mutation testing all passed. The browser tests ran in CI. |
| 39533225880 | Run after the mutation step was limited to the 13 in-scope files: all three jobs passed; mutation score 73.23% (1,417 of 1,935 mutants killed) against the 60% gate. |
| 39542535749 | A push of a deliberate style error (commit `be288a4`, a badly spaced method signature in `app/Models/Discount.php`) to a `staging` branch: the **Style and static analysis** job failed at the **Pint (check only)** step, so the gate blocks a bad change. The temporary branch was deleted afterwards. |

The mutation job does not run on pull requests, only on pushes and manual runs, because it takes about ten minutes.
