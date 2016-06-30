<?php

namespace Transfer\GoogleNewsBundle\Transfer\Manifest;

use eZ\Publish\API\Repository\Repository;
use Transfer\Commons\Xml\Worker\Transformer\StringToSimpleXmlTransformer;
use Transfer\EzPlatform\Repository\Values\ContentObject;
use Transfer\EzPlatform\Repository\Values\ContentTypeObject;
use Transfer\GoogleNewsBundle\Transfer\Adapter\GoogleNewsAdapter;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Transfer\Adapter\LocalDirectoryAdapter;
use Transfer\Commons\Yaml\Worker\Transformer\YamlToArrayTransformer;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\GoogleNewsBundle\Transfer\Worker\SimpleXmlToArrayTransformer;
use Transfer\Manifest\ManifestInterface;
use Transfer\Procedure\ProcedureBuilder;
use Transfer\Processor\EventDrivenProcessor;
use Transfer\Processor\ProcessorInterface;
use Transfer\Processor\SequentialProcessor;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Transfer\GoogleNewsBundle\Transfer\Worker\GoogleNewsToContentTransformer;

class GoogleNewsToEzPlatformContentManifest implements ManifestInterface
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

    /**
     * @var array Options
     */
    protected $options;

    public function __construct(Repository $repository, array $options)
    {
        $this->repository = $repository;
        $this->processor = new SequentialProcessor();

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    /**
     * Option configuration.
     *
     * @param OptionsResolver $resolver
     */
    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('url', 'https://news.google.com/news?cf=all&hl=en&ned=us&topic=t&output=rss');
        $resolver->setAllowedTypes('url', array('string'));

        $resolver->setRequired(array('location_id'));
        $resolver->setAllowedTypes('location_id', array('int'));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'googlenews_to_ezplatform_content';
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

                // Create or update our ContentType
                ->createProcedure('google_news_contenttype')

                    ->addSource(new LocalDirectoryAdapter(array('directory' => __DIR__.'/../../Resources/contenttypes')))

                        ->addWorker(function (ValueObject $object) { return $object->data; })
                        ->addWorker(new YamlToArrayTransformer())
                        ->split()
                        ->addWorker(function ($data) {
                            return new ContentTypeObject($data);
                        })

                    ->addTarget(new EzPlatformAdapter($this->repository))

                ->end()

                // Create or update our Content
                ->createProcedure('google_news_content')

                    // The source of our content, an adapter which fetches data from Google News
                    ->addSource(new GoogleNewsAdapter(array('url' => $this->options['url'])))

                        // Worker which transforms xml string into simplexml
                        ->addWorker(new StringToSimpleXmlTransformer())

                        // Transforms SimpleXml into array
                        ->addWorker(new SimpleXmlToArrayTransformer())

                        // Transforms our data into eZPlatform ContentObject
                        ->addWorker(new GoogleNewsToContentTransformer())

                        // Split the collection into individual elemnts
                        ->split()

                        // Creating a mini-worker to add our location id
                        ->addWorker(function ($data) {
                            /* @var $data ContentObject */
                            $data->addParentLocation($this->options['location_id']);

                            return $data;
                        })

                    // Pass the ContentObject to EzPlatformAdapter to be stored in eZ Platform/eZ Studio
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
        $logger->pushHandler(new StreamHandler(sprintf('%s/%s.log', __DIR__.'/../../../../app/logs/transfer/contenttype', date('Y-m-d')), Logger::DEBUG));
        if ($processor instanceof EventDrivenProcessor) {
            $processor->setLogger($logger);
        }
    }
}
