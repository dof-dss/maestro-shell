<?php

namespace Maestro\Shell\Commands;

use Maestro\Core\Context;
use Maestro\Shell\Filesystem\FilesystemManager;
use Maestro\Shell\Models\Project;
use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;

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
   * Initialize common configuration for all Maestro Shell commands.
   *
   * @inheritdoc
   */
  protected function initialize(InputInterface $input, OutputInterface $output) {

    $fs = FilesystemManager::fs(Context::Project);
    $client = HttpClient::create();

    $maestro_packages = [
      'dof-dss/maestro-shell' => '',
      'dof-dss/maestro-hosting' => '',
    ];

    // Determine the current version of the maestro packages from the project
    // composer file. We cannot use Composer's runtime utils here as that runs
    // within the context of the maestro shell composer.
    $project_composer = json_decode($fs->read('composer.lock'));

    foreach ($project_composer->{'packages-dev'} as $package) {
      if (array_key_exists($package->name, $maestro_packages)) {
        $maestro_packages[$package->name] = $package->version;
      }
    }

    // Fetch the maestro package info from Packagist.
    foreach ($maestro_packages as $package => $installed_version) {
      $response = $client->request('GET', "https://repo.packagist.org/p2/$package.json");
      $package_data = json_decode($response->getContent());

      $latest_version = $package_data->packages->$package[0]->version;

      if ($latest_version == $installed_version) {
        $output->writeln("There are updates available for $package ($latest_version)");
      }
    }


    if ($this->getName() !== 'project:create') {
      $this->project = new Project();
    }
  }

  /**
   * Project getter.
   *
   * @return \Maestro\Shell\Models\Project
   *   Current Project definition.
   */
  public function project() {
    return $this->project;
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
