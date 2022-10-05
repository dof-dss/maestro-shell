<?php

namespace Maestro\Shell\Commands;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to designate a site as the default.
 */
class SiteDefaultCommand extends Command {

  /**
   * Defines configuration options for this command.
   */
  protected function configure(): void {
    $this->setName('site:default');
    $this->setDescription('Designate a site as the default for the project');
    $this->setAliases(['sd']);

    $this->addArgument('siteid', InputArgument::OPTIONAL, 'Site ID (Must be a machine name e.g. uregni)');
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

    $site_id = $input->getArgument('siteid');

    // @todo Warn if we have no site entries.
    // Provide a list of sites from the project file for the user to select.
    if (empty($site_id)) {
      $site_options = ['Cancel'];
      $site_options = array_merge($site_options, array_keys($this->project()->sites()));

      $helper = $this->getHelper('question');
      $sites_choice_list = new ChoiceQuestion(
        'Please select a site to edit',
        $site_options,
        0
      );
      $sites_choice_list->setErrorMessage('Site %s is invalid.');

      $site_id = $helper->ask($input, $output, $sites_choice_list);

      if ($site_id === 'Cancel') {
        $io->info('Cancelling site edit.');
        return Command::SUCCESS;
      }
    }

    if (!array_key_exists($site_id, $this->project()->sites())) {
      throw new \InvalidArgumentException("Site ID '$site_id' does not exist in the project.");
    }

    foreach ($this->project()->sites() as $id => $site) {
      // Prevent multiple default site designations.
      if ($id !== $site_id && $site['default'] === true) {
        $site['default'] = false;
        $this->project()->updateSite($id, $site);
      } elseif ($id === $site_id && $site['default'] === false) {
        $site['default'] = true;
        $this->project()->updateSite($id, $site);
      }
    }

    if ($io->confirm('Would you like to rebuild the project?')) {
      $build_command = $this->getApplication()->find('project:build');
      return $build_command->run(new ArrayInput([]), $output);
    }
    return Command::SUCCESS;
  }

}
