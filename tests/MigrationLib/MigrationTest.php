<?php
namespace Test\MigrationLib;

use MigrationLib\Migration;

class MigrationTest extends \PHPUnit_Framework_TestCase
{
  public function testStatus()
  {
    // Database Settings adjusts Travis CI environment.
    // http://about.travis-ci.org/docs/user/database-setup/
    $migration = new Migration(array(
      'databases' => array(
        'yourdatabase' => array(
          'database_dsn'      => 'mysql:dbname=migration_lib_test;host=127.0.0.1',
          'database_user'     => 'test_user',
          'database_password' => 'test_user',
          'schema_version_table' => 'schema_version'
        ),
      ),
    ));
    $migration->status();

    // Database Settings adjusts Travis CI environment.
    // http://about.travis-ci.org/docs/user/database-setup/
    $migration = new Migration(array(
        'databases' => array(
            'yourdatabase' => array(
                'database_pdo' => new \PDO('mysql:dbname=migration_lib_test;host=127.0.0.1', 'test_user', 'test_user'),
            ),
        ),
    ));
    $migration->status();

  }


}