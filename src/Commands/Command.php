<?php

namespace Maestro\Shell\Commands;

use Maestro\Core\Context;
use Maestro\Shell\Filesystem\FilesystemManager;
use Maestro\Shell\Models\Project;
use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

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

    $cache = new FilesystemAdapter();

    $maestro_packages_cache = $cache->getItem('maestro.packages');
    if (!$maestro_packages_cache->isHit()) {

      $fs = FilesystemManager::fs(Context::Project);
      $client = HttpClient::create();

      $maestro_packages = [
        'dof-dss/maestro-shell' => [],
        'dof-dss/maestro-hosting' => [],
      ];

      $project_composer = json_decode($fs->read('composer.lock'));

      foreach ($project_composer->{'packages-dev'} as $package) {
        if (array_key_exists($package->name, $maestro_packages)) {
          $maestro_packages[$package->name]['installed'] = $package->version;
        }
      }

      foreach ($maestro_packages as $package => $versions) {
        $response = $client->request('GET', "https://repo.packagist.org/p2/$package.json");
        $package_data = json_decode($response->getContent());

        $maestro_packages[$package]['latest'] = $package_data->packages->$package[0]->version;
      }

      $maestro_packages_cache->set($maestro_packages);
      $maestro_packages_cache->expiresAt(new \DateTime('tomorrow'));
    } else {
      $maestro_packages = $maestro_packages_cache->get();

      foreach ($maestro_packages as $package => $versions) {
        if ($versions['latest'] == $versions['installed']) {
          $output->writeln('There are updates available for ' . $package . ' (' . $versions['latest'] . ')');
        }
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
