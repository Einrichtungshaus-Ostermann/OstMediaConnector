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

use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaAssociationCache\MediaAssociationNotFoundException;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaCache\MediaNotFoundException;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaCache\Structs\CachedMedia;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaCache\Structs\MediaToken;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaProviderListCache\MediaProviderListNotFoundException;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaProviderListCache\Structs\CachedMediaProviderList;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\ResourceCache\ResourceNotFoundException;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\ResourceCache\Structs\ResourceToken;

class RedisCacheProvider implements CacheProvider
{
    /** @var \Redis $redis */
    protected $redis;

    /** @var int */
    protected $mediaCacheTTL;

    /** @var int */
    protected $resourceCacheTTL;

    /** @var int */
    protected $mediaProviderListCacheTTL;

    /** @var int */
    protected $mediaAssociationTTL;

    /** @var string */
    protected $prefix = 'MEDIASERVER:';

    /**
     * RedisCacheProvider constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->mediaCacheTTL = (int)$config['mediaCacheTTL'];
        $this->resourceCacheTTL = (int)$config['resourceCacheTTL'];
        $this->mediaProviderListCacheTTL = (int)$config['mediaProviderListCacheTTL'];
        $this->mediaAssociationTTL = (int)$config['mediaAssociationTTL'];
    }

    public function getName(): string
    {
        return 'RedisCacheProvider';
    }



    protected function initRedis()
    {
        if ($this->redis !== null) {
            return;
        }

        $this->redis = new \Redis();
        $this->redis->connect('localhost');
    }



    /**
     * @param MediaToken $resourceToken
     *
     * @return bool
     */
    public function hasMedia(MediaToken $resourceToken): bool
    {
        $this->initRedis();

        $key = $this->getMediaKeyByToken($resourceToken);

        return $this->redis->exists($key);
    }

    /**
     * @param CachedMedia $cachedMedia
     *
     * @return bool
     */
    public function storeMedia(CachedMedia $cachedMedia): bool
    {
        $this->initRedis();

        $key = $this->getMediaKeyByToken($cachedMedia->getMediaToken());

        $return = $this->redis->set($key, serialize($cachedMedia));

        if ($return !== true) {
            return false;
        }

        $this->redis->setTimeout($key, $this->mediaCacheTTL);

        return true;
    }

    /**
     * @param MediaToken $mediaToken
     *
     * @throws MediaNotFoundException
     *
     * @return CachedMedia
     */
    public function getMedia(MediaToken $mediaToken): CachedMedia
    {
        $this->initRedis();

        $key = $this->getMediaKeyByToken($mediaToken);

        /** @var string|false $serializedMedia */
        $serializedMedia = $this->redis->get($key);

        if ($serializedMedia === false) {
            throw new MediaNotFoundException();
        }

        return unserialize($serializedMedia, ['allowed_classes' => CachedMedia::class, ResourceToken::class]);
    }



    /**
     * @return MediaToken[]
     * @throws Token\InvalidTokenException
     */
    public function getAllMediaTokens(): array
    {
        $this->initRedis();

        $pattern = $this->getMediaKey('*');

        $allKeys = $this->getAllKeysForPattern($pattern);

        $mediaTokens = [];
        foreach ($allKeys as $key) {
            $mediaTokens[] = MediaToken::fromToken($key);
        }

        return $mediaTokens;
    }

    /**
     * @param MediaToken $mediaToken
     *
     * @return bool
     */
    public function deleteMedia(MediaToken $mediaToken): bool
    {
        $this->initRedis();

        $key = $this->getMediaKey($mediaToken->getToken());

        return $this->redis->delete($key) === 1;
    }

    /**
     * @param ResourceToken $resourceToken
     *
     * @return bool
     */
    public function hasResource(ResourceToken $resourceToken): bool
    {
        $this->initRedis();

        $key = $this->getResourceKeyByToken($resourceToken);

        return $this->redis->exists($key);
    }

    /**
     * @param ResourceToken $resourceToken
     * @param $resource
     *
     * @return bool
     */
    public function storeResource(ResourceToken $resourceToken, $resource): bool
    {
        $this->initRedis();

        $key = $this->getResourceKeyByToken($resourceToken);

        ob_start();
        imagejpeg($resource);
        $contents = ob_get_contents();
        ob_end_clean();

        $return = $this->redis->set($key, base64_encode($contents));

        if ($return !== true) {
            return false;
        }

        $this->redis->setTimeout($key, $this->resourceCacheTTL);

        return true;
    }

