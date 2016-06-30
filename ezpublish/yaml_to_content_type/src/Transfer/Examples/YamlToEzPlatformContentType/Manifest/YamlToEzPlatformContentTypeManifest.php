<?php

namespace Transfer\Examples\YamlToEzPlatformContentType\Manifest;

use eZ\Publish\API\Repository\Repository;
use Transfer\Adapter\LocalDirectoryAdapter;
use Transfer\Commons\Yaml\Worker\Transformer\YamlToArrayTransformer;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Repository\Values\ContentTypeObject;
use Transfer\Manifest\ManifestInterface;
use Transfer\Procedure\ProcedureBuilder;
use Transfer\Processor\EventDrivenProcessor;
use Transfer\Processor\ProcessorInterface;
use Transfer\Processor\SequentialProcessor;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class YamlToEzPlatformContentTypeManifest implements ManifestInterface
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var SequentialProcessor
     */
    protected $processor;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->processor = new SequentialProcessor();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'yaml_to_ezplatform_contenttype';
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * {@inheritdoc}
     */
    public function configureProcedureBuilder(ProcedureBuilder $builder)
    {
        $builder
            ->createProcedure('import')
                ->createProcedure('contenttype')
                    ->addSource(new LocalDirectoryAdapter(array('directory' => __DIR__.'/../resources/yaml')))
                        ->addWorker(function (ValueObject $object) { return $object->data; })
                        ->addWorker(new YamlToArrayTransformer())
                        ->split()
                        ->addWorker(function ($data) {
                            return new ContentTypeObject($data);
                        })
                    ->addTarget(new EzPlatformAdapter($this->repository))
                ->end()
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureProcessor(ProcessorInterface $processor)
    {
        $logger = new Logger('default');
        $logger->pushHandler(new StreamHandler(sprintf('%s/%s.log', '../../app/logs/transfer/contenttype', date('Y-m-d')), Logger::DEBUG));
        if ($processor instanceof EventDrivenProcessor) {
            $processor->setLogger($logger);
        }
    }
}
