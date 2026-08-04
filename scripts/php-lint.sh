#!/usr/bin/env bash
# Lint every theme PHP file. This intentionally needs only the PHP CLI.
set -euo pipefail

if ! command -v php >/dev/null 2>&1; then
  echo 'PHP CLI is required. Install PHP 8.0 or newer and retry.' >&2
  exit 1
fi

status=0
while IFS= read -r -d '' file; do
  printf 'PHP lint: %s\n' "${file#./}"
  php -l "$file" || status=1
done < <(find . -type f -name '*.php' \
  ! -path './node_modules/*' \
  ! -path './vendor/*' \
  ! -path './release/*' \
  -print0)

exit "$status"
