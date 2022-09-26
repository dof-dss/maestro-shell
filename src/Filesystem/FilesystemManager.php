<?php

namespace Maestro\Shell\Filesystem;

use DrupalFinder\DrupalFinder;
use Maestro\Core\Filesystem;
use Maestro\Shell\Context;

/**
 * Provides filesystem handling.
 */
class FilesystemManager {

  /**
   * Filesystem for the provided context.
   *
   * @param \Maestro\Shell\Context $context
   *   Context to return filesystem for.
   *
   * @return \Maestro\Core\Filesystem
   *   Filesystem for the context.
   */
  public static function fs(Context $context) {
    return new Filesystem(self::rootPath($context));
  }

  /**
   * Absolute system path to the provided context.
   *
   * @param \Maestro\Shell\Context $context
   *   Context to return path for.
   *
   * @return string
   *   System path for the context.
   */
  public static function rootPath(Context $context) {
    if ($context === Context::Project) {
      $drupalFinder = new DrupalFinder();
      $drupalFinder->locateRoot(getcwd());
      return $drupalFinder->getComposerRoot();
    }

    return MAESTRO_ROOT;
  }

  /**
   * Convert arrays to ini file format.
   *
   * @param array $data
   *   Array of data to be written.
   * @param int $i
   *   Ini file index.
   *
   * @return string
   *   string of ini format data.
   */
  public static function arrayToIni(array $data, $i = 0) {
    $str = "";
    foreach ($data as $key => $val) {
      if (is_array($val)) {
        $str .= str_repeat(" ", $i * 2) . "[$key]" . PHP_EOL;
        $str .= self::arrayToIni($val, $i + 1);
      }
      else {
        $str .= str_repeat(" ", $i * 2) . "$key = $val" . PHP_EOL;
      }
    }
    return $str;
  }

}
