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

namespace OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaCache\Structs;

use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\ResourceCache\Structs\ResourceToken;

class CachedMedia
{
    /**
     * @var string
     */
    protected $orderNumber;



    /**
     * @var ResourceToken[]
     */
    protected $resourceTokens;



    /**
     * @var string
     */
    protected $file;



    /**
     * @var string
     */
    protected $extension;



    /**
     * @var bool
     */
    protected $preview;



    /**
     * @var int
     */
    protected $height;



    /**
     * @var int
     */
    protected $width;



    /**
     * @var string
     */
    private $seoName;



    /**
     * @var int
     */
    private $fileSize;



    /**
     * CachedMedia constructor.
     *
     * @param string $orderNumber
     * @param string $fileName
     * @param int $height
     * @param int $width
     * @param int $fileSize
     * @param ResourceToken[] $resourceTokens
     * @param string|null $seoName
     */
    public function __construct(string $orderNumber, string $fileName, int $height, int $width, int $fileSize, array $resourceTokens = [], string $seoName = null)
    {
        $this->file = $fileName;
        $this->extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $this->preview = false;
        $this->width = $width;
        $this->height = $height;
        $this->orderNumber = $orderNumber;
        $this->resourceTokens = $resourceTokens;
        $this->seoName = $seoName ?? $orderNumber;
        $this->fileSize = $fileSize;
    }



    /**
     * @return string
     */
    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }



    /**
     * @return ResourceToken[]
     */
    public function getResourceTokens(): array
    {
        return $this->resourceTokens;
    }



    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }



    /**
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }



    /**
     * @return bool
     */
    public function isPreview(): bool
    {
        return $this->preview;
    }



    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }



    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }



    /**
     * @return string
     */
    public function getSeoName(): string
    {
        return $this->seoName . '.' . $this->extension;
    }



    public function getMediaToken(): MediaToken
    {
        return new MediaToken($this->orderNumber, $this->file);
    }



    /**
     * @return int
     */
    public function getFileSize(): int
    {
        return $this->fileSize;
    }
}