    /**
     * @param ResourceToken $resourceToken
     *
     * @throws ResourceNotFoundException
     *
     * @return resource
     */
    public function getResource(ResourceToken $resourceToken)
    {
        $this->initRedis();

        $key = $this->getResourceKeyByToken($resourceToken);

        /** @var string|false $resource */
        $base64Resource = $this->redis->get($key);

        if ($base64Resource === false) {
            throw new ResourceNotFoundException();
        }

        return imagecreatefromstring(base64_decode($base64Resource));
    }



    /**
     * @return ResourceToken[]
     * @throws Token\InvalidTokenException
     */
    public function getAllResourceTokens(): array
    {
        $this->initRedis();

        $pattern = $this->getResourceKey('*');

        $allKeys = $this->getAllKeysForPattern($pattern);

        $resourceTokens = [];
        foreach ($allKeys as $key) {
            $resourceTokens[] = ResourceToken::fromToken($key);
        }

        return $resourceTokens;
    }

    /**
     * @param ResourceToken $resourceToken
     *
     * @return bool
     */
    public function deleteResource(ResourceToken $resourceToken): bool
    {
        $this->initRedis();

        $key = $this->getResourceKeyByToken($resourceToken);

        return $this->redis->delete($key) === 1;
    }

    public function hasMediaProviderList(string $ordernumber): bool
    {
        $this->initRedis();

        $key = $this->getMediaProviderListKey($ordernumber);

        return $this->redis->exists($key);
    }

    /**
     * @param string $ordernumber
     *
     * @throws MediaProviderListNotFoundException
     *
     * @return array
     */
    public function getMediaProviderList(string $ordernumber): array
    {
        $this->initRedis();

        $key = $this->getMediaProviderListKey($ordernumber);

        /** @var string|false $config */
        $config = $this->redis->get($key);

        if ($config === false) {
            throw new MediaProviderListNotFoundException();
        }

        return json_decode($config, true);
    }

    public function storeMediaProviderList(string $ordernumber, CachedMediaProviderList $mediaProviderList): bool
    {
        $this->initRedis();

        $key = $this->getMediaProviderListKey($ordernumber);

        $return = $this->redis->set($key, json_encode($mediaProviderList->getCache()));

        if ($return !== true) {
            return false;
        }

        $this->redis->setTimeout($key, $this->mediaProviderListCacheTTL);

        return true;
    }

    public function hasMediaAssociation(string $ordernumber, int $imageNumber): bool
    {
        $this->initRedis();

        $key = $this->getMediaAssociationKey($ordernumber, $imageNumber);

        return $this->redis->exists($key);
    }

    public function getMediaAssociation(string $ordernumber, int $imageNumber): string
    {
        $this->initRedis();

        $key = $this->getMediaAssociationKey($ordernumber, $imageNumber);

        /** @var string|false $path */
        $path = $this->redis->get($key);

        if ($path === false) {
            throw new MediaAssociationNotFoundException();
        }

        return $path;
    }

    public function storeMediaAssociation(string $ordernumber, int $imageNumber, string $path): bool
    {
        $this->initRedis();

        $key = $this->getMediaAssociationKey($ordernumber, $imageNumber);

        $return = $this->redis->set($key, $path);

        if ($return !== true) {
            return false;
        }

        $this->redis->setTimeout($key, $this->mediaAssociationTTL);

        return true;
    }



    /**
     * @param MediaToken $mediaToken
     *
     * @return string
     */
    private function getMediaKeyByToken(MediaToken $mediaToken): string
    {
        return $this->getMediaKey($mediaToken->getToken());
    }



    private function getMediaKey(string $token): string
    {
        return $this->prefix . 'media_' . $token;
    }



    /**
     * @param ResourceToken $resourceToken
     *
     * @return string
     */
    private function getResourceKeyByToken(ResourceToken $resourceToken): string
    {
        return $this->getResourceKey($resourceToken->getToken());
    }



    private function getResourceKey(string $token): string
    {
        return $this->prefix . 'resource_' . $token;
    }



    private function getMediaProviderListKey(string $ordernumber): string
    {
        return $this->prefix . 'mediaProviderList_' . $ordernumber;
    }



    private function getMediaAssociationKey(string $ordernumber, int $imageNumber): string
    {
        return $this->prefix . 'mediaAssociation_' . $ordernumber . '_' . $imageNumber;
    }

    /**
     * @param string $pattern
     *
     * @return array
     */
    private function getAllKeysForPattern(string $pattern): array
    {
        $this->redis->setOption(\Redis::OPT_SCAN, \Redis::SCAN_RETRY);
        $it = null;
        $keys = [];

        /* phpredis will retry the SCAN command if empty results are returned from the
           server, so no empty results check is required. */
        while ($arr_keys = $this->redis->scan($it, $pattern)) {
            foreach ($arr_keys as $str_key) {
                $keys[] = $str_key;
            }
        }

        return $keys;
    }
}
