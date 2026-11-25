#!/bin/sh
set -e

if [ ! -f config.php ]; then
    cp sample-config.php config.php
fi

exec "$@"