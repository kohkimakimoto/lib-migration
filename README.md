# MigrationLib

[![Build Status](https://travis-ci.org/kohkimakimoto/migration-lib.png?branch=master)](https://travis-ci.org/kohkimakimoto/migration-lib)

MigrationLib is a minimum database migration library and command line tool for MySQL.

It's PHP program restructured from [PHPMigrate](https://github.com/kohkimakimoto/phpmigrate) to use easily in other PHP products.

## Features

  * Migrations use plain SQL to change schema.
  * You can run some PHP codes post and previous executing SQL.

## Requrement

  * PHP5.3 or later.

## Installation

Use composer installation. Make `composer.json` like the following.

    {
      "require": {
        "kohkimakimoto/migration-lib": "dev-master"
      }
    }

And run Composer install command.

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar install

## License

  Apache License 2.0




