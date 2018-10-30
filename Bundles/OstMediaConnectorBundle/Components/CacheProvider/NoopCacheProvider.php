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

use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaCache\Structs\CachedMedia;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaCache\Structs\MediaToken;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaProviderListCache\Structs\CachedMediaProviderList;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\ResourceCache\Structs\ResourceToken;

class NoopCacheProvider implements CacheProvider
{
    public function getName(): string
    {
        return 'NoopCacheProvider';
    }



    public function hasMediaAssociation(string $ordernumber, int $imageNumber): bool
    {
        return false;
    }



    public function getMediaAssociation(string $ordernumber, int $imageNumber): string
    {
        return '';
    }



    public function storeMediaAssociation(string $ordernumber, int $imageNumber, string $path): bool
    {
        return false;
    }



    public function hasMedia(MediaToken $mediaToken): bool
    {
        return false;
    }



    public function storeMedia(CachedMedia $cachedMedia): bool
    {
        return false;
    }



    public function getMedia(MediaToken $mediaToken): CachedMedia
    {
        return null;
    }



    public function getAllMediaTokens(): array
    {
        return [];
    }



    public function deleteMedia(MediaToken $mediaToken): bool
    {
        return false;
    }



    public function hasMediaProviderList(string $ordernumber): bool
    {
        return false;
    }



    public function getMediaProviderList(string $ordernumber): array
    {
        return [];
    }



    public function storeMediaProviderList(string $ordernumber, CachedMediaProviderList $mediaProviderList): bool
    {
        return false;
    }



    public function hasResource(ResourceToken $resourceToken): bool
    {
        return false;
    }



    public function storeResource(ResourceToken $resourceToken, $resource): bool
    {
        return false;
    }



    public function getResource(ResourceToken $resourceToken)
    {
        return null;
    }



    public function getAllResourceTokens(): array
    {
        return [];
    }



    public function deleteResource(ResourceToken $resourceToken): bool
    {
        return false;
    }
}
