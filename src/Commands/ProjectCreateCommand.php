<?php

namespace Maestro\Shell\Commands;

use Maestro\Core\Context;
use Maestro\Shell\Filesystem\FilesystemManager;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to create a Maestro project.
 */
class ProjectCreateCommand extends Command {

  /**
   * Defines configuration options for this command.
   */
  protected function configure(): void {
    $this->setName('project:create');
    $this->setDescription('Create a new project');
    $this->setAliases(['pc']);

    $this->addArgument('name', InputArgument::OPTIONAL, 'Project name');
    $this->addArgument('id', InputArgument::OPTIONAL, 'PlatformSH project ID');
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
   *
   * @throws \Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);
    $fs = FilesystemManager::fs(Context::Project);

    $project_name = $input->getArgument('name');

    if (empty($project_name)) {
      $project_name = $io->ask('Please provide a project name (Human readable)');
      if (empty($project_name)) {
        $io->error('Project name not given');
        return Command::FAILURE;
      }
    }

    $project_id = $input->getArgument('id');

    if (empty($project_id)) {
      $project_id = $io->ask('Please provide a PlatformSH project ID');
      if (empty($project_id)) {
        $io->error('Project ID not given');
        return Command::FAILURE;
      }
    }

    if (!$fs->exists('/project')) {
      $fs->createDirectory('/project');
      $fs->createDirectory('/project/config');
      $fs->createDirectory('/project/sites');
      $io->note('Creating project directory.');
    }

    $project['project_name'] = $project_name;
    $project['project_id'] = $project_id;

    $fs->write('/project/project.yml', $project);
    $io->success('Created project file');

    if (!$fs->exists('/maestro.yml')) {
      $fs->copy('/vendor/dof-dss/maestro-shell/resources/maestro_template.yml', '/maestro.yml');
      $io->success('Created maestro file');
    }

    if ($io->confirm('Would you like to add a site to the project?')) {
      $build_command = $this->getApplication()->find('site:add');

      $return_code = $build_command->run(new ArrayInput([]), $output);
      return $return_code;
    }
    return Command::SUCCESS;
  }

}
