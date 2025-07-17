
# Path to PHP executable
PHP_BIN=$(which php)

# Path to cron.php script
SCRIPT_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/cron.php"

# Add cron job if not exists
(crontab -l 2>/dev/null | grep -v "$SCRIPT_PATH" ; echo "*/5 * * * * $PHP_BIN $SCRIPT_PATH >/dev/null 2>&1") | crontab -

echo "Cron job installed: Runs cron.php every 5 minutes."
