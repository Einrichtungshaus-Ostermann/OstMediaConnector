<?php declare(strict_types=1);

namespace OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaProviderListCache;

class MediaProviderListNotFoundException extends \Exception
{
    public function __construct()
    {
        parent::__construct();
    }
}
