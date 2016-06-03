<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\Examples\Tests\YamlToEzPlatformContentType\Manifest;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy\SetupFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Transfer\Event\TransferEvents;
use Transfer\Examples\YamlToEzPlatformContentType\Manifest\YamlToEzPlatformContentTypeManifest;
use Transfer\Manifest\ManifestRunner;
use Transfer\Processor\EventDrivenProcessor;

class YamlToEzPlatformContentTypeManifestTest extends KernelTestCase
{
    /**
     * @var Repository
     */
    protected static $repository;

    public function setUp()
    {
        parent::setUp();
        $setupFactory = new SetupFactory();
        static::$repository = $setupFactory->getRepository();
    }

    public function testManifestRunsToItsEnd()
    {
        $completed = false;
        $manifest = new YamlToEzPlatformContentTypeManifest(static::$repository);

        $this->assertInstanceOf(EventDrivenProcessor::class, $manifest->getProcessor());
        $manifest->getProcessor()->addListener(TransferEvents::POST_PROCEDURE, function () use (&$completed) {
            $completed = true;
        });
        $manifest->configureProcessor($manifest->getProcessor());

        $runner = new ManifestRunner($manifest);
        $runner->run($manifest);

        $this->assertTrue($completed);

        $this->assertEquals('yaml_to_ezplatform_contenttype', $manifest->getName());
    }
}
