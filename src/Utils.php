<?php

namespace Maestro;

use DrupalFinder\DrupalFinder;

/**
 * Maestro Shell Utilities.
 */
class Utils {

  /**
   * Create a machine safe application ID.
   *
   * @param string $name
   *   Name of the project to create an ID for.
   *
   * @return string
   *   Machine safe application ID.
   */
  public static function createApplicationId($name) {
    return strtolower(str_replace(' ', '_', $name));
  }

  /**
   * System path to the Maestro Shell application.
   *
   * @return string
   *   System path for Maestro Shell.
   */
  public static function shellRoot() {
    return PROJECT_ROOT;
  }

  /**
   * System path to the project.
   *
   * @return bool|string
   *   System path or False for not found.
   */
  public static function projectRoot() {
    $drupalFinder = new DrupalFinder();
    $drupalFinder->locateRoot(getcwd());
    return $drupalFinder->getComposerRoot();
  }

}
