<?php

namespace Maestro\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to build a Maestro project.
 */
class ProjectBuildCommand extends Command {

  /**
   * Hosting service instructions.
   *
   * @var array
   */
  protected $instructions = [];

  /**
   * Defines configuration options for this command.
   */
  protected function configure(): void {
    $this->setName('project:build');
    $this->setDescription('Builds hosting environments for this project');
    $this->setAliases(['pb']);
  }

  /**
   * The command execution.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   CLI input interface.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   CLI output interface.
   *
   * @return int
   *   return 0 if command successful, non-zero for failure.
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    // Warn if we don't have a project file.
    if (empty($this->project()->sites())) {
      $io->warning('This project does not have any sites defined, please add some using site:add before running this command.');
      return Command::FAILURE;
    }

    // Retrieve each hosting service and if enabled, execute its build.
    $hosting_service_ids = $this->container()->findTaggedServiceIds('maestro.hosting');

    if (!empty($hosting_service_ids)) {
      $io->title('## Hosting setup ##');

      foreach ($hosting_service_ids as $service_id => $data) {
        $service = $this->container()->get($service_id);
        if ($service->isEnabled()) {
          $service->build($io);
          $this->instructions = array_merge($this->instructions, $service->instructions());
        }
      }
    }

    $io->title('## Instructions ##');
    $io->listing($this->instructions);

    return Command::SUCCESS;
  }

}
