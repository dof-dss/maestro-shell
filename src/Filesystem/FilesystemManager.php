<?php

namespace Maestro\Shell\Filesystem;

use DrupalFinder\DrupalFinder;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
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
   * @return Filesystem
   *   Filesystem for the context.
   */
  public static function fs(Context $context) {
    $adapter = new LocalFilesystemAdapter(
      self::rootPath($context)
    );

    return new Filesystem($adapter);
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

}
