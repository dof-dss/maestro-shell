<?php

namespace Maestro\Shell\Commands;

use Maestro\Core\Context;
use Maestro\Core\Utils;
use Maestro\Shell\Filesystem\FilesystemManager;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Command to edit a site within a Maestro project.
 */
class SiteEditCommand extends Command {

  /**
   * Defines configuration options for this command.
   */
  protected function configure(): void {
    $this->setName('site:edit');
    $this->setDescription('Edit a site in the project');
    $this->setAliases(['se']);

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
    $site_id_update = FALSE;

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
        return Command::SUCCESS;
      }
    }

    if (!array_key_exists($site_id, $this->project()->sites())) {
      throw new \InvalidArgumentException("Site ID '$site_id' does not exist in the project.");
    }

    $site_current = $this->project()->sites()[$site_id];
    $site['name'] = $io->ask('Site name', $site_current['name']);
    $site['url'] = $io->ask('Site URL (minus the protocol and trailing slash', $site_current['url']);

    if ($site['url'] !== $site_current['url']) {
      $old_site_id = $site_id;
      $site_id = Utils::createSiteId($site['url']);

      if ($io->confirm('Site ID will be updated, would you like to update the project directories and symlinks?')) {
        $this->project()->removeSite($old_site_id);

        $fs = FilesystemManager::fs(Context::Project);

        // Move existing site assets to the new site id.
        $fs->copyDirectory('/project/config/' . $old_site_id, '/project/config/' . $site_id);
        $fs->copyDirectory('/project/sites/' . $old_site_id, '/project/sites/' . $site_id);

        // Remove the old site id directories.
        $fs->delete('/project/config/' . $old_site_id);
        $fs->delete('/project/sites/' . $old_site_id);

        // Remove old symlink. A new one will be created during project build.
        $fs->delete('/web/sites/' . $old_site_id);
      }
      $site_id_update = TRUE;
    }

    if ($io->confirm('Does this site require a Solr search?')) {
      $site['solr'] = $site_id;
    }

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

    if ($site_id_update) {
      $this->project()->addSite($site_id, $site);
    } else {
      // Ensure that default setting is not lost.
      if ($this->project()->sites()[$site_id]['default']) {
        $site['default'] = true;
      } else {
        $site['default'] = false;
      }
      $this->project()->updateSite($site_id, $site);
    }

    if ($io->confirm('Would you like to rebuild the project?')) {
      $build_command = $this->getApplication()->find('project:build');
      return $build_command->run(new ArrayInput([]), $output);
    }
    return Command::FAILURE;
  }

}
