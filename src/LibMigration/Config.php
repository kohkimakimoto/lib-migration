<?php
/**
 * LibMigration
 *
 * @package    LibMigration
 */
namespace LibMigration;

/**
 * Migration Connfig Class
 *
 * @author kohkimakimoto <kohki.makimoto@gmail.com>
 */
class Config
{
  /**
   * Array of configuration values.
   * @var unknown
   */
  protected $config = array();

  public function __construct($config = array())
  {
    $this->merge($config);
  }

  /**
   * Get a config parameter.
   * @param unknown $name
   * @param string $default
  */
  public function get($name, $default = null, $delimiter = '/')
  {
    $config = $this->config;
    foreach (explode($delimiter, $name) as $key) {
      $config = isset($config[$key]) ? $config[$key] : $default;
    }
    return $config;
  }

  /**
   * Set a config parameter.
   * @param unknown $name
   * @param unknown $value
   */
  public function set($name, $value)
  {
    $this->config[$name] = $value;
  }

  public function delete($name)
  {
    unset($this->config[$name]);
  }

  /**
   * Merge config array.
   * @param unknown $path
   */
  public function merge($arr)
  {
    $this->config = array_merge($this->config, $arr);
  }

  /**
   * Get All config parameters.
   * @return multitype:
   */
  public function getAll()
  {
    return $this->config;
  }

  public function getAllOnFlatArray($namespace = null, $key = null, $array = null, $delimiter = '/')
  {
    $ret = array();

    if ($array === null) {
      $array = $this->config;
    }

    foreach ($array as $key => $val) {
      if (is_array($val) && $val) {
        if ($namespace === null) {
          $ret = array_merge($ret, $this->getAllOnFlatArray($key, $key, $val, $delimiter));
        } else {
          $ret = array_merge($ret, $this->getAllOnFlatArray($namespace.$delimiter.$key, $key, $val, $delimiter));
        }
      } else {
        if ($namespace !== null) {
          $ret[$namespace.$delimiter.$key] = $val;
        } else {
          $ret[$key] = $val;
        }
      }
    }

    return $ret;
  }
}
