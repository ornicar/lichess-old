#!/bin/sh
# Usage: ./lichess/reloadTestEnv.sh
rm -f /tmp/lichess.sqlite &&
php lichess/console-test doctrine:database:drop &&
php lichess/console-test doctrine:database:create &&
php lichess/console-test doctrine:schema:create &&
php lichess/console-test doctrine:generate:proxies &&
php lichess/console-test doctrine:data:load &&
echo "You're good to go!"
