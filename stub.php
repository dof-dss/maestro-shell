#!/usr/bin/env php
<?php
/**
 * @file
 * Platform.sh CLI Phar stub.
 */

if (class_exists('Phar')) {
  Phar::mapPhar('default.phar');
  require 'phar://' . __FILE__ . '/build/maestro';
}

__HALT_COMPILER(); ?>
