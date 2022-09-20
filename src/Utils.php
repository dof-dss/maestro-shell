<?php

namespace Maestro;

use DrupalFinder\DrupalFinder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

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
    return MAESTRO_ROOT;
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

  /**
   * Return an instance of the DI container.
   *
   * @return bool|string
   *   System path or False for not found.
   */
  public static function container() {
    $container = new ContainerBuilder();
    $loader = new YamlFileLoader($container, new FileLocator());
    $loader->load(self::shellRoot() . '/services.yml');

    if (file_exists(self::projectRoot() . '/maestro.yml')) {
      $loader->load(self::projectRoot() . '/maestro.yml');
    }
    return $container;
  }

}
