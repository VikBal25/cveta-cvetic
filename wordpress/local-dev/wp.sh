#!/bin/sh
# WP-CLI for the local site, without PHP 8.5 deprecation noise.
cd "$(dirname "$0")/../../wp-local" && wp "$@" 2>&1 | grep -v -e '^Deprecated:' -e '^PHP Deprecated:' -e '^$'
