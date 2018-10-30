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

namespace OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaProviderListCache;

use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaProviderListCache\Structs\CachedMediaProviderList;

/**
 * Interface MediaProviderListCache
 *
 * @package OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaProviderListCache
 */
interface MediaProviderListCache
{
    /**
     * @param string $ordernumber
     *
     * @return bool
     */
    public function hasMediaProviderList(string $ordernumber): bool;



    /**
     * @param string $ordernumber
     *
     * @throws MediaProviderListNotFoundException
     *
     * @return array
     */
    public function getMediaProviderList(string $ordernumber): array;



    /**
     * @param string $ordernumber
     * @param CachedMediaProviderList $mediaProviderList
     *
     * @return bool
     */
    public function storeMediaProviderList(string $ordernumber, CachedMediaProviderList $mediaProviderList): bool;
}
