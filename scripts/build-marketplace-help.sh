#!/usr/bin/env bash
# Build the Persian marketplace manual from its local HTML source.
set -euo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
source_file="$root/marketplace/help-source.html"
output_file="$root/marketplace/help.pdf"

if ! command -v chromium >/dev/null 2>&1; then
  echo 'Chromium is required to build marketplace/help.pdf.' >&2
  exit 1
fi

chromium --headless --no-sandbox --disable-gpu \
  --print-to-pdf="$output_file" \
  "file://$source_file"

echo "Marketplace help PDF created: ${output_file#$root/}"
