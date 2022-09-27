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

    $commands = [];
    $commands[] = "git fetch upstream main";
    $commands[] = "git pull --no-rebase upstream main";

    $process = new Process(implode(' && ', $commands));
    $process->setWorkingDirectory(FilesystemManager::rootPath(Context::Project));
    $process->run();

    if (!$process->isSuccessful()) {
      // If the error warns that upstream doesn't exist, try adding it.
      if (str_starts_with($process->getErrorOutput(), "fatal: 'upstream' does not appear to be a git repository")) {
        $commands = [];
        $commands[] = "git remote add upstream https://github.com/dof-dss/unity_base.git";
        $commands[] = "git remote set-url --push upstream no-push";

        $process = new Process(implode(' && ', $commands));
        $process->setWorkingDirectory(FilesystemManager::rootPath(Context::Project));
        $process->run();

        if (!$process->isSuccessful()) {
          $io->error("Unable to add upstream remote. Check your permissions and manually add the upstream remote by running:");
          $io->listing([
            'git remote add upstream https://github.com/dof-dss/unity_base.git',
            'git remote set-url --push upstream no-push',
          ]);
          return Command::FAILURE;
        }

        $io->success("Successfully added upstream remote to the repository.");
        $this->execute($input, $output);
      }
      else {
        throw new ProcessFailedException($process);
      }
    }
    else {
      $io->success('Update from Unity Base successful.');

      if ($io->confirm('Would you like to rebuild the project?')) {
        $build_command = $this->getApplication()->find('project:build');
        return $build_command->run(new ArrayInput([]), $output);
      }

      return Command::SUCCESS;
    }

    return Command::FAILURE;
  }

}
