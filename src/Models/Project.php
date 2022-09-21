<?php

namespace Maestro\Models;

use League\Flysystem\Filesystem;
use Maestro\Context;
use Maestro\Filesystem\FilesystemManager;
use RomaricDrigon\MetaYaml\Loader\YamlLoader;
use RomaricDrigon\MetaYaml\MetaYaml;
use Maestro\Utils;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides methods for managing a Unity Project definition file.
 */
class Project {

  /**
   * The project definition.
   *
   * @var object|string[]|null
   */
  protected array $project;

  /**
   * The Filesystem.
   *
   * @var \League\Flysystem\Filesystem
   */
  private Filesystem $fs;

  /**
   * Project constructor.
   */
  public function __construct() {
    $this->fs = FilesystemManager::fs(Context::Project);

    if (!$this->fs()->fileExists('project/project.yml')) {
      throw new \Exception("Project file not found.");
    }

    // Load the Project file.
    $project = Yaml::parse($this->fs()->read('project/project.yml'));

    try {
      $this->validate($project);
      $this->project = $project;
    }
    catch (\Exception $exception) {
      throwException($exception);
    }
  }

  /**
   * Save the project.
   */
  public function save() {
    try {
      $this->validate($this->project);
      $this->fs()->write('project/project.yml', Yaml::dump($this->project, 6));
    }
    catch (\Exception $exception) {
      throwException($exception);
    }
  }

  /**
   * Add a site to the project.
   *
   * @param string $site_id
   *   Site identifier.
   * @param array $site_data
   *   Site configuration data.
   */
  public function addSite(string $site_id, array $site_data) {
    // @todo Validate site data.
    if ($this->siteExists($site_id)) {
      throw new \InvalidArgumentException("Site ID '$site_id' already exists in the project.");
    }

    $this->project['sites'][$site_id] = $site_data;
    $this->save();
  }

  /**
   * Update a site within the project.
   *
   * @param string $site_id
   *   Site identifier.
   * @param array $site_data
   *   Site configuration data.
   */
  public function updateSite(string $site_id, array $site_data) {
    // @todo Validate site data.
    if (!$this->siteExists($site_id)) {
      throw new \InvalidArgumentException("Site ID '$site_id' does not exist in the project.");
    }

    $this->project['sites'][$site_id] = $site_data;
    $this->save();
  }

  /**
   * Remove a site from the project.
   *
   * @param string $site_id
   *   Site identifier.
   */
  public function removeSite(string $site_id) {
    if (!$this->siteExists($site_id)) {
      throw new \InvalidArgumentException("Site ID '$site_id' does not exist in the project.");
    }
    unset($this->project['sites'][$site_id]);
    $this->save();
  }

  /**
   * Validates the project file.
   *
   * @param array $project_data
   *   Project definition array.
   *
   * @return bool|Exception
   *   True if valid, false if invalid and exception if there was an issue.
   *
   * @throws \Exception
   *   Exception if unable to validate the data.
   */
  protected function validate(array $project_data) {
    // Validate Project file.
    $yaml_loader = new YamlLoader();
    $schema_data = $yaml_loader->loadFromFile(FilesystemManager::rootPath(Context::Maestro) . '/resources/schemas/maestro_project.yml');
    $schema = new MetaYaml($schema_data);

    try {
      return $schema->validate($project_data);
    }
    catch (\Exception $exception) {
      return $exception;
    }
  }

  /**
   * Project name.
   *
   * @return string|null
   *   The project name.
   */
  public function name() {
    return $this->project['project_name'];
  }

  /**
   * Project ID.
   *
   * @return string|null
   *   The project ID.
   */
  public function id() {
    return $this->project['project_id'];
  }

  /**
   * Project type.
   *
   * @return string|null
   *   The project type.
   */
  public function type() {
    return $this->project['project_type'];
  }

  /**
   * Sites for the project.
   *
   * @return array
   *   Array of project sites.
   */
  public function sites() {
    return $this->project['sites'] ?? NULL;
  }

  /**
   * Determine if a site exists in the Project.
   *
   * @return bool
   *   True if site exists, otherwise false.
   */
  public function siteExists($site_id) {
    if (!array_key_exists('sites', $this->project)) {
      return FALSE;
    }

    return array_key_exists($site_id, $this->project['sites']);
  }

  /**
   * Filesystem getter.
   *
   * @return \League\Flysystem\Filesystem
   *   The Filesystem instance.
   */
  public function fs() {
    return $this->fs;
  }

}
