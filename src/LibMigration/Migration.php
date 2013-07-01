<?php
/**
 * LibMigration
 *
 * @package    LibMigration
 */
namespace LibMigration;

/**
 * Migration Class
 *
 * @author kohkimakimoto <kohki.makimoto@gmail.com>
 */
class Migration
{
  const VERSION = '1.1.0';
  const DEFAULT_CONFIG_FILE = 'migration.php';

  protected $config;
  protected $command;

  protected $logger;

  protected $conns = array();
  protected $cli_bases = array();


  public function __construct($config = array())
  {
    $this->config = new Config($config);
    $this->initialize();

    $this->logger = new Logger($this->config);
  }

  protected function initialize()
  {
    $config_file = $this->config->get('config_file');
    if ($config_file) {
      if (file_exists($config_file)) {
        $this->config->merge(array_merge(include $config_file, $this->config->getAll()));
      }
    }
  }

  /**
   * Run Helps Command
   */
  public function helpForCli()
  {
    $this->logger->write("LibMigration is a minimum database migration library and framework for MySQL. version ".self::VERSION);
    $this->logger->write("");
    $this->logger->write("Copyright (c) Kohki Makimoto <kohki.makimoto@gmail.com>");
    $this->logger->write("Apache License 2.0");
    $this->logger->write("");
    $this->logger->write("Usage");
    $this->logger->write("  phpmigrate [-h|-d|-c] COMMAND");
    $this->logger->write("");
    $this->logger->write("Options:");
    $this->logger->write("  -d         : Switch the debug mode to output log on the debug level.");
    $this->logger->write("  -h         : List available command line options (this page).");
    $this->logger->write("  -f=FILE    : Specify to load configuration file.");
    $this->logger->write("  -c         : List configurations.");
    $this->logger->write("");
    $this->logger->write("Commands:");
    $this->logger->write("  create NAME [DATABASENAME ...]    : Create new skeleton migration task file.");
    $this->logger->write("  status [DATABASENAME ...]         : List the migrations yet to be executed.");
    $this->logger->write("  migrate [DATABASENAME ...]        : Execute the next migrations up.");
    $this->logger->write("  up [DATABASENAME ...]             : Execute the next migration up.");
    $this->logger->write("  down [DATABASENAME ...]           : Execute the next migration down.");
    $this->logger->write("  init                              : Create skelton configuration file in the current working directory.");
    $this->logger->write("");
  }

  /**
   * List config
   */
  public function listConfig()
  {
    $largestLength = Utils::arrayKeyLargestLength($this->config->getAllOnFlatArray());
    $this->logger->write("");
    $this->logger->write("Configurations :");
    foreach ($this->config->getAllOnFlatArray() as $key => $val) {
      if ($largestLength === strlen($key)) {
        $sepalator = str_repeat(" ", 0);
      } else {
        $sepalator = str_repeat(" ", $largestLength - strlen($key));
      }

      $message = "  [".$key."] ".$sepalator;
      if (is_array($val)) {
        $message .= "=> array()";
      } else {
        $message .= "=> ".$val;
      }
      $this->logger->write($message);
    }
    $this->logger->write("");
  }

  /**
   * Run Create Command
   */
  public function create($taskName, $databases)
  {
    if (!$databases) {
      // At default, processing all defined databases.
      $databases = $this->getDatabaseNames();
    }

    // Validate database names.
    $this->validateDatabaseNames($databases);

    $timestamp = new \DateTime();
    foreach ($databases as $database) {
      $this->createMigrationTask($database."_".$taskName, $timestamp, $database);
    }
  }

  /**
   * Run Status Command
   * @param array $databases
   */
  public function status($databases = array())
  {
    $this->checkAllMigrationFileList();

    if (!$databases) {
      // At default, processing all defined databases.
      $databases = $this->getDatabaseNames();
    }

    // Validate database names.
    $this->validateDatabaseNames($databases);

    foreach ($databases as $database) {
      $version = $this->getSchemaVersion($database);
      if ($version !== null) {
        $this->logger->write("Current schema version is ".$version, "[$database]");
      }

      $files = $this->getValidMigrationUpFileList($database, $version);
      if (count($files) === 0) {
        $this->logger->write("Already up to date.", "[$database]");
        continue;
      }

      $this->logger->write("Your migrations yet to be executed are below.", "[$database]");
      $this->logger->write("");
      foreach ($files as $file) {
        $this->logger->write(basename($file));
      }
      $this->logger->write("");
    }
  }

