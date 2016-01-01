<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
$loader = require __DIR__.'/../vendor/autoload.php';

$configDir = __DIR__.'/../vendor/ezsystems/ezpublish-kernel';

if (!file_exists($configDir.'/config.php')) {
    if (!symlink($configDir.'/config.php-DEVELOPMENT', $configDir.'/config.php')) {
        throw new \RuntimeException('Could not symlink config.php-DEVELOPMENT to config.php!');
    }
}

$loader->addPsr4('Transfer\\EzPlatform\\Tests\\', __DIR__.'/../vendor/transfer/ezplatform/tests/Transfer/EzPlatform/Tests');
$loader->addPsr4('Transfer\\Examples\\', __DIR__.'/../src/Transfer/Examples');
$loader->addPsr4('Transfer\\Examples\\Tests\\', __DIR__.'/Transfer/Examples/Tests');
