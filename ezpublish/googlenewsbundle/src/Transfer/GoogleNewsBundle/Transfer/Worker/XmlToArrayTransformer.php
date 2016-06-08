<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\GoogleNewsBundle\Transfer\Worker;

use Transfer\Worker\WorkerInterface;

class XmlToArrayTransformer implements WorkerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle($xml_string)
    {
        return json_decode(json_encode(
            simplexml_load_string($xml_string)
        ), true);
    }
}
