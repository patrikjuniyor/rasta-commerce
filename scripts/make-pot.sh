#!/usr/bin/env bash
# Generate fresh translation templates for both the theme and bundled gateway.
set -euo pipefail

if ! command -v wp >/dev/null 2>&1; then
  echo 'WP-CLI is required: https://wp-cli.org/' >&2
  exit 1
fi

wp i18n make-pot . languages/rasta-commerce.pot \
  --domain=rasta-commerce \
  --exclude=node_modules,vendor,release,marketplace,plugins,tests,demo

wp i18n make-pot plugins/rasta-zarinpal-gateway \
  plugins/rasta-zarinpal-gateway/languages/rasta-zarinpal-gateway.pot \
  --domain=rasta-zarinpal-gateway
