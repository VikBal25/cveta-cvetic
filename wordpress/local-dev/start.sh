#!/bin/sh
# Starts the local WordPress: MariaDB (if not running) + PHP built-in server on http://localhost:8080
DIR="$(cd "$(dirname "$0")" && pwd)"
mysqladmin ping >/dev/null 2>&1 || mysql.server start
cd "$DIR/../../wp-local" || exit 1
echo "WordPress: http://localhost:8080  (admin: http://localhost:8080/wp-admin)"
exec php -d error_reporting=8191 -d display_errors=0 -d log_errors=1 -d upload_max_filesize=64M -d post_max_size=64M -d memory_limit=512M -S localhost:8080 "$DIR/router.php"
