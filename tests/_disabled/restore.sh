#!/usr/bin/env bash
# Uncomment each file and move it back to tests/ (run from the repo root).
set -euo pipefail
cd "$(dirname "$0")/../.."
find tests/_disabled -name '*.php' | while read -r f; do
  rel=${f#tests/_disabled/}
  mkdir -p "tests/$(dirname "$rel")"
  python3 - "$f" "tests/$rel" <<'PY'
import sys
src,dst=sys.argv[1:]
out=[]
for i,l in enumerate(open(src).read().split('\n')):
    out.append(l[3:] if l.startswith('// ') else l)
open(dst,'w').write('\n'.join(out))
PY
  git rm -q -f "$f"
done
echo "Also uncomment the Browser testsuite in phpunit.xml."
rm -f tests/_disabled/README.md tests/_disabled/restore.sh
rmdir tests/_disabled 2>/dev/null || true
