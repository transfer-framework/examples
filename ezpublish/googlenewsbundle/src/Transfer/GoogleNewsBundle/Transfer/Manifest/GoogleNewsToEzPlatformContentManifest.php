<?php

namespace Transfer\GoogleNewsBundle\Transfer\Manifest;

use eZ\Publish\API\Repository\Repository;
use Transfer\GoogleNewsBundle\Transfer\Adapter\GoogleNewsAdapter;
use Transfer\GoogleNewsBundle\Transfer\Worker\XmlToArrayTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Transfer\Adapter\LocalDirectoryAdapter;
use Transfer\Commons\Yaml\Worker\Transformer\YamlToArrayTransformer;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Worker\Transformer\ArrayToEzPlatformContentTypeObjectTransformer;
use Transfer\Manifest\ManifestInterface;
use Transfer\Procedure\ProcedureBuilder;
use Transfer\Processor\EventDrivenProcessor;
use Transfer\Processor\ProcessorInterface;
use Transfer\Processor\SequentialProcessor;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Transfer\Worker\SplitterWorker;
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
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(array('url'));
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
                        ->addWorker(new ArrayToEzPlatformContentTypeObjectTransformer())
                        ->addWorker(new SplitterWorker())

            ->addWorker(function (ValueObject $object) {
                print_r($object->data['fields']['link']->data['default_value']);
                $object->data['fields']['link']->data['default_value'] = null;
                return $object;
            })

                    ->addTarget(new EzPlatformAdapter(array('repository' => $this->repository)))

                ->end()

                // Create or update our Content
                ->createProcedure('google_news_content')

                    ->addSource(new GoogleNewsAdapter(array('url' => $this->options['url'])))

                        ->addWorker(new XmlToArrayTransformer())
                        ->addWorker(new GoogleNewsToContentTransformer())
                        ->addWorker(new SplitterWorker())

                        ->addWorker(function ($data) {
                            /* @var $data ValueObject */
                            $data->setProperty('main_location_id', array(
                                'destination_location_id' => $this->options['location_id'],
                            ));
                            $data->setProperty('main_language_code', 'eng-GB');

                            return $data;
                        })

                    ->addTarget(new EzPlatformAdapter(array('repository' => $this->repository)))
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
