<?php

namespace UnityShell\Commands;

use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use UnityShell\FileSystemDecorator;

/**
 * Base class form building Unity Shell commands.
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
   * The FileSystemDecorator.
   *
   * @var \UnityShell\FileSystemDecorator
   */
  private FileSystemDecorator $fs;

  /**
   * Initialize common configuration for all Unity Shell commands.
   *
   * @inheritdoc
   */
  protected function initialize(InputInterface $input, OutputInterface $output) {
    // @todo Create fs as a service and inject.
    $this->fs = new FileSystemDecorator(new Filesystem());
  }

  /**
   * FileSystemDecorator getter.
   *
   * @return \UnityShell\FileSystemDecorator
   *   The FileSystemDecorator.
   */
  public function fs() {
    return $this->fs;
  }

}