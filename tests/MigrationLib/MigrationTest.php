<?php
namespace Test\MigrationLib;

use MigrationLib\Migration;

class MigrationTest extends \PHPUnit_Framework_TestCase
{
  public function testStatus()
  {
    $migration = new Migration(array(
      'databases' => array(
        'yourdatabase' => array(
          'database_dsn'      => 'mysql:dbname=yourdatabase;host=localhost',
          'database_user'     => 'user',
          'database_password' => 'password',
          'schema_version_table' => 'schema_version'
        ),
      ),
    ));

    $migration->status();
  }


}