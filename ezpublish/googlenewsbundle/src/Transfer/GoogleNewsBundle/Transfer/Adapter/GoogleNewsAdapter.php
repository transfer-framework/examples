<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\GoogleNewsBundle\Transfer\Adapter;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Transfer\Adapter\SourceAdapterInterface;
use Transfer\Adapter\Transaction\Request;
use Transfer\Adapter\Transaction\Response;

class GoogleNewsAdapter implements SourceAdapterInterface, LoggerAwareInterface
{
    /**
     * @var LoggerInterface Logger
     */
    protected $logger;

    /**
     * @var array Options
     */
    protected $options;

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
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
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return mixed
     */
    public function getGoogleNewsXml()
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->options['url'],
        ));

        return curl_exec($curl);
    }

    /**
     * {@inheritdoc}
     */
    public function receive(Request $request)
    {
        $response = new Response();

        $data = $this->getGoogleNewsXml();

        $response->setData(new \ArrayIterator(array($data)));

        return $response;
    }
}
