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

namespace OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\MediaProvider;

use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Image;

class ShopwareProvider implements MediaProvider
{
    /**
     * @var MediaServiceInterface
     */
    private $mediaService;



    /**
     * @var ModelManager
     */
    private $modelManager;



    /**
     * ShopwareProvider constructor.
     *
     * @param MediaServiceInterface $mediaService
     * @param ModelManager $modelManager
     */
    public function __construct(MediaServiceInterface $mediaService, ModelManager $modelManager)
    {
        $this->mediaService = $mediaService;
        $this->modelManager = $modelManager;
    }



    /**
     * Returns a Media for an Ordernumber
     *
     * @param string $ordernumber
     * @param int $imageNumber
     *
     * @return string|null
     */
    public function get(string $ordernumber, int $imageNumber = 0)
    {
        $media = $this->getMediaForOrdernumber($ordernumber, $imageNumber);

        if ($media === null) {
            return null;
        }

        return $this->mediaService->getUrl($media->getPath());
    }



    /**
     * @param string $ordernumber
     *
     * @return array
     */
    public function getAll(string $ordernumber): array
    {
        $mediaArray = $this->getAllMediaForOrdernumber($ordernumber);

        $urls = [];
        foreach ($mediaArray as $media) {
            $urls[] = $this->mediaService->getUrl($media->getPath());
        }

        return $urls;
    }



    /**
     * @param string $ordernumber
     *
     * @return int
     */
    public function count(string $ordernumber): int
    {
        return \count($this->getAllMediaForOrdernumber($ordernumber));
    }



    public function getName(): string
    {
        return 'Shopware Media Provider';
    }



    public function getConfigParameter(): array
    {
        return [];
    }



    public function getConfig(): array
    {
        return [];
    }



    public function setConfig(array $config)
    {
    }



    private function getMediaForOrdernumber(string $ordernumber, int $imageNumber = 0)
    {
        $article = $this->getArticleByOrdernumber($ordernumber);

        if ($article === null) {
            return null;
        }

        if ($imageNumber !== 0) {
            $imageList = $this->getAllMediaForOrdernumber($ordernumber);

            if (\count($imageList) < $imageNumber) {
                return null;
            }

            return $imageList[$imageNumber];
        }

        $image = $this->modelManager->getRepository(Image::class)->findOneBy(['article' => $article]);

        if ($image === null) {
            return null;
        }

        return $this->getMediaForImage($image);
    }



    /**
     * @param string $ordernumber
     *
     * @return Article|null
     */
    private function getArticleByOrdernumber(string $ordernumber)
    {
        /** @var Detail $detail */
        $detail = $this->modelManager->getRepository(Detail::class)->findOneBy(['number' => $ordernumber]);

        if ($detail === null) {
            return null;
        }

        return $detail->getArticle();
    }



    private function getAllMediaForOrdernumber(string $ordernumber)
    {
        $article = $this->getArticleByOrdernumber($ordernumber);

        if ($article === null) {
            return null;
        }

        $images = $this->modelManager->getRepository(Image::class)->findBy(['article' => $article]);

        $mediaArray = [];
        foreach ($images as $image) {
            $mediaArray[] = $this->getMediaForImage($image);
        }

        return $mediaArray;
    }



    private function getMediaForImage(Image $image)
    {
        if ($image === null) {
            return null;
        }

        /** @var Image|null $parent */
        $parent = $image->getParent();
        if ($parent !== null) {
            return $parent->getMedia();
        }

        return $image->getMedia();
    }
}
