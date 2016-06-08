<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\GoogleNewsBundle\Transfer\Worker;

use Transfer\EzPlatform\Repository\Values\ContentObject;
use Transfer\Worker\WorkerInterface;

class GoogleNewsToContentTransformer implements WorkerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle($arrays)
    {
        $feeds = array();

        foreach ($arrays['channel']['item'] as $feed) {
            $feeds[] = new ContentObject(
                array(
                    'title' => $feed['title'],
                    'link' => $feed['link'],
                    'category' => $feed['category'],
                    'publish_date' => $feed['pubDate'],
                ),
                array(
                    // 'main_location_id' => int, // We will append this in our manifest.
                    'language' => 'eng-GB',
                    'content_type_identifier' => 'google_news',
                    'remote_id' => 'google_news_'.strtolower(preg_replace('/[^A-Za-z0-9]/', '', $feed['title'])),
                )
            );
        }

        return $feeds;
    }
}
