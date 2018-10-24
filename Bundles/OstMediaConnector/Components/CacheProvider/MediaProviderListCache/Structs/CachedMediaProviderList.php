<?php declare(strict_types=1);
/**
 * Einrichtungshaus Ostermann GmbH & Co. KG - Media Connector
 *
 * Import Images from external Servers
 *
 * 1.0.0
 * - initial release
 *
 * @package   OstMediaConnector
 *
 * @author    Tim Windelschmidt <tim.windelschmidt@ostermann.de>
 * @copyright 2018 Einrichtungshaus Ostermann GmbH & Co. KG
 * @license   proprietary
 */

namespace OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaProviderListCache\Structs;

use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\MediaProvider\MediaProvider;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\MediaProvider\MediaProviderListProvider;

class CachedMediaProviderList extends MediaProviderListProvider
{
    /** @var array|null */
    private $cache;

    public function __construct(MediaProviderListProvider $mediaProviderListProvider, $cache)
    {
        parent::__construct($mediaProviderListProvider->getMediaProviderList());
        $this->cache = $cache;
    }

    public function getConfiguredProviderList(string $ordernumber): array
    {
        $parent = null;
        if ($this->cache === null) {
            /** @var array $parent */
            $parent = parent::getConfiguredProviderList($ordernumber);

            foreach ($parent as $mediaProvider) {
                $this->cache[$mediaProvider->getName()] = $mediaProvider->getConfig();
            }
        }

        if ($parent !== null) {
            return $parent;
        }

        if ($this->cache === null) {
            return [];
        }

        $providerList = $this->getMediaProviderList();
        $returnData = [];
        foreach ($this->cache as $name => $config) {
            /** @var MediaProvider $provider */
            $provider = clone $providerList[$name];

            $provider->setConfig($config);

            $returnData[] = $provider;
        }

        return $returnData;
    }

    /**
     * @return array
     */
    public function getCache(): array
    {
        return $this->cache ?? [];
    }
}
