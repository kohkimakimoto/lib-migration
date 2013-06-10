<?php
/**
 * MigrationLib
 *
 * @package    MigrationLib
 */
namespace MigrationLib;

/**
 * Migration Class
 *
 * @author kohkimakimoto <kohki.makimoto@gmail.com>
 */
class Migration
{
  const VERSION = '1.0.0';

  protected $config;
  protected $arguments;
  protected $command;

  protected $logger;

  protected $conns = array();
  protected $cli_bases = array();

  public function __construct($config = array())
  {
    $this->config = new Config($config);
    $this->logger = new Logger($this->config);
  }

  /**
   * Execute.
   * @param unknown $task
   * @param unknown $options
   */
  public function execute($command, $arguments)
  {
    try {

      $this->command   = $command;
      $this->arguments = $arguments;

      if ($this->command == 'help') {

        $this->help();

      } elseif ($this->command == 'status') {

        $this->runStatus();

      } elseif ($this->command == 'create') {

        $this->runCreate();

      } elseif ($this->command == 'migrate') {

        $this->runMigrate();

      } elseif ($this->command == 'up') {

        $this->runUp();

      } elseif ($this->command == 'down') {

        $this->runDown();

      } else {
        fputs(STDERR, 'Unknown command: '.$this->command."\n");
        exit(1);
      }

    } catch (Exception $e) {

      if (Config::get('debug')) {
        fputs(STDERR, $e);
      } else {
        fputs(STDERR, $e->getMessage()."\n");
      }

      exit(1);
    }
  }

  /**
   * Run Helps Command
   */
  public function help()
  {
    $this->logger->write("MigrationLib is a minimum migration tool library. version ".self::VERSION);
  }

  /**
   * Run Status Command
   */
  public function status()
  {
    $databases = $this->getValidDatabases($this->arguments);
    foreach ($databases as $database) {
      $version = $this->getSchemaVersion($database);
      if ($version !== null) {
        MigrationLogger::log("[".$database."] Current schema version is ".$version);
      }

      $files = $this->getValidMigrationUpFileList($version);
      if (count($files) === 0) {
        MigrationLogger::log("[".$database."] Already up to date.");
        continue;
      }

      MigrationLogger::log("[".$database."] Your migrations yet to be executed are below.");
      MigrationLogger::log("");
      foreach ($files as $file) {
        MigrationLogger::log(basename($file));
      }
      MigrationLogger::log("");
    }

  }

  /**
   * Run Create Command
   */
  protected function runCreate()
  {
    if (count($this->arguments) > 0) {
      $name = $this->arguments[0];
    } else {
      throw new Exception("You need to pass the argument for migration name. (ex php ".basename(__FILE__)." create foo");
    }

    $timestamp = new DateTime();
    $filename = $timestamp->format('YmdHis')."_".$name.".php";
    $filepath = __DIR__."/".$filename;
    $camelize_name = MigrationUtils::camelize($name);

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

    file_put_contents($filename, $content);

    MigrationLogger::log("Created ".$filename);
  }

  /**
   * Run Migrate Command
   */
  protected function runMigrate()
  {
    $databases = $this->getValidDatabases($this->arguments);
    foreach ($databases as $database) {
      $version = $this->getSchemaVersion($database);

      if ($version !== null) {
        MigrationLogger::log("[".$database."] Current schema version is ".$version);
      }

      $files = $this->getValidMigrationUpFileList($version);
      if (count($files) === 0) {
        MigrationLogger::log("[".$database."] Already up to date.");
        continue;
      }

      foreach ($files as $file) {
        $this->migrateUp($file, $database);
      }
    }
  }

  /**
   * Run Up Command
   */
  protected function runUp()
  {
    $databases = $this->getValidDatabases($this->arguments);
    foreach ($databases as $database) {
      $version = $this->getSchemaVersion($database);

      if ($version !== null) {
        MigrationLogger::log("[".$database."] Current schema version is ".$version);
      }

      $files = $this->getValidMigrationUpFileList($version);
      if (count($files) === 0) {
        MigrationLogger::log("[".$database."] Already up to date.");
        continue;
      }

      $this->migrateUp($files[0], $database);
    }
  }

