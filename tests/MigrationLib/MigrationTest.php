<?php
namespace Test\MigrationLib;

use MigrationLib\Migration;

class MigrationTest extends \PHPUnit_Extensions_Database_TestCase
{
  /**
   * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
   */
  public function getConnection()
  {
    $pdo = new \PDO('mysql:dbname=migration_lib_test;host=127.0.0.1', 'test_user', 'test_user');
    return  $this->createDefaultDBConnection($pdo, "test01");
  }

  /**
   * @return PHPUnit_Extensions_Database_DataSet_IDataSet
   */
  public function getDataSet()
  {
    return new \PHPUnit_Extensions_Database_DataSet_DefaultDataSet(array());
  }

  public function testStatus1()
  {
    $expect = <<<EOF
[migration_lib_test] Already up to date.

EOF;

    $this->expectOutputString($expect);

    // Database Settings adjusts Travis CI environment.
    // http://about.travis-ci.org/docs/user/database-setup/
    $migration = new Migration(array(
      'databases' => array(
        'migration_lib_test' => array(
          'database_dsn'      => 'mysql:dbname=migration_lib_test;host=127.0.0.1',
          'database_user'     => 'test_user',
          'database_password' => 'test_user',
          'schema_version_table' => 'schema_version'
        ),
      ),
    ));
    $migration->status();
  }

  public function testStatus2()
  {
    $expect = <<<EOF
[migration_lib_test] Already up to date.

EOF;

    $this->expectOutputString($expect);

    $migration = new Migration(array(
      'databases' => array(
        'migration_lib_test' => array(
          // mysql client command settings.
          'mysql_command_enable'    => true,
          'mysql_command_cli'       => "/usr/bin/mysql",
          'mysql_command_tmpsqldir' => "/tmp",
          'mysql_command_host'      => "127.0.0.1",
          'mysql_command_user'      => "test_user",
          'mysql_command_password'  => "test_user",
          'mysql_command_database'  => "migration_lib_test",
          'mysql_command_options'   => "--default-character-set=utf8",

          // schema version table
          'schema_version_table' => 'schema_version'
        ),
      ),
    ));
    $migration->status();
  }

  /*
  public function testStatus3()
  {
    $expect = <<<EOF
[migration_lib_test] Your migrations yet to be executed are below.

20130613185549_test01.php


EOF;

    $this->expectOutputString($expect);

    $migration = new Migration(array(
      'databases' => array(
        'migration_lib_test' => array(
          'database_pdo' => new \PDO('mysql:dbname=migration_lib_test;host=127.0.0.1', 'test_user', 'test_user'),
        ),
      ),
      'migration_dir' => __DIR__."/../data/migration01",
    ));
    $migration->status();

  }
  */

  public function testUp()
  {
    $migration = new Migration(array(
      'databases' => array(
        'migration_lib_test' => array(
          'database_pdo' => new \PDO('mysql:dbname=migration_lib_test;host=127.0.0.1', 'test_user', 'test_user'),
          'schema_version_table_pk_column' => 'id',
          'schema_version_table_pk_value'  => 'test',
        ),
      ),
      'migration_dir' => __DIR__."/../data/migration01",
    ));

    $migration->up();
  }


}