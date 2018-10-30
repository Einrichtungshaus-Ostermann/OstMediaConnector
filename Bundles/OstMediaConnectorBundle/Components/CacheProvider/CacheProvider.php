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

namespace OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider;

use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaAssociationCache\MediaAssociationCache;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaCache\MediaCache;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaProviderListCache\MediaProviderListCache;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\ResourceCache\ResourceCache;

interface CacheProvider extends MediaCache, ResourceCache, MediaProviderListCache, MediaAssociationCache
{
    public function getName(): string;
}
