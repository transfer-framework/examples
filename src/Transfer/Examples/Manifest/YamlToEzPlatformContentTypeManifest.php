<?php

namespace Transfer\Examples\Manifest;

use Transfer\Commons\Stream\Adapter\StreamAdapter;
use Transfer\Commons\Yaml\Worker\Transformer\YamlToArrayTransformer;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\Manifest\AbstractManifest;
use Transfer\Manifest\ManifestInterface;
use Transfer\Procedure\ProcedureBuilder;
use Transfer\Processor\EventSubscriber\Logger;
use Transfer\Processor\ProcessorInterface;

class YamlToEzPlatformContentTypeManifest extends AbstractManifest
{

    /**
     * Returns manifest name.
     *
     * @return string Manifest name
     */
    public function getName()
    {
        return 'yaml_to_ezplatform_contenttype';
    }

    /**
     * Configures procedure builder.
     *
     * @param ProcedureBuilder $builder Procedure builder
     */
    public function configureProcedureBuilder(ProcedureBuilder $builder)
    {
        $builder
            ->createProcedure('import')
                ->createProcedure('contenttype')
                    ->addSource(new StreamAdapter(fopen(__DIR__.'/resources/yaml/frontpage.yml')))
                        ->addWorker(new YamlToArrayTransformer())
                    ->addTarget(new EzPlatformAdapter())
                ->end()
            ->end()
        ;
    }

    /**
     * Configures processor.
     *
     * @param ProcessorInterface $processor Processor
     */
    public function configureProcessor(ProcessorInterface $processor)
    {
        $logger = new Logger('default');
        $logger->pushHandler(new StreamHandler(sprintf('%s/%s.log', 'var/log/transfer/contenttype', date('Y-m-d')), Logger::DEBUG));

        if ($processor instanceof EventDrivenProcessor) {
            $processor->setLogger($logger);
            $processor->setStorageStack($this->stack);

            \Mage::register('isSecureArea', true);
            $processor->addListener(TransferEvents::POST_PROCESS, array(new ModelRemover($this->tracker), 'deactivate'));
        }
    }
}