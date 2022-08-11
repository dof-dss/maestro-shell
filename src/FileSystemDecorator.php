<?php

namespace App;

use DrupalFinder\DrupalFinder;
use Symfony\Component\Yaml\Yaml;

/**
 * Decorator for the symfony FileSystem component.
 *
 * This decorator mirrors the FileSystem component but also provides
 * custom methods for reading and writing files.
 *
 * See: https://symfony.com/doc/current/components/filesystem.html
 *
 * By default paths passed to these methods will be prefixed with the project
 * root path. If you require an absolute path you should start the path with
 * a double slash (e.g. //app/project/sites).
 *
 */
class FileSystemDecorator {

    protected $fs;
    protected string $projectRoot;

    /**
     * FileSystemDecorator constructor.
     *
     * @param $file_system
     *   Filesystem component.
     */
    public function __construct($file_system) {
        $this->fs = $file_system;

        $drupalFinder = new DrupalFinder();
        $drupalFinder->locateRoot(getcwd());
        $this->projectRoot = $drupalFinder->getComposerRoot();
    }

    /**
     * Magic method to process calls before passing on.
     *
     * @param $method
     * @param $args
     * @return array|false|mixed|string|null
     * @throws \Exception
     */
    public function __call($method, $args) {
        // Pretty basic way to determine if the arg is a file/dir path.
        // Could do with a lot of improvement.
        foreach ($args as $index => $val) {
            // If a path starts with a double slash, do not prepend the
            // project root path and remove the leading slash.
            if (is_string($val) && str_starts_with($val, '//')) {
                $args[$index] = substr($val, 1, strlen($val));
            }
            else if (is_string($val) && str_starts_with($val, '/')) {
                $args[$index] = $this->projectRoot . $val;
            }
        }

        if ($method == 'readFile') {
            return $this->readFile($args);
        }

        if ($method == 'dumpFile') {
            return call_user_func_array([$this, 'dumpFile'], $args);
        }

        if (is_callable([$this->fs, $method])) {
            return call_user_func_array([$this->fs, $method], $args);
        }
        throw new \Exception('Undefined method: ' . get_class($this->fs) . '::' . $method);
    }

    /**
     * Read and parse contents of multiple file types.
     *
     * @param $file_path
     * @return array|false|mixed|string|null
     */
    protected function readFile($file_path) {
        $file_path = current($file_path);

        if (!$this->fs->exists($file_path)) {
            return null;
        }

        switch (pathinfo($file_path, PATHINFO_EXTENSION)) {
            case 'yaml':
            case 'yml':
                return Yaml::parseFile($file_path);
            case 'env':
                return parse_ini_file($file_path);
            default:
                return file_get_contents($file_path);
        }
    }

    /**
     * Write content to a file.
     *
     * @param $file_path
     * @param $contents
     */
    protected function dumpFile($file_path, $contents) {
        if (str_ends_with($file_path, '.env')) {
            $contents = $this->writeIniFile($contents);
        }

        $this->fs->dumpFile($file_path, $contents);
    }


    /**
     * @param $data
     *  Array of data to be written.
     * @param $i
     *  ini file index.
     * @return string
     */
    private function writeIniFile(array $data, $i = 0){
        $str="";
        foreach ($data as $key => $val){
            if (is_array($val)) {
                $str.=str_repeat(" ",$i*2)."[$key]".PHP_EOL;
                $str.= $this->writeIniFile($val, $i+1);
            } else {
                $str.=str_repeat(" ",$i*2)."$key = $val".PHP_EOL;
            }
        }
        return $str;
    }


}