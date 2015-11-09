<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\Examples\Tests\YamlToEzPlatformContentType\Manifest;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Transfer\Event\TransferEvents;
use Transfer\Examples\YamlToEzPlatformContentType\Manifest\YamlToEzPlatformContentTypeManifest;
use Transfer\EzPlatform\Tests\EzPlatformTestCase;
use Transfer\Manifest\ManifestRunner;
use Transfer\Processor\SequentialProcessor;

class YamlToEzPlatformContentTypeTestCase extends EzPlatformTestCase
{

    /**
     * @covers YamlToEzPlatformContentTypeManifest
     */
    public function testManifestRunsToItsEnd()
    {
        $completed = false;
        $manifest = new YamlToEzPlatformContentTypeManifest(static::$repository);

        $this->assertInstanceOf('Transfer\Processor\EventDrivenProcessor', $manifest->getProcessor());
        $manifest->getProcessor()->addListener(TransferEvents::POST_PROCEDURE, function() use (&$completed) {
            $completed = true;
        });
        $manifest->configureProcessor($manifest->getProcessor());

        $runner = new ManifestRunner($manifest);
        $runner->run($manifest);

        $this->assertTrue($completed);
    }
}
