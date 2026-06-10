#!/usr/bin/env bash
# run-tests.sh — Run PHPUnit tests for magento-2-blog module only
# Usage:
#   ./run-tests.sh              — run all tests
#   ./run-tests.sh --filter <n> — run specific test
#   ./run-tests.sh --coverage   — run with HTML coverage report

set -e

# ── Auto-detect paths from script location ────────────────────────────────────
# MODULE_ROOT = directory containing this script
MODULE_ROOT="$(cd "$(dirname "$0")" && pwd)"

# MAGENTO_ROOT = walk up from MODULE_ROOT until vendor/autoload.php is found
MAGENTO_ROOT="$MODULE_ROOT"
while [ "$MAGENTO_ROOT" != "/" ]; do
    if [ -f "$MAGENTO_ROOT/vendor/autoload.php" ]; then
        break
    fi
    MAGENTO_ROOT="$(dirname "$MAGENTO_ROOT")"
done

if [ ! -f "$MAGENTO_ROOT/vendor/autoload.php" ]; then
    echo "❌ Cannot find Magento root (vendor/autoload.php not found)"
    exit 1
fi

PHPUNIT="$MAGENTO_ROOT/vendor/bin/phpunit"
CONFIG="$MODULE_ROOT/phpunit.xml"
COVERAGE_DIR="$MODULE_ROOT/Test/coverage"

# ── Parse --coverage flag ──────────────────────────────────────────────────────
COVERAGE=0
EXTRA_ARGS=()
for arg in "$@"; do
    if [ "$arg" = "--coverage" ]; then
        COVERAGE=1
    else
        EXTRA_ARGS+=("$arg")
    fi
done

# ── Header ─────────────────────────────────────────────────────────────────────
echo ""
echo "========================================"
echo "  Mageplaza Blog — PHPUnit Test Runner"
echo "========================================"
echo "  Magento root : $MAGENTO_ROOT"
echo "  Test scope   : $MODULE_ROOT/Test/Unit"
echo "  Coverage     : $([ $COVERAGE -eq 1 ] && echo "yes → $COVERAGE_DIR" || echo "no")"
echo "========================================"
echo ""

# ── Run tests ──────────────────────────────────────────────────────────────────
if [ $COVERAGE -eq 1 ]; then
    # Check coverage driver
    if php -m 2>/dev/null | grep -qE "xdebug|pcov"; then
        mkdir -p "$COVERAGE_DIR"
        XDEBUG_MODE=coverage php "$PHPUNIT" -c "$CONFIG" --testdox \
            --coverage-html "$COVERAGE_DIR" \
            --coverage-text \
            "${EXTRA_ARGS[@]}"
        echo ""
        echo "✅ Coverage report: $COVERAGE_DIR/index.html"
    else
        echo "⚠️  No coverage driver found (need xdebug or pcov)."
        echo "   Inside DDEV: run 'ddev xdebug on' then retry."
        echo "   Running tests without coverage..."
        echo ""
        php "$PHPUNIT" -c "$CONFIG" --testdox "${EXTRA_ARGS[@]}"
    fi
else
    php "$PHPUNIT" -c "$CONFIG" --testdox "${EXTRA_ARGS[@]}"
fi

echo ""
