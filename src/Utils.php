<?php

namespace Maestro;

use Maestro\Filesystem\FilesystemManager;
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
   * Return an instance of the DI container.
   *
   * @return bool|string
   *   System path or False for not found.
   */
  public static function container() {
    $container = new ContainerBuilder();
    $loader = new YamlFileLoader($container, new FileLocator());
    $loader->load(FilesystemManager::rootPath(Context::Maestro) . '/services.yml');

    $project_root = FilesystemManager::rootPath(Context::Project);

    if (!empty($project_root) && file_exists($project_root. '/maestro.yml')) {
      $loader->load($project_root . '/maestro.yml');
    }
    return $container;
  }

}
