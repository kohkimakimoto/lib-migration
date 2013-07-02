---
layout: default
title: LibMigration Documentation
---

## Documentation


### Requrement

PHP5.3 or later.


### Installation

Use composer installation. Make `composer.json` file like the following.

<pre class="javascript">
{
  "require": {
    "kohkimakimoto/lib-migration": "~1.0"
  }
}
</pre>

And run composer install command.

<pre class="sh">
$ curl -s http://getcomposer.org/installer | php
$ php composer.phar install
</pre>


### Basic Usage

LibMigration bundles simple command line interface `phpmigrate`.
You can use it to manage your database schema migrations.
At default, it's placed under the `vendor/bin` directory in your project.

<pre class="sh">
$ php vendor/bin/phpmigrate init
# => Create skeleton configuration file in the current working directory.

$ php vendor/bin/phpmigrate create
# => Create new skeleton migration task file

$ php vendor/bin/phpmigrate status
# => List the migrations yet to be executed.

$ php vendor/bin/phpmigrate migrate
# => Execute the next migrations up.

$ php vendor/bin/phpmigrate up
# => Execute the next migration up.

$ php vendor/bin/phpmigrate down
# => Execute the next migration down.
</pre>


### Configurations

Run the below command to create a configuration file.

<pre class="sh">
$ php vendor/bin/phpmigrate init
</pre>

You will get `migration.php` file that is core configuration file.
This configuration file has to return array.
Open and edit it to your environment like the following.

<pre class="php">
return array(
  'colors' => true,
  'databases' => array(
    'yourdatabase' => array(
      // PDO Connection settings.
      'database_dsn'      => 'mysql:dbname=yourdatabase;host=localhost',
      'database_user'     => 'user',
      'database_password' => 'password',

      // schema version table
      'schema_version_table' => 'schema_version',
      ,
      // directory contains migration task files.
      'migration_dir' => './databases/yourdatabase'
    ),
    'yourdatabase2' => array(
       // Second database setting...
    ),
  ),
);
</pre>

Under the array key `databases`, your database settings is written.
You can write multiple database settings.

You can also use another style settings. See below.

<pre class="php">
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
      'schema_version_table'    => 'schema_version',

      // directory contains migration task files.
      'migration_dir' => './databases/yourdatabase'
    ),
    'yourdatabase2' => array(
       // Second database setting...
    ),
  ),
);
</pre>

Difference between settings of `database_xxx` and `mysql_command_xxx` is database connection to execute migration SQL.
At default, LibMigration uses `database_xxx` settings to connect database using PDO.
If you set up that `mysql_command_enable` is **true**.
It uses `mysql_command_xxx` settings to connect database using mysql client command instead of PDO.

Which setting should you choose?
If you use a kind of `delimeter` command in your migration SQL. You need to use `mysql_command_xxx` settings.
Because `delimeter` command is not a SQL. Actually, it's a mysql client command. so that SQL dose not run through a PDO connection.


### Create migration class file

Run the following command.

<pre class="sh">
$ php vendor/bin/phpmigrate create create_sample_table
</pre>

You will get the following messages and the skeleton migration class file.
`20130702170043` timestamp part depeneds on your environment.

<pre class="sh">
[yourdatabase] Created databases/yourdatabase/20130702170043_yourdatabase_create_sample_table.php
</pre>

Open the `20130702170043_yourdatabase_create_sample_table.php`. And modify `getUpSQL` and `getDownSQL` method like below.



<pre class="php">
/**
 * Migration Task class.
 */
class YourdatabaseCreateSampleTable
{
    /**
     * Return the SQL statements for the Up migration
     *
     * @return string The SQL string to execute for the Up migration.
     */
    public function getUpSQL()
    {
        return &lt;&lt;&lt;END

    CREATE TABLE `sample` (
      `id` INT UNSIGNED NOT NULL,
      PRIMARY KEY (`id`) )
    ENGINE = InnoDB
    DEFAULT CHARACTER SET = utf8
    COLLATE = utf8_bin;7

    END;
    }

    /**
     * Return the SQL statements for the Down migration
     *
     * @return string The SQL string to execute for the Down migration.
     */
    public function getDownSQL()
    {
        return &lt;&lt;&lt;END

    DROP TABLE `sample`;

    END;
    }

</pre>

### Run migration

OK. You are ready to execute migrate command. Run the following command.

<pre class="sh">
$ php vendor/bin/phpmigrate migrate
</pre>

This commad will create your sample table.

