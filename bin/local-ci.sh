#!/usr/bin/env bash
# Local CI for Activity Share Pro. Exit 1 on any failure.
set -uo pipefail
cd "$(dirname "$0")/.."
fail=0
step() { printf '\n== %s\n' "$1"; }
bad() { echo "FAIL: $1"; fail=1; }

step "PHP lint"
while IFS= read -r f; do php -l "$f" >/dev/null || bad "lint $f"; done < <(find . -name '*.php' -not -path './node_modules/*' -not -path './libs/*')

step "WPCS (errors)"
phpcs -q -n || bad "WPCS errors"

step "Pro never uses Free internals (only functions.php API + hooks)"
leaks=$(grep -rnE 'BPAS\\\\[A-Z]' includes templates --include='*.php' | grep -v 'BPAS_Pro' || true)
[ -z "$leaks" ] || { echo "$leaks"; bad "direct BPAS\\ class use"; }

step "Versions (header, constant, readme, package, Free)"
hv=$(grep -m1 -E '^\s*\*\s*Version:' buddypress-share.php | awk '{print $NF}')
cv=$(grep -m1 "define( 'BPAS_PRO_VERSION'" buddypress-share.php | grep -oE "[0-9]+\.[0-9]+\.[0-9]+")
rv=$(grep -m1 -i 'Stable tag' readme.txt | tr -d '\r' | awk '{print $NF}')
pv=$(node -p "require('./package.json').version")
fv=$(grep -m1 -E '^\s*\*\s*Version:' ../bp-activity-social-share/buddypress-share.php 2>/dev/null | awk '{print $NF}')
echo "header=$hv const=$cv readme=$rv package=$pv free=${fv:-missing}"
[ "$hv" = "$cv" ] && [ "$cv" = "$rv" ] && [ "$rv" = "$pv" ] || bad "version mismatch"
[ -z "$fv" ] || [ "$fv" = "$hv" ] || bad "Free $fv != Pro $hv (always the same version)"

step "BuddyBoss-safe URLs (bp_members_get_user_url / bp_get_group_url are BuddyPress 12+ only)"
grep -rn 'bp_members_get_user_url\|bp_get_group_url' --include='*.php' includes templates | grep -v 'includes/functions.php' && bad "use bpas_member_url() / bpas_group_url()"

step "No inline <script>/<style> in PHP"
grep -rln '<script\|<style' --include='*.php' includes templates && bad "inline script/style"

step "EDD SDK template guard present"
grep -q "Wbcom guard" libs/edd-sl-sdk/src/Handlers/Handler.php || bad "SDK LFI guard missing"
grep -q "wp_ajax_edd_sdk_get_notice_buddypress-activity-share-pro" includes/licensing.php || bad "plugin-level SDK guard missing"

step "Plan copy in sync with Free"
[ -f ../bp-activity-social-share/docs/plans/3.6.0-PLAN.md ] && { diff -q docs/plans/3.6.0-PLAN.md ../bp-activity-social-share/docs/plans/3.6.0-PLAN.md >/dev/null || bad "plan copies differ"; }

step "i18n: POT builds without warnings"
out=$(wp i18n make-pot . /tmp/bpas-pro-ci.pot --domain=buddypress-activity-share-pro '--exclude=node_modules,bin,docs,libs' 2>&1)
echo "$out" | grep -qi warning && { echo "$out"; bad "make-pot warnings"; }

step "Assets built"
for f in assets/css/bpas-pro.min.css assets/css/bpas-pro-rtl.min.css assets/js/bpas-pro.min.js assets/js/bpas-pro-admin.min.js; do [ -f "$f" ] || bad "missing $f (npm run build)"; done

[ $fail -eq 0 ] && echo -e "\nALL CHECKS PASSED" || echo -e "\nCHECKS FAILED"
exit $fail