  /**
   * Run Migrate Command
   * @param unknown $databases
   */
  public function migrate($databases = array())
  {
    $this->checkAllMigrationFileList();

    if (!$databases) {
      // At default, processing all defined databases.
      $databases = $this->getDatabaseNames();
    }

    // Validate database names.
    $this->validateDatabaseNames($databases);

    foreach ($databases as $database) {
      $version = $this->getSchemaVersion($database);

      if ($version !== null) {
        $this->logger->write("Current schema version is ".$version, "[$database]");
      }

      $files = $this->getValidMigrationUpFileList($database, $version);
      if (count($files) === 0) {
        $this->logger->write("Already up to date.", "[$database]");
        continue;
      }

      foreach ($files as $file) {
        $this->migrateUp($file, $database);
      }
    }
  }

  /**
   * Run Up Command
   * @param unknown $databases
   */
  public function up($databases = array())
  {
    $this->checkAllMigrationFileList();

    if (!$databases) {
      // At default, processing all defined databases.
      $databases = $this->getDatabaseNames();
    }

    // Validate database names.
    $this->validateDatabaseNames($databases);

    foreach ($databases as $database) {
      $version = $this->getSchemaVersion($database);

      if ($version !== null) {
        $this->logger->write("Current schema version is ".$version, "[$database]");
      }

      $files = $this->getValidMigrationUpFileList($database, $version);
      if (count($files) === 0) {
        $this->logger->write("Already up to date.", "[$database]");
        continue;
      }

      $this->migrateUp($files[0], $database);
    }
  }

  /**
   * Run Down Command
   * @param unknown $databases
   */
  public function down($databases = array())
  {
    $this->checkAllMigrationFileList();

    if (!$databases) {
      // At default, processing all defined databases.
      $databases = $this->getDatabaseNames();
    }

    // Validate database names.
    $this->validateDatabaseNames($databases);

    foreach ($databases as $database) {
      $version = $this->getSchemaVersion($database);

      if ($version !== null) {
        $this->logger->write("Current schema version is ".$version, "[$database]");
      }

      $files = $this->getValidMigrationDownFileList($database, $version);
      if (count($files) === 0) {
        $this->logger->write("Not found older migration files than current schema version.", "[$database]");
        continue;
      }

      $prev_version = null;
      if (isset($files[1])) {
        preg_match("/(\d+)_(.*)\.php$/", basename($files[1]), $matches);
        $prev_version    = $matches[1];
      }

      $this->migrateDown($files[0], $prev_version, $database);
    }

  }

  /**
   * Init task creates skelton configuration file.
   * @throws Exception
   */
  public function init()
  {
    $cwd = getcwd();
    $configpath = $cwd.'/'.self::DEFAULT_CONFIG_FILE;
    if (file_exists($configpath)) {
      throw new Exception("Exists $configpath");
    }

    $cotent = <<<END
<?php
return array(
  'colors' => true,
  'databases' => array(
    'yourdatabase' => array(
      // PDO Connection settings.
      'database_dsn'      => 'mysql:dbname=yourdatabase;host=localhost',
      'database_user'     => 'user',
      'database_password' => 'password',

      // or
      // mysql client command settings.
      // 'mysql_command_enable'    => true,
      // 'mysql_command_cli'       => "/usr/bin/mysql",
      // 'mysql_command_tmpsqldir' => "/tmp",
      // 'mysql_command_host'      => "localhost",
      // 'mysql_command_user'      => "user",
      // 'mysql_command_password'  => "password",
      // 'mysql_command_database'  => "yourdatabase",
      // 'mysql_command_options'   => "--default-character-set=utf8",

      // schema version table
      'schema_version_table'    => 'schema_version',

      // directory contains migration task files.
      'migration_dir'           => '.'
    ),
  ),
);

END;

    file_put_contents($configpath, $cotent);
    $this->logger->write("Create configuration file to $configpath");
  }

  public function getConfig()
  {
    return $this->config;
  }

