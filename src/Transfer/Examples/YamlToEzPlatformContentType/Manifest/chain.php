<?php

namespace Transfer\Examples\YamlToEzPlatformContentType\Manifest;

use Transfer\Manifest\ManifestChain;

require_once 'vendor/autoload.php';

$chain = new ManifestChain();
$chain->addManifest(new YamlToEzPlatformContentTypeManifest());

return $chain;