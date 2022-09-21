<?php

namespace Maestro\Commands;

use League\Flysystem\FilesystemException;
use Maestro\Context;
use Maestro\Filesystem\FilesystemManager;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

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

    $fs->write('test.txt', 'foo');

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

    if (!$fs->directoryExists('project')) {
      $fs->createDirectory('project');
      $fs->createDirectory('project/config');
      $fs->createDirectory('project/sites');
      $io->note('Creating project directory.');
    }

    $project['project_name'] = $project_name;
    $project['project_id'] = $project_id;

    try {
      $fs->write('project/project.yml', Yaml::dump($project, 6));
      $io->success('Created project file');

      if ($io->confirm('Would you like to add a site to the project?')) {
        $build_command = $this->getApplication()->find('site:add');

        $return_code = $build_command->run(new ArrayInput([]), $output);
        return $return_code;
      }
      return Command::SUCCESS;
    }
    catch (FilesystemException $e) {
      $io->error('Unable to create Project file, error: ' . $e->getMessage());
      return Command::FAILURE;
    }
  }

}
