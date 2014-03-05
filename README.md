# LibMigration

[![Build Status](https://travis-ci.org/kohkimakimoto/lib-migration.png?branch=master)](https://travis-ci.org/kohkimakimoto/lib-migration)

LibMigration is a minimum database migration library and framework for MySQL.

## Features

* LibMigration uses plain SQL to change your database schema. Does’t use any specific syntax.
* LibMigration can manage muliple databases.
* LibMigration core api is very simple. So you can easily use it in your other code.

## Requirement

PHP5.3 or later.

## Installation

Use composer installation. Make `composer.json` like the following.

``` json
{
  "require": {
    "kohkimakimoto/lib-migration": "1.*"
  }
}
```

And run Composer install command.

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar install

## Usage and Documentation

See the [LibMigraion Github page](http://kohkimakimoto.github.io/lib-migration/)

## License

Apache License 2.0




