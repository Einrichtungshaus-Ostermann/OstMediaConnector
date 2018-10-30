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

use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaCache\Structs\CachedMedia;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaProviderListCache\Structs\CachedMediaProviderList;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\ResourceCache\Structs\ResourceToken;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\Token\InvalidTokenException;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\MediaProvider\MediaProvider;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\MediaProvider\MediaProviderListProvider;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\Worker\MediaWorker;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Services\Structs\Media;
use Shopware\Bundle\StoreFrontBundle\Struct\Thumbnail;
use Shopware\Components\Routing\Router;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use Shopware\Models\Media\Album;

class LiveImageService implements MediaService
{
    /**
     * @var MediaWorker
     */
    private $mediaWorker;



    /**
     * @var MediaProviderListProvider
     */
    private $mediaProviderListProvider;



    /**
     * @var Router
     */
    private $router;



    /**
     * ImageService constructor.
     *
     * @param MediaWorker $mediaWorker
     * @param MediaProviderListProvider $mediaProviderListProvider
     * @param Router $router
     */
    public function __construct(MediaWorker $mediaWorker, MediaProviderListProvider $mediaProviderListProvider, Router $router)
    {
        $this->mediaWorker = $mediaWorker;
        $this->mediaProviderListProvider = $mediaProviderListProvider;
        $this->router = $router;
    }



    /**
     * @param string $token
     *
     * @throws InvalidTokenException
     *
     * @return resource|null
     */
    public function getResource(string $token)
    {
        $resourceToken = ResourceToken::fromToken($token);

        $image = $this->mediaWorker->getResized($resourceToken->getFileName(), $resourceToken->getWidth());

        return $image;
    }



    /**
     * @param string $ordernumber
     *
     * @return array
     */
    public function getAll(string $ordernumber): array
    {
        $mediaArray = [];
        $amount = $this->count($ordernumber);
        for ($i = 0; $i < $amount; ++$i) {
            $mediaArray[] = $this->getMedia($ordernumber, $i);
        }

        return $mediaArray;
    }



    /**
     * @param string $ordernumber
     *
     * @return int
     */
    public function count(string $ordernumber): int
    {
        /** @var MediaProvider[] $mediaProviderList */
        $mediaProviderList = $this->mediaProviderListProvider->getConfiguredProviderList($ordernumber);

        $count = 0;
        if (\count($mediaProviderList) !== 0) {
            foreach ($mediaProviderList as $mediaProvider) {
                $count += $mediaProvider->count($ordernumber);
            }
        }

        return $count;
    }



    /**
     * Returns a Media for an Ordernumber
     *
     * @param string $ordernumber
     * @param int $imageNumber
     *
     * @return Media|null
     */
    public function getMedia(string $ordernumber, int $imageNumber = 0)
    {
        $this->mediaProviderListProvider = new CachedMediaProviderList($this->mediaProviderListProvider, null);

        /** @var MediaProvider[] $mediaProviderList */
        $mediaProviderList = $this->mediaProviderListProvider->getConfiguredProviderList($ordernumber);

        if (\count($mediaProviderList) === 0) {
            return null;
        }

        $imagePath = null;

        if ($imagePath === null) {
            if ($imageNumber === 0) {
                $imagePath = $mediaProviderList[0]->get($ordernumber, $imageNumber);
            } else {
                $currentProviderCount = 0;
                $providerCount = [];
                foreach ($mediaProviderList as $mediaProvider) {
                    $providerCount[$currentProviderCount] = $mediaProvider;
                    $currentProviderCount += $mediaProvider->count($ordernumber);
                }

                $iMax = \count($providerCount);
                $providerCountKeys = array_keys($providerCount);
                for ($i = 0; $i < $iMax; ++$i) {
                    $currentProvider = $providerCount[$providerCountKeys[$i]];
                    $previousCount = $i === 0 ? 0 : $providerCountKeys[$i];
                    $currentImageNumber = $imageNumber - $previousCount;

                    if ($currentImageNumber < 0) {
                        break;
                    }

                    if ($currentImageNumber < $currentProvider->count($ordernumber)) {
                        $imagePath = $currentProvider->get($ordernumber, $currentImageNumber);
                        break;
                    }
                }
            }

            if ($imagePath === null) {
                return null;
            }
        }

        if ($imagePath === null) {
            return null;
        }

        list($originalWidth, $originalHeight) = $this->mediaWorker->getResolution($imagePath);

        /** @var Album $articleAlbum */
        $articleAlbum = Shopware()->Models()->getRepository(Album::class)->findOneBy(['id' => Album::ALBUM_ARTICLE]);
        $albumSettings = $articleAlbum->getSettings();

        if ($albumSettings === null) {
            $thumbnailSizes = [];
        } else {
            $thumbnailSizes = $albumSettings->getThumbnailSize();
        }


        $thumbnails = [];
        foreach ($thumbnailSizes as $thumbnailSize) {
            $size = explode('x', $thumbnailSize);

            $thumbnails[] = new ResourceToken($imagePath, (int)$size[0], (int)$size[1]);
        }

        $seoName = null;
        $detail = Shopware()->Models()->getRepository(Detail::class)->findOneBy(['number' => $ordernumber]);
        if ($detail !== null) {
            /** @var Article $article */
            $article = $detail->getArticle();

            if ($article !== null) {
                $articleName = str_replace([' '], ['_'], $article->getName());
                $supplierName = $article->getSupplier()->getName();
                $model = $detail->getSupplierNumber();

                $seoName = urlencode($ordernumber . '-' . $articleName . '-' . $supplierName . '-' . $model);
            }
        }

        $fileSize = $this->mediaWorker->getFileSize($imagePath);

        $media = new CachedMedia($ordernumber, $imagePath, $originalWidth, $originalHeight, $fileSize, $thumbnails, $seoName);

        return $this->getMediaFromCachedMedia($media);
    }



    public function getAllMediaProvider(): array
    {
        return $this->mediaProviderListProvider->getMediaProviderList();
    }



    private function getMediaFromCachedMedia(CachedMedia $cachedMedia)
    {
        $thumbnails = [];

        foreach ($cachedMedia->getResourceTokens() as $resourceToken) {
            $thumbnails[] = new Thumbnail(
                $this->createImageUrl($resourceToken->getToken(), $cachedMedia),
                $this->createImageUrl($resourceToken->getRetinaToken(), $cachedMedia),
                $resourceToken->getWidth(),
                $resourceToken->getHeight()
            );
        }

        return new Media($cachedMedia->getFile(), $cachedMedia->getHeight(), $cachedMedia->getWidth(), $cachedMedia->getFileSize(), $thumbnails);
    }



    private function createImageUrl(string $token, CachedMedia $cachedMedia): string
    {
        return $this->router->assemble(['controller' => 'OstMediaConnector', 'action' => 'image',
                                        $token       => $cachedMedia->getSeoName()]);
    }
}
