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

namespace OstMediaConnector\Bundles\OstMediaConnectorBundle\Services\Structs;

use Shopware\Bundle\StoreFrontBundle\Struct\Media as MediaStruct;

class Media extends MediaStruct
{
    /**
     * @var int
     */
    private $fileSize;



    /**
     * CachedMedia constructor.
     *
     * @param string $fileName
     * @param int $height
     * @param int $width
     * @param int $fileSize
     * @param array $thumbnails
     */
    public function __construct(string $fileName, int $height, int $width, int $fileSize, array $thumbnails = [])
    {
        $this->file = $fileName;
        $this->name = pathinfo($fileName, PATHINFO_FILENAME);
        $this->extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $this->preview = false;
        $this->width = $width;
        $this->height = $height;
        $this->attributes = [];
        $this->fileSize = $fileSize;
        $this->thumbnails = $thumbnails;
    }



    /**
     * @return int
     */
    public function getFileSize(): int
    {
        return $this->fileSize;
    }
}
