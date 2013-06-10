<?php

namespace MigrationLib;



/**
 * Migration Logger Class
 *
 * @author kohkimakimoto <kohki.makimoto@gmail.com>
 * @version $Revision$
 */
class MigrationLogger
{
  public static function log($msg, $level = 'info')
  {
    if (!MigrationConfig::get('log', true)) {
      return;
    }

    if ($level == 'debug') {
      if (MigrationConfig::get('debug')) {
        echo "DEBUG >> ".$msg."\n";
      }
    } else {
      echo $msg."\n";
    }
  }
}

if (realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
  // Run the main program logic, when this script file is directly executed.
  Migration::main();
}

