#!/usr/bin/env bash
# Build dist/bp-activity-social-share-{version}.zip (runs local CI first).
set -euo pipefail
cd "$(dirname "$0")/.."
slug=buddypress-activity-share-pro
npm run build --silent
bin/local-ci.sh
version=$(grep -m1 -E '^\s*\*\s*Version:' buddypress-share.php | awk '{print $NF}')
rm -rf dist && mkdir -p "dist/$slug"
rsync -a --exclude-from=<(sed 's#^/##' .distignore) --exclude=dist ./ "dist/$slug/"
( cd dist && zip -qr "$slug-$version.zip" "$slug" )
leaks=$(unzip -l "dist/$slug-$version.zip" | grep -iE '/docs/|/audit/|/bin/|/tests/|node_modules|CLAUDE|\.md$' || true)
[ -z "$leaks" ] || { echo "Zip contains dev files:"; echo "$leaks"; exit 1; }
echo "Built dist/$slug-$version.zip"
