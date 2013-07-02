---
layout: default
title: LibMigration
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
At default, it's placed under the `/bin` directory in your project.

<pre class="sh">
$ php bin/phpmigrate init
# => Create skeleton configuration file in the current working directory.

$ php bin/phpmigrate create
# => Create new skeleton migration task file

$ php bin/phpmigrate status
# => List the migrations yet to be executed.

$ php bin/phpmigrate migrate
# => Execute the next migrations up.

$ php bin/phpmigrate up
# => Execute the next migration up.

$ php bin/phpmigrate down
# => Execute the next migration down.
</pre>


### Configurations

Run the below command to create a configuration file.

<pre class="shell">
$ php bin/phpmigrate init
</pre>

You will get `migration.php` file that is core configuration file. Open and edit it to your environment like the following.


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

### Create migration class file





