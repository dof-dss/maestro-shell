<?php

namespace Maestro\Shell\Commands;

use Maestro\Core\Context;
use Maestro\Core\Utils;
use Maestro\Shell\Filesystem\FilesystemManager;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Command to update the project with changes from the base repository.
 */
class ProjectUpdateBaseCommand extends Command {

  /**
   * Defines configuration options for this command.
   */
  protected function configure(): void {
    $this->setName('project:update-base');
    $this->setDescription('Update the project with the latest changes from the base repository');
    $this->setAliases(['pub']);
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

    $process = new Process(["git", "remote", "get-url", "upstream"]);
    $process->setWorkingDirectory(FilesystemManager::rootPath(Context::Project));
    $process->run();

    if (!$process->isSuccessful()) {
      $this->addUpstreamRepo($input, $output, $io);
    } else {
      $io->info("Fetching upstream");
      $process = new Process(["git", "fetch", "upstream", "main"]);
      $process->setWorkingDirectory(FilesystemManager::rootPath(Context::Project));
      $process->run();

      if ($process->isSuccessful()) {
        $process = new Process(["git", "pull", "upstream", "main", "--no-rebase"]);
        $process->setWorkingDirectory(FilesystemManager::rootPath(Context::Project));

        // Output any warnings/errors
        $process->run(function ($type, $buffer) use ($io) {
          if (Process::ERR === $type) {
            $io->error($buffer);
          } else {
            $io->info($buffer);
          }
        });

        if ($process->isSuccessful()) {
          $io->success('Update from project base repository successful.');

          if ($io->confirm('Would you like to rebuild the project?')) {
            $build_command = $this->getApplication()->find('project:build');
            return $build_command->run(new ArrayInput([]), $output);
          }
          return Command::SUCCESS;
        } else {
          $io->warning("Unable to update base.");
          return Command::FAILURE;
        }
      }
    }

    return Command::FAILURE;
  }

  /**
   * Adds the upstream repository to the local user's git repository.
   *
   * @param InputInterface $input
   * @param OutputInterface $output
   * @param SymfonyStyle $io
   * @return int|void
   * @throws \Exception
   */
  private function addUpstreamRepo(InputInterface $input, OutputInterface $output, SymfonyStyle $io) {
    $fs = FilesystemManager::fs(Context::Project);
    $composer_json = $fs->read('/composer.json');

    $process = new Process(["git", "remote", "add", "upstream", "https://github.com/" . $composer_json->name]);
    $process->setWorkingDirectory(FilesystemManager::rootPath(Context::Project));
    $process->run();

    if ($process->isSuccessful()) {
      $process = new Process(["git", "remote", "set-url", "--push", "upstream", "no-push"]);
      $process->setWorkingDirectory(FilesystemManager::rootPath(Context::Project));
      $process->run();

      if ($process->isSuccessful()) {
        $io->success("Successfully added upstream remote (" . $composer_json->name . ") to the repository.");
        $this->execute($input, $output);
      }
    } else {
      if (!$process->isSuccessful()) {
        $io->error("Unable to add upstream remote. Check your permissions and manually add the upstream remote.");
        return Command::FAILURE;
      }
    }
  }
}