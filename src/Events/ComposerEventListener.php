<?php

namespace Maestro\Shell\Events;

use Composer\Installer\PackageEvent;
use Symfony\Component\Cache\Adapter\FilesystemAdapter as CacheFilesystemAdapter;

/**
 * Event listener for Composer events.
 */
class ComposerEventListener {

  public static function postPackageUpdate(PackageEvent $event) {
    $operation = $event->getOperation();

    $package = method_exists($operation, 'getPackage') ? $operation->getPackage() : $operation->getInitialPackage();

    // Clear the maestro package versions cache to prevent cached banner display
    // after packages have been updated.
    if (in_array($package->getName(), ['dof-dss/maestro-shell', 'dof-dss/maestro-hosting'])) {
      $cache = new CacheFilesystemAdapter();
      $cache->delete('maestro.packages');
    }
  }

}
