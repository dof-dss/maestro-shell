<?php

namespace Maestro\Shell;

use Maestro\Core\Context;
use Maestro\Shell\Commands\ProjectBuildCommand;
use Maestro\Shell\Commands\ProjectCreateCommand;
use Maestro\Shell\Commands\ProjectInfoCommand;
use Maestro\Shell\Commands\ProjectUpdateBaseCommand;
use Maestro\Shell\Commands\SiteAddCommand;
use Maestro\Shell\Commands\SiteEditCommand;
use Maestro\Shell\Commands\SiteRemoveCommand;
use Maestro\Shell\Filesystem\FilesystemManager;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application as ParentApplication;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Maestro Shell Application.
 */
class Application extends ParentApplication {

  /**
   * The Dependency Injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  private $container;

  /**
   * Class constructor.
   *
   * @inheritDoc
   */
  public function __construct() {
    parent::__construct("Maestro", "1.0.0");

    $this->addCommands([
      new CompletionCommand(),
      new ProjectUpdateBaseCommand(),
      new ProjectBuildCommand(),
      new ProjectCreateCommand(),
      new ProjectInfoCommand(),
      new SiteAddCommand(),
      new SiteEditCommand(),
      new SiteRemoveCommand(),
    ]);
  }

  /**
   * @return ContainerBuilder
   */
  public function container() {
    $fs = FilesystemManager::fs(Context::Project);

//    if ($fs->exists('/maestro.yml')) {
      $path = FilesystemManager::rootPath(Context::Project) . '/maestro.yml';
      $this->container = new ContainerBuilder();
      $loader = new YamlFileLoader($this->container, new FileLocator());
      $loader->load($path);
//    }

//    $this->container = new ContainerBuilder();
//    $this->container->register("maestro.common", "Maestro\Hosting\Provider\Common");

    return $this->container;
  }

}
