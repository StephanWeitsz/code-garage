#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
STAMP="$(date +%Y%m%d-%H%M%S)"
WORK_DIR="$ROOT_DIR/.release/work-$STAMP"
DIST_DIR="$ROOT_DIR/.release/dist"
ZIP_PATH="$DIST_DIR/code-garage-release-$STAMP.zip"

if [[ ! -f "$ROOT_DIR/artisan" ]]; then
  echo "Run this script from inside the Laravel project."
  exit 1
fi

if [[ ! -f "$ROOT_DIR/public/build/manifest.json" ]]; then
  echo "Missing public/build/manifest.json"
  echo "Run npm run build locally before packaging."
  exit 1
fi

if [[ ! -f "$ROOT_DIR/vendor/autoload.php" ]]; then
  echo "Missing vendor/autoload.php"
  echo "Run composer install locally before packaging."
  exit 1
fi

mkdir -p "$WORK_DIR" "$DIST_DIR"

EXCLUDES=(
  ".git"
  ".release"
  "node_modules"
  ".env"
  ".env.*"
  "tests"
  "storage/logs/*"
  "storage/framework/cache/*"
  "storage/framework/sessions/*"
  "storage/framework/views/*"
  "bootstrap/cache/*.php"
  "sync.ffs_lock"
)

if command -v rsync >/dev/null 2>&1; then
  RSYNC_ARGS=(-a)
  for pattern in "${EXCLUDES[@]}"; do
    RSYNC_ARGS+=(--exclude "$pattern")
  done
  rsync "${RSYNC_ARGS[@]}" "$ROOT_DIR/" "$WORK_DIR/"
else
  echo "rsync not found, using tar fallback..."
  TAR_EXCLUDES=()
  for pattern in "${EXCLUDES[@]}"; do
    TAR_EXCLUDES+=(--exclude="$pattern")
  done

  (
    cd "$ROOT_DIR"
    tar -cf - "${TAR_EXCLUDES[@]}" .
  ) | (
    cd "$WORK_DIR"
    tar -xf -
  )
fi

mkdir -p "$WORK_DIR/storage/logs" "$WORK_DIR/storage/framework/cache/data" "$WORK_DIR/storage/framework/sessions" "$WORK_DIR/storage/framework/views" "$WORK_DIR/bootstrap/cache"

(
  cd "$WORK_DIR"
  zip -qr "$ZIP_PATH" .
)

echo "Release package created:"
echo "  $ZIP_PATH"
echo
echo "Upload and extract this zip to your hosting root."
echo "After deploy, open /deployment-tools and run:"
echo "  1) optimize:clear"
echo "  2) storage:link"
echo "  3) filament:upgrade (if admin/lecturer pages are blank)"