  /**
   * Run Down Command
   */
  protected function runDown()
  {
    $databases = $this->getValidDatabases($this->arguments);
    foreach ($databases as $database) {
      $version = $this->getSchemaVersion($database);

      if ($version !== null) {
        MigrationLogger::log("[".$database."] Current schema version is ".$version);
      }

      $files = $this->getValidMigrationDownFileList($version);
      if (count($files) === 0) {
        MigrationLogger::log("[".$database."] Not found older migration files than current schema version.");
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


  protected function migrateUp($file, $database)
  {
    MigrationLogger::log("[".$database."] Proccesing migrate up by ".basename($file)."");

    require_once $file;

    preg_match("/(\d+)_(.*)\.php$/", basename($file), $matches);
    $version    = $matches[1];
    $class_name = MigrationUtils::camelize($matches[2]);

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

    MigrationLogger::log("[".$database."] Proccesing migrate down to version $prev_version by ".basename($file)."");

    require_once $file;

    preg_match("/(\d+)_(.*)\.php$/", basename($file), $matches);
    $version    = $matches[1];
    $class_name = MigrationUtils::camelize($matches[2]);

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
      $table = Config::get('databases/'.$database.'/schema_version_table', 'schema_version');
      $sql = "show tables like '".$table."'";

      $arr = $this->execUsingCli($sql, $database);

      // Create table if it dosen't exist.
      if (count($arr) == 0) {
        $sql =<<<EOF

CREATE TABLE `$table` (
  `version` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`version`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin;

EOF;
        $this->execUsingCli($sql, $database);
      }

      // Insert initial record if it dosen't exist.
      $sql = "select * from ".$table;
      $arr = $this->execUsingCli($sql, $database);
      if (count($arr) == 0) {
        $sql = "insert into ".$table."(version) values ('$version')";
        $this->execUsingCli($sql, $database);
      }

      // Update version.
      $sql = "update ".$table." set version = '$version'";
      $this->execUsingCli($sql, $database);

    } else {
      // pdo
      $conn = $this->getConnection($database);

      $table = Config::get('databases/'.$database.'/schema_version_table', 'schema_version');
      $sql = "show tables like '".$table."'";
      $stmt = $conn->prepare($sql);
      $stmt->execute();

      $arr = $stmt->fetchAll();

      // Create table if it dosen't exist.
      if (count($arr) == 0) {
        $sql =<<<EOF

CREATE TABLE `$table` (
  `version` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`version`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin;

EOF;
        $stmt = $conn->prepare($sql);
        $stmt ->execute();
      }

      // Insert initial record if it dosen't exist.
      $sql = "select * from ".$table;
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      $arr = $stmt->fetchAll();
      if (count($arr) == 0) {
        $sql = "insert into ".$table."(version) values (:version)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(array(':version' => $version));
      }

      // Update version.
      $sql = "update ".$table." set version = :version";
      $stmt = $conn->prepare($sql);
      $stmt->execute(array(':version' => $version));
    }
  }

  public function getValidDatabases($databases)
  {
    $valid_databases = array();
    if (!$databases) {
      $valid_databases = $this->getDatabaseNames();
    } else {
      $this->validateDatabaseNames($databases);
      $valid_databases = $this->arguments;
    }
    return $valid_databases;
  }

  protected function getDatabaseNames()
  {
    return array_keys(Config::get('databases'));
  }

  protected function validateDatabaseNames($databases)
  {
    $definedDatabaseNames = $this->getDatabaseNames();
    foreach ($databases as $dbname) {
      if (array_search($dbname, $definedDatabaseNames) === false) {
        throw new Exception("Database '".$dbname."' is not defined.");
      }
    }
  }

  protected function getSchemaVersion($database)
  {
    MigrationLogger::log("Getting schema version from '$database'", "debug");
    if ($this->isCliExecution($database)) {
      // cli
      $table = Config::get('databases/'.$database.'/schema_version_table', 'schema_version');
      $sql = "show tables like '".$table."'";

      $arr = $this->execUsingCli($sql, $database);

      // Check to exist table.
      if (count($arr) == 0) {
        MigrationLogger::log("Table [".$table."] is not found. This schema hasn't been managed yet by PHPMigrate.", "debug");
        return null;
      }

      $sql = "select version from ".$table."";
      $arr = $this->execUsingCli($sql, $database);
      if (count($arr) > 0) {
        return $arr[0];
      } else {
        return null;
      }

    } else {
      // pdo

      $conn = $this->getConnection($database);

      $table = Config::get('databases/'.$database.'/schema_version_table', 'schema_version');
      $sql = "show tables like '".$table."'";
      $stmt = $conn->prepare($sql);
      $stmt->execute();

      $arr = $stmt->fetchAll();

      // Check to exist table.
      if (count($arr) == 0) {
        MigrationLogger::log("Table [".$table."] is not found. This schema hasn't been managed yet by PHPMigrate.", "debug");
        return null;
      }

      $sql = "select version from ".$table."";
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
      $dsn      = Config::get('databases/'.$database.'/database_dsn');
      $user     = Config::get('databases/'.$database.'/database_user');
      $password = Config::get('databases/'.$database.'/database_password');

      $this->conns[$database] = new PDO($dsn, $user, $password);
      $this->conns[$database]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
      Config::get('databases/'.$database.'/mysql_command_cli', 'mysql')
      ." -u".Config::get('databases/'.$database.'/mysql_command_user')
      ." -p".Config::get('databases/'.$database.'/mysql_command_password')
      ." -h".Config::get('databases/'.$database.'/mysql_command_host')
      ." --batch -N"
          ." ".Config::get('databases/'.$database.'/mysql_command_options')
          ." ".Config::get('databases/'.$database.'/mysql_command_database')
          ;
    }

    return $this->cli_bases[$database];
  }

  /**
   * Return ture, if it use mysql command to execute migration.
   */
  protected function isCliExecution($database)
  {
    $ret = Config::get('databases/'.$database.'/mysql_command_enable', false);
    if ($ret) {
      if (!Config::get('databases/'.$database.'/mysql_command_user')) {
        throw new Exception("You are using mysql_command. so config [mysql_command_user] is required.");
      }
      if (!Config::get('databases/'.$database.'/mysql_command_host')) {
        throw new Exception("You are using mysql_command. so config [mysql_command_host] is required.");
      }
      if (!Config::get('databases/'.$database.'/mysql_command_password')) {
        throw new Exception("You are using mysql_command. so config [mysql_command_password] is required.");
      }
      if (!Config::get('databases/'.$database.'/mysql_command_database')) {
        throw new Exception("You are using mysql_command. so config [mysql_command_database] is required.");
      }
    }

    return $ret;
  }

  protected function getTmpSqlFilePath($sql, $database)
  {
    $dir = Config::get('databases/'.$database.'/mysql_command_tmpdir', '/tmp');
    $prefix = $database.'_'.md5($sql);
    $uniqid = uniqid();

    $sqlfile = basename(__FILE__).".".$prefix.".".$uniqid.".sql";
    $path = $dir."/".$sqlfile;

    return $path;
  }

  protected function execUsingCli($sql, $database)
  {
    $path = $this->getTmpSqlFilePath($sql, $database);

    MigrationLogger::log("Executing sql is the following \n".$sql, "debug");
    MigrationLogger::log("Creating temporary sql file to [".$path."]", "debug");
    file_put_contents($path, $sql);

    $clibase = $this->getCliBase($database);

    $cmd = $clibase." < ".$path."  2>&1";
    MigrationLogger::log("Executing command is [".$cmd."]", "debug");

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



  protected function getValidMigrationUpFileList($version)
  {
    $valid_files = array();

    $files = $this->getMigrationFileList();
    foreach ($files as $file) {
      preg_match ("/^\d+/", basename($file), $matches);
      $timestamp = $matches[0];

      if ($timestamp > $version) {
        $valid_files[] = $file;
      }
    }

    return $valid_files;
  }

  protected function getValidMigrationDownFileList($version)
  {
    $valid_files = array();

    $files = $this->getMigrationFileList();
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

  protected function getMigrationFileList()
  {
    $files = array();
    $classes = array();
    $gfiles = glob('*');
    foreach ($gfiles as $file) {
      if (preg_match("/^\d+_.+\.php$/", $file)) {

        preg_match("/(\d+)_(.*)\.php$/", basename($file), $matches);
        $version    = $matches[1];
        $class_name = MigrationUtils::camelize($matches[2]);

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
   * List config
   */
  protected function listConfig()
  {
    $largestLength = MigrationUtils::arrayKeyLargestLength(Config::getAllOnFlatArray());
    echo "\n";
    echo "Configurations :\n";
    foreach (Config::getAllOnFlatArray() as $key => $val) {
      if ($largestLength === strlen($key)) {
        $sepalator = str_repeat(" ", 0);
      } else {
        $sepalator = str_repeat(" ", $largestLength - strlen($key));
      }

      echo "  [".$key."] ";
      echo $sepalator;
      if (is_array($val)) {
        echo "=> array()\n";
      } else {
        echo "=> ".$val."\n";
      }
    }
    echo "\n";
  }
}









