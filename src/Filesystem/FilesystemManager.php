<?php

namespace Maestro\Shell\Filesystem;

use DrupalFinder\DrupalFinder;
use Maestro\Core\Context;
use Maestro\Core\Filesystem\Filesystem;

/**
 * Provides filesystem handling.
 */
class FilesystemManager {

  /**
   * Filesystem for the provided context.
   *
   * @param \Maestro\Core\Context $context
   *   Context to return filesystem for.
   *
   * @return \Maestro\Core\Filesystem\Filesystem
   *   Filesystem for the context.
   */
  public static function fs(Context $context) {
    return new Filesystem(self::rootPath($context));
  }

  /**
   * Absolute system path to the provided context.
   *
   * @param \Maestro\Core\Context $context
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

    return MAESTRO_SHELL_ROOT;
  }

}
