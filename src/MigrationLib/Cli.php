<?php
/**
 * MigrationLib
 *
 * @package    MigrationLib
 */
namespace MigrationLib;

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
    $options = getopt("hdc");
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
        $arguments[] = $raw_argument;
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

    if (!$command) {
      $command = 'help';
    }

    $cwd = getcwd();

    $migration = new Migration(array(
      'migration_dir' => $cwd,
      'debug' => $debug,
    ));

    try {
      $migration->execute($command, $arguments);
    } catch (\Exception $e) {
      if ($debug) {
        fputs(STDERR, $e);
      } else {
        fputs(STDERR, $e->getMessage()."\n");
      }
    }
  }
}