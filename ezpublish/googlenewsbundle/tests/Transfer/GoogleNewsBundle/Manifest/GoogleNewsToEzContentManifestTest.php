<?php

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy\SetupFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Transfer\Event\TransferEvents;
use Transfer\GoogleNewsBundle\Transfer\Manifest\GoogleNewsToEzPlatformContentManifest;
use Transfer\Manifest\ManifestRunner;
use Transfer\Processor\EventDrivenProcessor;

class GoogleNewsToEzContentManifestTest extends KernelTestCase
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

    public function testManifestRun()
    {
        // design/plainsite location id
        $parentLocationId = 56;
        $completed = false;

        $location = static::$repository->getLocationService()->loadLocation($parentLocationId);
        $children = static::$repository->getLocationService()->loadLocationChildren($location);
        $this->assertLessThan(4, $children->totalCount);

        $manifest = new GoogleNewsToEzPlatformContentManifest(static::$repository, array(
            'url' => 'https://news.google.com/news?cf=all&hl=en&ned=us&topic=t&output=rss',
            'location_id' => $parentLocationId,
        ));

        $this->assertInstanceOf(EventDrivenProcessor::class, $manifest->getProcessor());
        $manifest->getProcessor()->addListener(TransferEvents::POST_PROCEDURE, function () use (&$completed) {
            $completed = true;
        });

        $manifest->configureProcessor($manifest->getProcessor());

        $runner = new ManifestRunner($manifest);
        $runner->run($manifest);
        $this->assertTrue($completed);

        $this->assertEquals('googlenews_to_ezplatform_content', $manifest->getName());

        $location = static::$repository->getLocationService()->loadLocation($parentLocationId);
        $children = static::$repository->getLocationService()->loadLocationChildren($location);
        $this->assertGreaterThan(4, $children->totalCount);
    }
}
