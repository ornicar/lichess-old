#!/bin/sh
# Usage: ./lichess/reloadDevEnv.sh
php lichess/console doctrine:database:drop &&
php lichess/console doctrine:database:create &&
php lichess/console doctrine:schema:create &&
php lichess/console doctrine:generate:proxies &&
php lichess/console doctrine:data:load &&
echo "You're good to go!"
