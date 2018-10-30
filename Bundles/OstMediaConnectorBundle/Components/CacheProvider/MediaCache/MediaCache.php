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

namespace OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaCache;

use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaCache\Structs\CachedMedia;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaCache\Structs\MediaToken;

interface MediaCache
{
    /**
     * @param MediaToken $mediaToken
     *
     * @return bool
     */
    public function hasMedia(MediaToken $mediaToken): bool;



    /**
     * @param CachedMedia $cachedMedia
     *
     * @return bool
     */
    public function storeMedia(CachedMedia $cachedMedia): bool;



    /**
     * @param MediaToken $mediaToken
     *
     * @throws MediaNotFoundException
     *
     * @return CachedMedia
     */
    public function getMedia(MediaToken $mediaToken): CachedMedia;



    /**
     * @return MediaToken[]
     */
    public function getAllMediaTokens(): array;



    /**
     * @param MediaToken $mediaToken
     *
     * @return bool
     */
    public function deleteMedia(MediaToken $mediaToken): bool;
}