  protected function createMigrationTask($taskName, $timestamp, $database)
  {
    $migration_dir = $this->config->get('databases/'.$database.'/migration_dir');
    if (!$migration_dir) {
      $migration_dir = __DIR__;
    }

    $filename = $timestamp->format('YmdHis')."_".$taskName.".php";
    $filepath = $migration_dir."/".$filename;
    $camelize_name = Utils::camelize($taskName);

    $content = <<<EOF
<?php
/**
 * Migration Task class.
 */
class $camelize_name
{
  public function preUp()
  {
      // add the pre-migration code here
  }

  public function postUp()
  {
      // add the post-migration code here
  }

  public function preDown()
  {
      // add the pre-migration code here
  }

  public function postDown()
  {
      // add the post-migration code here
  }

  /**
   * Return the SQL statements for the Up migration
   *
   * @return string The SQL string to execute for the Up migration.
   */
  public function getUpSQL()
  {
     return "";
  }

  /**
   * Return the SQL statements for the Down migration
   *
   * @return string The SQL string to execute for the Down migration.
   */
  public function getDownSQL()
  {
     return "";
  }

}
EOF;
    if (!is_dir(dirname($filepath))) {
      mkdir(dirname($filepath), 0777, true);
    }

    file_put_contents($filepath, $content);

    $this->logger->write("Created ".$filepath, "[$database]");
  }

  protected function migrateUp($file, $database)
  {
    $this->logger->write("Proccesing migrate up by ".basename($file)."", "[$database]");

    require_once $file;

    preg_match("/(\d+)_(.*)\.php$/", basename($file), $matches);
    $version    = $matches[1];
    $class_name = Utils::camelize($matches[2]);

    $migrationInstance = new $class_name();

    if (method_exists($migrationInstance, 'preUp')) {
      $migrationInstance->preUp();
    }

    $sql = $migrationInstance->getUpSQL();
    if (!empty($sql)) {
      if ($this->isCliExecution($database)) {
        // cli
        $this->execUsingCli($sql, $database);

      } else {
        // pdo
        $conn = $this->getConnection($database);
        $conn->exec($sql);
      }
    }

    if (method_exists($migrationInstance, 'postUp')) {
      $migrationInstance->postUp();
    }

    $this->updateSchemaVersion($version, $database);
  }

  protected function migrateDown($file, $prev_version, $database)
  {
    if ($prev_version === null) {
      $prev_version = 0;
    }

    $this->logger->write("Proccesing migrate down to version $prev_version by ".basename($file)."", "[$database]");

    require_once $file;

    preg_match("/(\d+)_(.*)\.php$/", basename($file), $matches);
    $version    = $matches[1];
    $class_name = Utils::camelize($matches[2]);

    $migrationInstance = new $class_name();

    if (method_exists($migrationInstance, 'preDown')) {
      $migrationInstance->preDown();
    }

    $sql = $migrationInstance->getDownSQL();
    if (!empty($sql)) {
      if ($this->isCliExecution($database)) {
        // cli
        $this->execUsingCli($sql, $database);

      } else {
        // pdo
        $conn = $this->getConnection($database);
        $conn->exec($sql);
      }
    }

    if (method_exists($migrationInstance, 'postDown')) {
      $migrationInstance->postDown();
    }

    $this->updateSchemaVersion($prev_version, $database);
  }

