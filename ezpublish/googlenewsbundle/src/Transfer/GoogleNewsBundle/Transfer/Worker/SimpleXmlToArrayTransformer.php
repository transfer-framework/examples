<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\GoogleNewsBundle\Transfer\Worker;

use Transfer\Worker\WorkerInterface;

class SimpleXmlToArrayTransformer implements WorkerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle($simpleXmlElement)
    {
        return json_decode(json_encode((array) $simpleXmlElement), 1);
    }
}
