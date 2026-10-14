#!/usr/bin/env bash
# Run PHP with WordPress loaded, against this theme's Local (localwp.com) site.
# No wp-cli needed: finds the site's MySQL socket in Local's sites.json.
#
#   _dev/tools/wp/run.sh path/to/script.php     run a file (WP, ACF and the theme are loaded)
#   _dev/tools/wp/run.sh -e 'echo home_url();'  run inline code
#
# See _dev/docs/wp-scripting.md for recipes (ACF field groups, menus, block content).
set -euo pipefail

THEME_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../.." && pwd)"
WP_ROOT="$(cd "$THEME_ROOT/../../.." && pwd)"
LOCAL_DIR="${LOCAL_DIR:-$HOME/Library/Application Support/Local}"

if [[ $# -lt 1 ]]; then
  sed -n '2,8p' "$0"
  exit 1
fi

# Match the Local site whose folder contains this theme → "<id> <domain>".
read -r SITE_ID SITE_DOMAIN < <(WP_ROOT="$WP_ROOT" LOCAL_DIR="$LOCAL_DIR" node -e '
  const { homedir } = require("os");
  const sites = require(process.env.LOCAL_DIR + "/sites.json");
  const site = Object.values(sites).find((s) =>
    process.env.WP_ROOT.startsWith(s.path.replace(/^~/, homedir()) + "/"));
  if (!site) { console.error("No Local site contains " + process.env.WP_ROOT); process.exit(1); }
  console.log(site.id, site.domain);
')

SOCKET="$LOCAL_DIR/run/$SITE_ID/mysql/mysqld.sock"
if [[ ! -S "$SOCKET" ]]; then
  echo "MySQL socket not found ($SOCKET) — is the site running in Local?" >&2
  exit 1
fi

if [[ "$1" == "-e" ]]; then
  SCRIPT="$(mktemp -t elysian-wp).php"
  trap 'rm -f "$SCRIPT"' EXIT
  printf '<?php\n%s\n' "${2:?-e needs PHP code}" > "$SCRIPT"
else
  SCRIPT="$(cd "$(dirname "$1")" && pwd)/$(basename "$1")"
fi

WP_ROOT="$WP_ROOT" WP_HOST="$SITE_DOMAIN" php \
  -d "mysqli.default_socket=$SOCKET" \
  -d "pdo_mysql.default_socket=$SOCKET" \
  "$THEME_ROOT/_dev/tools/wp/bootstrap.php" "$SCRIPT"
