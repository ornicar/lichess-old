INSTALL
-------

To configure your DB for your development and test environments, edit your `/lichess/config/config_dev_local.yml` and `/lichess/config/config_test_local.yml` to add your specific DB settings:

    imports:
      - { resource: config_dev.yml }

    doctrine.dbal:
      connections:
        default:
          driver:               PDOMySql
          dbname:               lichess
          user:                 root
          password:             changeme
          host:                 localhost
          port:                 ~

Create your database and tables

    php lichess/console doctrine:database:drop
    php lichess/console doctrine:database:create
    php lichess/console doctrine:schema:create

    php lichess/console-test doctrine:database:drop
    php lichess/console-test doctrine:database:create
    php lichess/console-test doctrine:schema:create

Generate the doctrine proxies

    php lichess/console doctrine:generate:proxies
    php lichess/console-test doctrine:generate:proxies

Load fixtures

    php lichess/console doctrine:data:load
    php lichess/console-test doctrine:data:load

The easy way: create database, generate proxies and load fixtures in one single command

    php lichess/reloadDev.sh // dev environment
    php lichess/reloadTest.sh // test environment
  
Run unit tests

    phpunit src/Bundle/LichessBundle/Tests/AllTests.php

Run functional tests

    phpunit --bootstrap lichess/tests/bootstrap/functional.php lichess/tests/AllTests.php

To generate migrations from your current schema

    php lichess/console doctrine:migrations:diff --bundle=Bundle\\LichessBundle

Then apply migrations

    php lichess/console doctrine:migrations:migrate --bundle=Bundle\\LichessBundle
