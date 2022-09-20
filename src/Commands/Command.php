<?php

namespace Maestro\Commands;

use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Maestro\Models\Project;
use Maestro\Services\FileSystemDecorator;

/**
 * Base class form building Maestro Shell commands.
 */
abstract class Command extends ConsoleCommand {

  /**
   * Command return values.
   */
  public const SUCCESS = 0;
  public const FAILURE = 1;
  public const INVALID = 2;

  /**
   * The site development status.
   */
  protected const SITE_STATUS = [
    'development',
    'production',
  ];

  /**
   * The Maestro project definition.
   */
  protected Project $project;

  /**
   * The FileSystemDecorator.
   *
   * @var \Maestro\Services\FileSystemDecorator
   */
  private FileSystemDecorator $fs;

  /**
   * Initialize common configuration for all Maestro Shell commands.
   *
   * @inheritdoc
   */
  protected function initialize(InputInterface $input, OutputInterface $output) {
    if ($this->getName() !== 'project:create') {
      $this->project = new Project();
    }
  }

  /**
   * Project getter.
   *
   * @return \Maestro\Models\Project
   *   Current Project definition.
   */
  public function project() {
    return $this->project;
  }

  /**
   * FileSystemDecorator getter.
   *
   * @return \Maestro\Services\FileSystemDecorator
   *   The FileSystemDecorator.
   */
  public function fs() {
    if (empty($this->fs)) {
      $this->fs = $this->container()->get('Maestro.filesystem');
    }
    return $this->fs;
  }

  /**
   * Returns the DI container.
   *
   * @return \Symfony\Component\DependencyInjection\ContainerBuilder
   *   Dependency Injection container.
   */
  protected function container() {
    return $this->getApplication()->container();
  }

}
