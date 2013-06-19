<?php
/**
 * LibMigration
 *
 * @package    LibMigration
 */
namespace LibMigration;

/**
 * Migration Logger Class
 *
 * @author kohkimakimoto <kohki.makimoto@gmail.com>
 */
class Logger
{
  protected $config;

  public function __construct($config)
  {
    $this->config = $config;
  }

  public function write($msg, $level = 'info')
  {
    if (!$this->config->get('log', true)) {
      return;
    }

    if ($level == 'debug') {
      if ($this->config->get('debug')) {
        echo "DEBUG >> ".$msg."\n";
      }
    } else {
      echo $msg."\n";
    }
  }
}