  protected function updateSchemaVersion($version, $database)
  {
    if (empty($version)) {
      $version = 0;
    }

    if ($this->isCliExecution($database)) {
      // cli
      $table = $this->config->get('databases/'.$database.'/schema_version_table', 'schema_version');
      $pk = $this->config->get('databases/'.$database.'/schema_version_table_pk_column', null);
      $pkvalue = $this->config->get('databases/'.$database.'/schema_version_table_pk_value', null);

      $sql = "show tables like '".$table."'";

      $arr = $this->execUsingCli($sql, $database);

      // Create table if it dosen't exist.
      if (count($arr) == 0) {
        $sql = $this->getSchemaVersionTebleCreateSQL($table, $pk);
        $this->execUsingCli($sql, $database);
      }

      // Insert initial record if it dosen't exist.
      $sql = $this->getSchemaVersionTebleSelectSQL($table, $pk ,$pkvalue);
      $arr = $this->execUsingCli($sql, $database);
      if (count($arr) == 0) {
        $sql = $this->getSchemaVersionTebleInsertSQL($version, $table, $pk ,$pkvalue);
        $this->execUsingCli($sql, $database);
      }

      // Update version.
      $sql = $this->getSchemaVersionTebleUpdateSQL($version, $table, $pk ,$pkvalue);
      $this->execUsingCli($sql, $database);

    } else {
      // pdo
      $conn = $this->getConnection($database);

      $table = $this->config->get('databases/'.$database.'/schema_version_table', 'schema_version');
      $pk = $this->config->get('databases/'.$database.'/schema_version_table_pk_column', null);
      $pkvalue = $this->config->get('databases/'.$database.'/schema_version_table_pk_value', null);

      $sql = "show tables like '".$table."'";
      $stmt = $conn->prepare($sql);
      $stmt->execute();

      $arr = $stmt->fetchAll();

      // Create table if it dosen't exist.
      if (count($arr) == 0) {
        $sql = $this->getSchemaVersionTebleCreateSQL($table, $pk);
        $stmt = $conn->prepare($sql);
        $stmt ->execute();
      }

      // Insert initial record if it dosen't exist.
      $sql = $this->getSchemaVersionTebleSelectSQL($table, $pk ,$pkvalue);
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      $arr = $stmt->fetchAll();
      if (count($arr) == 0) {
        $sql = $this->getSchemaVersionTebleInsertSQL($version, $table, $pk ,$pkvalue);
        $stmt = $conn->prepare($sql);
        $stmt->execute();
      }

      // Update version.
      $sql = $this->getSchemaVersionTebleUpdateSQL($version, $table, $pk ,$pkvalue);
      $stmt = $conn->prepare($sql);
      $stmt->execute();
    }
  }

  /**
   * Validate defined database names.
   * @param unknown $databases
   * @throws Exception
   */
  protected function validateDatabaseNames($databases)
  {
    $definedDatabaseNames = $this->getDatabaseNames();
    foreach ($databases as $dbname) {
      if (array_search($dbname, $definedDatabaseNames) === false) {
        throw new Exception("Database '".$dbname."' is not defined.");
      }
    }
  }

  /**
   * Get defined database names
   * @throws Exception
   */
  protected function getDatabaseNames()
  {
    $database = $this->config->get('databases');
    if (!$database) {
      throw new Exception("Database settings are not found.");
    }

    return array_keys($database);
  }

  protected function getSchemaVersion($database)
  {
    $this->logger->write("Getting schema version from '$database'", null, "debug");
    if ($this->isCliExecution($database)) {
      // cli
      $table = $this->config->get('databases/'.$database.'/schema_version_table', 'schema_version');
      $pk = $this->config->get('databases/'.$database.'/schema_version_table_pk_column', null);
      $pkvalue = $this->config->get('databases/'.$database.'/schema_version_table_pk_value', null);

      $sql = "show tables like '".$table."'";

      $arr = $this->execUsingCli($sql, $database);

      // Check to exist table.
      if (count($arr) == 0) {
        $this->logger->write("Table [".$table."] is not found. This schema hasn't been managed yet by PHPMigrate.", null, "debug");
        return null;
      }

      $sql = $this->getSchemaVersionTebleSelectSQL($table, $pk ,$pkvalue);
      $arr = $this->execUsingCli($sql, $database);
      if (count($arr) > 0) {
        return $arr[0];
      } else {
        return null;
      }

    } else {
      // pdo

      $conn = $this->getConnection($database);

      $table = $this->config->get('databases/'.$database.'/schema_version_table', 'schema_version');
      $pk = $this->config->get('databases/'.$database.'/schema_version_table_pk_column', null);
      $pkvalue = $this->config->get('databases/'.$database.'/schema_version_table_pk_value', null);

      $sql = "show tables like '".$table."'";
      $stmt = $conn->prepare($sql);
      $stmt->execute();

      $arr = $stmt->fetchAll();

      // Check to exist table.
      if (count($arr) == 0) {
        $this->logger->write("Table [".$table."] is not found. This schema hasn't been managed yet by PHPMigrate.", null, "debug");
        return null;
      }

      $sql = $this->getSchemaVersionTebleSelectSQL($table, $pk ,$pkvalue);
      $stmt = $conn->prepare($sql);
      $stmt->execute();

      $arr = $stmt->fetchAll();
      if (count($arr) > 0) {
        return $arr[0]['version'];
      } else {
        return null;
      }
    }
  }


