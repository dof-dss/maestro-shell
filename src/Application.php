<?php

namespace Maestro\Shell;

use Maestro\Shell\Commands\ProjectBuildCommand;
use Maestro\Shell\Commands\ProjectCreateCommand;
use Maestro\Shell\Commands\ProjectInfoCommand;
use Maestro\Shell\Commands\ProjectUpdateBaseCommand;
use Maestro\Shell\Commands\SiteAddCommand;
use Maestro\Shell\Commands\SiteEditCommand;
use Maestro\Shell\Commands\SiteRemoveCommand;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Symfony\Component\Console\Application as ParentApplication;

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

}
