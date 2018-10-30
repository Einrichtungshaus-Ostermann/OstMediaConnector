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

namespace OstMediaConnector\Bundles\OstMediaConnectorBundle\Services;

use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\CacheProvider;

class CacheProviderSelector
{
    /**
     * @var array|CacheProvider[]
     */
    private $cacheProviders;



    /**
     * @var array
     */
    private $config;



    /**
     * CacheProviderSelector constructor.
     *
     * @param CacheProvider[] $cacheProviders
     * @param array $config
     */
    public function __construct(array $cacheProviders, array $config)
    {
        $this->cacheProviders = $cacheProviders;
        $this->config = $config;
    }



    public function getCacheProvider()
    {
        foreach ($this->cacheProviders as $cacheProvider) {
            if ($cacheProvider->getName() === $this->config['cacheProvider']) {
                return $cacheProvider;
            }
        }

        throw new \RuntimeException('Could not find a suitable CacheProvider');
    }
}
