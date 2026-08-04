#!/usr/bin/env bash
# Generate a fresh translation template when WP-CLI is available.
set -euo pipefail

if ! command -v wp >/dev/null 2>&1; then
  echo 'WP-CLI is required: https://wp-cli.org/' >&2
  exit 1
fi

wp i18n make-pot . languages/rasta-commerce.pot \
  --domain=rasta-commerce \
  --exclude=node_modules,vendor,release,demo
