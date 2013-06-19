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

## Usage

### phpmigrate

MigrationLib bundles simple command line interface `phpmigrate`.
You can use it to manage your database schema migrations.

#### Configurations

Run the below command to create first configuration file.

    $ php bin/phpmigrate init

You will get `migration.php` file. Open and edit it to your environment like the following.

    <?php
    return array(
      'databases' => array(
        'yourdatabase' => array(
          // PDO Connection settings.
          'database_dsn'      => 'mysql:dbname=yourdatabase;host=localhost',
          'database_user'     => 'user',
          'database_password' => 'password',

          // schema version table
          'schema_version_table' => 'schema_version'
        ),
        'yourdatabase2' => array(
           // Second database setting...
        ),
      ),
    );

or

    <?php
    return array(
      'databases' => array(
        'yourdatabase' => array(
          // mysql client command settings.
          'mysql_command_enable'    => true,
          'mysql_command_cli'       => "/usr/bin/mysql",
          'mysql_command_tmpsqldir' => "/tmp",
          'mysql_command_host'      => "localhost",
          'mysql_command_user'      => "user",
          'mysql_command_password'  => "password",
          'mysql_command_database'  => "yourdatabase",
          'mysql_command_options'   => "--default-character-set=utf8",

          // schema version table
          'schema_version_table' => 'schema_version'
        ),
        'yourdatabase2' => array(
           // Second database setting...
        ),
      ),
    );

Under the array key `databases`, your database settings is written.
You can write multiple database settings to manage same schema at multi databases.

Difference between settings of `database_xxx` and `mysql_command_xxx` is database connection to execute migration SQL.
At default, it uses `database_xxx` settings to connect database using PDO.
You set up that `mysql_command_enable` is **true**. It uses `mysql_command_xxx` settings to connect databse using mysql client command.
If you use `delimeter` command in your SQL. You need to use `mysql_command_xxx` settings. Because `delimeter` command is not a SQL.
It's a mysql client command.


#### Create migration class file

Run the following command

    php bin/phpmigrate create create_sample_table

You will get the following messages and the skeleton migration file.
`20130617213426` timestamp part depeneds on your environment.

    Created 20130617213426_create_sample_table.php

Open the `20130617213426_create_sample_table.php`. And modify `getUpSQL` and `getDownSQL` method like below.



      /**
       * Return the SQL statements for the Up migration
       *
       * @return string The SQL string to execute for the Up migration.
       */
      public function getUpSQL()
      {
         return <<<END

    CREATE TABLE `sample` (
      `id` INT UNSIGNED NOT NULL,
      PRIMARY KEY (`id`) )
    ENGINE = InnoDB
    DEFAULT CHARACTER SET = utf8
    COLLATE = utf8_bin;

    END;
      }

    /**
     * Return the SQL statements for the Down migration
     *
     * @return string The SQL string to execute for the Down migration.
     */
    public function getDownSQL()
    {
        return <<<END

       DROP TABLE `sample`;

    END;
    }

OK. You are ready to execute migrate command. Run the following command.

    php bin/phpmigrate migrate

This commad will creat your sample table.

#### Command line Options

  * **-d**

    Switch the debug mode to output log on the debug level.

  * **-h**

    List available command line options.


  * **-c**

    List configurations.

#### Commands

  * **create**

    Create new skeleton migration file.

        php bin/phpmigrate create foo

  * **status [DATABASENAME ...]**

    List the migrations yet to be executed.

        php bin/phpmigrate status

  * **migrate [DATABASENAME ...]**

    Execute the next migrations up.

        php bin/phpmigrate migrate

  * **up [DATABASENAME ...]**

    Execute the next migration up.

        php bin/phpmigrate up

  * **down [DATABASENAME ...]**

    Execute the next migration down.

        php bin/phpmigrate down

### Using as library

You easily use MigrationLib in your products. The followin Migration code.

    $migration = new \MigrationLib\Migration(array(
      'databases' => array(
        'yourdatabase' => array(
          'database_pdo'         => $connection,  // PDO Connecition instance.
          'schema_version_table' => 'schema_version',
        )),
      'migration_dir' => "path/to/migration/directory"
    ));

    $migration->migrate();
    // or other tasks.
    // $migration->status();
    // $migration->up();
    // $migration->down();






## License

  Apache License 2.0




