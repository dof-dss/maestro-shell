<?php

namespace Maestro\Shell\Commands;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Maestro\Core\Utils;

/**
 * Command to add a site to a Maestro project.
 */
class SiteAddCommand extends Command {

  /**
   * Defines configuration options for this command.
   */
  protected function configure(): void {
    $this->setName('site:add');
    $this->setDescription('Add a new site to the project');
    $this->setAliases(['sa']);

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

    $site['name'] = $io->ask('Site name');
    $site['url'] = $io->ask('Site URL (minus the protocol and trailing slash');

    $site_id = Utils::createSiteId($site['url']);

    if ($io->confirm('Does this site require a Solr search?')) {
      $site['solr'] = $site_id;
    }

    $site['www'] = 'false';
    if ($io->confirm('Should this site be served from www ?')) {
      $site['www'] = 'true';
    }

    // @todo Prompt if user would like to use cron defaults.
    $site['cron_spec'] = '10 * * * *';
    $site['cron_cmd'] = 'cd web/sites/' . $site_id . ' ; drush core-cron';

    $site['database'] = $site_id;

    $helper = $this->getHelper('question');
    $site_status_list = new ChoiceQuestion(
      'Please select the site status',
      self::SITE_STATUS,
      0
    );

    $site_status_list->setErrorMessage('Status %s is invalid.');

    $site_status = $helper->ask($input, $output, $site_status_list);
    $site['status'] = $site_status;
    $site['default'] = false;

    $this->project()->addSite($site_id, $site);

    if ($io->confirm('Would you like to rebuild the project?')) {
      $build_command = $this->getApplication()->find('project:build');
      return $build_command->run(new ArrayInput([]), $output);
    }

    return Command::SUCCESS;
  }

}
