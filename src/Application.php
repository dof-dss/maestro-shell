<?php

namespace Maestro;

use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Symfony\Component\Console\Application as ParentApplication;
use Maestro\Commands\ProjectUpdateBaseCommand;
use Maestro\Commands\ProjectBuildCommand;
use Maestro\Commands\ProjectCreateCommand;
use Maestro\Commands\ProjectInfoCommand;
use Maestro\Commands\SiteAddCommand;
use Maestro\Commands\SiteEditCommand;
use Maestro\Commands\SiteRemoveCommand;

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
   * Returns the DI container.
   *
   * @return \Symfony\Component\DependencyInjection\ContainerBuilder
   *   Dependency Injection container.
   */
  public function container() {
    if (!isset($this->container)) {
      $this->container = Utils::container();
    }

    return $this->container;
  }

}