  /**
   * Get PDO connection
   * @return PDO
   */
  protected function getConnection($database)
  {
    if (!@$this->conns[$database]) {

      if ($this->config->get('databases/'.$database.'/database_pdo')) {
        $this->conns[$database] = $this->config->get('databases/'.$database.'/database_pdo');
        $this->conns[$database]->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
      } else {
        $dsn      = $this->config->get('databases/'.$database.'/database_dsn');
        $user     = $this->config->get('databases/'.$database.'/database_user');
        $password = $this->config->get('databases/'.$database.'/database_password');

        $this->conns[$database] = new \PDO($dsn, $user, $password);
        $this->conns[$database]->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
      }
    }

    return $this->conns[$database];
  }


  /**
   * Get mysql command base string.
   * @return Ambigous <string, unknown>
   */
  protected function getCliBase($database)
  {
    if (!@$this->cli_bases[$database]) {
      $this->cli_bases[$database] =
      $this->config->get('databases/'.$database.'/mysql_command_cli', 'mysql')
      ." -u".$this->config->get('databases/'.$database.'/mysql_command_user')
      ." -p".$this->config->get('databases/'.$database.'/mysql_command_password')
      ." -h".$this->config->get('databases/'.$database.'/mysql_command_host')
      ." --batch -N"
          ." ".$this->config->get('databases/'.$database.'/mysql_command_options')
          ." ".$this->config->get('databases/'.$database.'/mysql_command_database')
          ;
    }

    return $this->cli_bases[$database];
  }

  /**
   * Return ture, if it use mysql command to execute migration.
   */
  protected function isCliExecution($database)
  {
    $ret = $this->config->get('databases/'.$database.'/mysql_command_enable', false);
    if ($ret) {
      if (!$this->config->get('databases/'.$database.'/mysql_command_user')) {
        throw new Exception("You are using mysql_command. so config [mysql_command_user] is required.");
      }
      if (!$this->config->get('databases/'.$database.'/mysql_command_host')) {
        throw new Exception("You are using mysql_command. so config [mysql_command_host] is required.");
      }
      if (!$this->config->get('databases/'.$database.'/mysql_command_password')) {
        throw new Exception("You are using mysql_command. so config [mysql_command_password] is required.");
      }
      if (!$this->config->get('databases/'.$database.'/mysql_command_database')) {
        throw new Exception("You are using mysql_command. so config [mysql_command_database] is required.");
      }
    }

    return $ret;
  }

  protected function getTmpSqlFilePath($sql, $database)
  {
    $dir = $this->config->get('databases/'.$database.'/mysql_command_tmpdir', '/tmp');
    $prefix = $database.'_'.md5($sql);
    $uniqid = uniqid();

    $sqlfile = "libmigration.".$prefix.".".$uniqid.".sql";
    $path = $dir."/".$sqlfile;

    return $path;
  }

  protected function execUsingCli($sql, $database)
  {
    $path = $this->getTmpSqlFilePath($sql, $database);

    $this->logger->write("Executing sql is the following \n".$sql, null, "debug");
    $this->logger->write("Creating temporary sql file to [".$path."]", null, "debug");
    file_put_contents($path, $sql);

    $clibase = $this->getCliBase($database);

    $cmd = $clibase." < ".$path."  2>&1";
    $this->logger->write("Executing command is [".$cmd."]", null, "debug");

    //$output = shell_exec($cmd);
    exec($cmd, $output, $return_var);

    unlink($path);

    if ($return_var !== 0) {
      // SQL Error
      $err = '';
      foreach ($output as $str) {
        $err .= $str."\n";
      }
      throw new Exception($err);
    }

    return $output;
  }



  protected function getValidMigrationUpFileList($database, $version)
  {
    $valid_files = array();

    $files = $this->getMigrationFileList($database);
    foreach ($files as $file) {
      preg_match ("/^\d+/", basename($file), $matches);
      $timestamp = $matches[0];

      if ($timestamp > $version) {
        $valid_files[] = $file;
      }
    }

    return $valid_files;
  }

