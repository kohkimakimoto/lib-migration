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
          'database_dsn'      => 'mysql:dbname=myapp_test;host=127.0.0.1',
          'database_user'     => 'root',
          'database_password' => '',
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
                'database_pdo' => new \PDO('mysql:dbname=myapp_test;host=127.0.0.1', 'root', ''),
            ),
        ),
    ));
    $migration->status();

  }


}