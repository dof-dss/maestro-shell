<?php

namespace Maestro\Shell\Filesystem;

use Maestro\Core\FilesystemInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides basic filesystem functions.
 */
class Filesystem implements FilesystemInterface {

  /**
   * Warning text to prepend to template files.
   *
   * @var string
   */
  protected $warning_text = <<<EOD
    #############################################################################
    ###                           --== IMPORTANT ==--                         ###
    #############################################################################
    # If you require changes to this file you must edit the file within the     #
    # original repository.Any changes here will be overwritten when the project #
    # is built.                                                                 #
    #############################################################################

    EOD;

  /**
   * The root path.
   *
   * @var string
   */
  protected $rootPath;

  /**
   * The FileSystem.
   *
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected $fs;

  /**
   * @param $path
   *   The root system path.
   */
  public function __construct($path) {
    $this->rootPath = $path;
    $this->fs = new \Symfony\Component\Filesystem\Filesystem();
  }

  /**
   * {@inheritdoc}
   */
  public function exists($path) {
    return $this->fs->exists($this->fullPath($path));
  }

  /**
   * {@inheritdoc}
   */
  public function read($path) {
    $path = $this->fullPath($path);

    if (!$this->fs->exists($path)) {
      return NULL;
    }

    switch (pathinfo($path, PATHINFO_EXTENSION)) {
      case 'yaml':
      case 'yml':
        return Yaml::parseFile($path);

      case 'env':
        return parse_ini_file($path);

      case 'json':
      case 'lock':
        $data = file_get_contents($path);
        return json_decode($data);

      default:
        return file_get_contents($path);

    }
  }

  /**
   * {@inheritdoc}
   */
  public function write($path, $content, $add_warning=FALSE) {
    $path = $this->fullPath($path);

    if (str_ends_with($path, '.env')) {
      $content = $this->arrayToIni($content);
    }

    if (str_ends_with($path, '.yml') || str_ends_with($path, '.yaml')) {
      $content = Yaml::dump($content, 6, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
    }

    if (str_ends_with($path, '.json') || str_ends_with($path, '.lock')) {
      $content = json_encode($content);
    }

    if ($add_warning) {
      $content = $this->warning_text . $content;
    }

    $this->fs->dumpFile($path, $content);
  }

  /**
   * {@inheritdoc}
   */
  public function delete($path) {
    $this->fs->remove($this->fullPath($path));
  }

  /**
   * {@inheritdoc}
   */
  public function copy($path, $destination) {
    $path = $this->fullPath($path);
    $destination = $this->fullPath($destination);

    $this->fs->copy($path, $destination);
  }

  /**
   * {@inheritdoc}
   */
  public function copyDirectory($path, $destination) {
    $path = $this->fullPath($path);
    $destination = $this->fullPath($destination);

    $this->fs->mirror($path, $destination);
  }

  /**
   * {@inheritdoc}
   */
  public function createDirectory($path) {
    $this->fs->mkdir($this->fullPath($path));
  }

  /**
   * {@inheritdoc}
   */
  public function pathContents($path, PathContentsFilter $filter = PathContentsFilter::All) {
    if (!is_dir($path) && is_file($path)) {
      return $this->read($path);
    }

    $contents = array_diff(scandir($this->fullPath($path)), array('..', '.'));

    if ($filter === PathContentsFilter::Directories) {
      $directories = [];
      foreach ($contents as $item) {
        if (is_dir($this->fullPath($path) . '/' . $item)) {
          $directories[] = $item;
        }
      }

      return $directories;
    }
    elseif ($filter === PathContentsFilter::Files) {
      $files = [];
      foreach ($contents as $item) {
        if (is_file($this->fullPath($path) . '/' . $item)) {
          $files[] = $item;
        }
      }

      return $files;
    }
    else {
      return $contents;
    }

  }

  /**
   * {@inheritdoc}
   */
  public function link($source, $link) {
    $this->fs->symlink($this->fullPath($source), $this->fullPath($link));
  }

  /**
   * Set the root path from which all file operation are based.
   *
   * @param string $path The path to set as the root.
   */
  public function setRootPath($path) {
    $this->rootPath = $path;
  }

  /**
   * Returns the full filesystem path.
   *
   * @param $path
   *   The path to append to the root path.
   * @return string
   *   The full root and path.
   */
  protected function fullPath($path) {
    // If the path starts with a double slash do not prepend the rootPath.
    if (str_starts_with($path, '//')) {
      return substr($path, 1, strlen($path));
    }

    // Prefix dir slash if missing.
    if (!str_starts_with($path, '/')) {
      $path = '/' . $path;
    }

    return $this->rootPath . $path;
  }

  /**
   * Convert arrays to ini file format.
   *
   * @param array $data
   *   Array of data to be written.
   * @param int $i
   *   Ini file index.
   *
   * @return string
   *   string of ini format data.
   */
  private function arrayToIni(array $data, $i = 0) {
    $str = "";
    foreach ($data as $key => $val) {
      if (is_array($val)) {
        $str .= str_repeat(" ", $i * 2) . "[$key]" . PHP_EOL;
        $str .= $this->arrayToIni($val, $i + 1);
      }
      else {
        $str .= str_repeat(" ", $i * 2) . "$key = $val" . PHP_EOL;
      }
    }
    return $str;
  }

}