  protected function getValidMigrationDownFileList($database, $version)
  {
    $valid_files = array();

    $files = $this->getMigrationFileList($database);
    rsort($files);
    foreach ($files as $file) {
      preg_match ("/^\d+/", basename($file), $matches);
      $timestamp = $matches[0];

      if ($timestamp <= $version) {
        $valid_files[] = $file;
      }
    }

    return $valid_files;
  }

  protected function getMigrationFileList($database)
  {
    $migration_dir = $this->config->get('databases/'.$database.'/migration_dir');

    $files = array();
    $classes = array();

    $gfiles = array();
    if ($migration_dir) {
      $gfiles = glob($migration_dir.'/*');
    } else {
      $gfiles = glob('*');
    }

    foreach ($gfiles as $file) {
      if (preg_match("/^\d+_.+\.php$/", basename($file))) {

        preg_match("/(\d+)_(.*)\.php$/", basename($file), $matches);
        $version    = $matches[1];
        $class_name = Utils::camelize($matches[2]);

        // Check to exist same class name.
        if (array_key_exists($class_name, $classes)) {
          // Can't use same class name to migration tasks.
          throw new Exception("Can't use same class name to migration tasks. Duplicate migration task name [".$classes[$class_name]."] and [".$file."].");
        }

        $classes[$class_name] = $file;
        $files[] = $file;
      }
    }

    sort($files);
    return $files;
  }

  /**
   * Check migration file validation.
   */
  protected function checkAllMigrationFileList()
  {
    $databases = $this->getDatabaseNames();

    $files = array();
    $classes = array();

    foreach ($databases as $database) {
      $migration_dir = $this->config->get('databases/'.$database.'/migration_dir');

      $gfiles = array();
      if ($migration_dir) {
        $gfiles = glob($migration_dir.'/*');
      } else {
        $gfiles = glob('*');
      }

      foreach ($gfiles as $file) {
        if (preg_match("/^\d+_.+\.php$/", basename($file))) {

          preg_match("/(\d+)_(.*)\.php$/", basename($file), $matches);
          $version    = $matches[1];
          $class_name = Utils::camelize($matches[2]);

          $class_text = file_get_contents($file);

          if (!preg_match("/class +$class_name/", $class_text)) {
            throw new Exception("Unmatch defined class in the $file. You must define '$class_name' class in that file.");
          }

          // Check to exist same class name.
          if (array_key_exists($class_name, $classes)
            && $classes[$class_name] != $file) {
            // Can't use same class name to migration tasks.
            throw new Exception("Can't use same class name to migration tasks. Duplicate migration task name [".$classes[$class_name]."] and [".$file."].");
          }

          $classes[$class_name] = $file;
          $files[] = $file;
        }
      }
    }
  }

  protected function getSchemaVersionTebleCreateSQL($table, $pk = null)
  {
    $sql= null;
    if ($pk) {
      $sql =<<<EOF

CREATE TABLE `$table` (
  `$pk` VARCHAR(255) NOT NULL,
  `version` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`$pk`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin;

EOF;

    } else {
      $sql =<<<EOF

CREATE TABLE `$table` (
  `version` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`version`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin;

EOF;

    }

    return $sql;
  }

  protected function getSchemaVersionTebleSelectSQL($table, $pk = null, $pkvalue = null)
  {
    $sql = null;
    if ($pk && $pkvalue) {
      $sql = "select version from ".$table." where $pk = '".$pkvalue."'";
    } else {
      $sql = "select version from ".$table."";
    }

    return $sql;
  }

  protected function getSchemaVersionTebleInsertSQL($version, $table, $pk = null, $pkvalue = null)
  {
    $sql = null;
    if ($pk && $pkvalue) {
      $sql = "insert into ".$table."($pk, version) values ('$pkvalue', '$version')";
    } else {
      $sql = "insert into ".$table."(version) values ('$version')";
    }

    return $sql;
  }

  protected function getSchemaVersionTebleUpdateSQL($version, $table, $pk = null, $pkvalue = null)
  {
    $sql = null;
    if ($pk && $pkvalue) {
      $sql = "update ".$table." set version = '$version' where $pk = '$pkvalue'";
    } else {
      $sql = "update ".$table." set version = '$version'";
    }

    return $sql;
  }
}









