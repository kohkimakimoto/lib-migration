<?php
/**
 * LibMigration
 *
 * @package    LibMigration
 */
namespace LibMigration;

/**
 * Command line interface Class
 *
 * @author kohkimakimoto <kohki.makimoto@gmail.com>
 */
class Cli
{
  /**
   * Main method.
   */
  public static function main()
  {
    list($command, $arguments, $config) = self::preProcess();
    $migration = new Migration($config);

    try {

      $cli = new Cli();
      $cli->execute($migration, $command, $arguments, $config);

    } catch (\Exception $e) {

      $debug = $migration->getConfig()->get('debug');
      $colors = $migration->getConfig()->get('colors');

      if (isset($debug) && $debug) {

        if ($colors) {
          fputs(STDERR, pack('c',0x1B)."[1;37;41m".$e.pack('c',0x1B)."[0m\n");
        } else {
          fputs(STDERR, $e);
        }

      } else {

        if ($colors) {
          fputs(STDERR, pack('c',0x1B)."[1;37;41m".$e->getMessage().pack('c',0x1B)."[0m\n");
        } else {
          fputs(STDERR, $e->getMessage()."\n");
        }

      }
    }
  }

  public static function preProcess()
  {
    $options = getopt("hdcf:");
    $argv = $_SERVER['argv'];
    $raw_arguments = $argv;
    $command = null;

    $debug = false;
    if (isset($options['d'])) {
      $debug = true;
    }

    // Remove program name.
    if (isset($raw_arguments[0])) {
      array_shift($raw_arguments);
    }

    // Process arguments
    $arguments = array();
    $i = 0;
    while ($raw_argument = array_shift($raw_arguments)) {
      if ('-' == substr($raw_argument, 0, 1)) {

      } else {
        if ($argv[$i] !== '-f') {
          $arguments[] = $raw_argument;
        }
      }
      $i++;
    }
    $command = array_shift($arguments);

    if (isset($options['h'])) {
      $command = 'help';
    }

    if (isset($options['c'])) {
      $command = 'config';
    }

    $config_file = 'migration.php';
    if (isset($options['f'])) {
      $config_file = $options['f'];
    }

    if (!$command) {
      $command = 'help';
    }

    $config = array(
        'config_file' => $config_file,
        'debug' => $debug,
    );

    return array(
      $command,
      $arguments,
      $config
    );
  }

  /**
   * Execute
   * @param unknown $task
   * @param unknown $options
   */
  public function execute($migration, $command, $arguments, $config)
  {
    if ($command == 'help') {

      $migration ->helpForCli();

    } elseif ($command == 'init') {

      $migration ->init();

    } elseif ($command == 'config') {

      $migration ->listConfig();

    } elseif ($command == 'create') {

      if (count($arguments) > 0) {
        $name = array_shift($arguments);
      } else {
        throw new Exception("You need to pass the argument for migration task name.");
      }

      $migration ->create($name, $arguments);

    } elseif ($command == 'status') {

      // arguments are database names to be processed.
      $migration ->status($arguments);

    } elseif ($command == 'migrate') {

      // arguments are database names to be processed.
      $migration ->migrate($arguments);

    } elseif ($command == 'up') {

      // arguments are database names to be processed.
      $migration ->up($arguments);

    } elseif ($command == 'down') {

      // arguments are database names to be processed.
      $migration ->down($arguments);

    } else {
      throw new Exception('Unknown command: '.$command);
    }
  }

}