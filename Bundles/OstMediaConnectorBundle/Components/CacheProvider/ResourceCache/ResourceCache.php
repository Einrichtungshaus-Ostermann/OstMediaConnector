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

namespace OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\ResourceCache;

use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\ResourceCache\Structs\ResourceToken;

interface ResourceCache
{
    /**
     * @param ResourceToken $resourceToken
     *
     * @return bool
     */
    public function hasResource(ResourceToken $resourceToken): bool;

    /**
     * @param ResourceToken $resourceToken
     * @param $resource
     *
     * @return bool
     */
    public function storeResource(ResourceToken $resourceToken, $resource): bool;

    /**
     * @param ResourceToken $resourceToken
     *
     * @throws ResourceNotFoundException
     *
     * @return resource
     */
    public function getResource(ResourceToken $resourceToken);

    /**
     * @return ResourceToken[]
     */
    public function getAllResourceTokens(): array;

    /**
     * @param ResourceToken $resourceToken
     *
     * @return bool
     */
    public function deleteResource(ResourceToken $resourceToken): bool;
}
