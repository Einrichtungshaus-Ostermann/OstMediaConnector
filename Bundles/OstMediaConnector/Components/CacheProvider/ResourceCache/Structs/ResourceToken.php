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

namespace OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\ResourceCache\Structs;

use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\Token\AbstractToken;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\Token\InvalidTokenException;

/**
 * Class ResourceToken
 *
 * @package OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\Cache\ResourceCache\Structs
 */
class ResourceToken extends AbstractToken
{
    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var int
     */
    protected $height;

    /**
     * @var int
     */
    protected $width;

    /**
     * ResourceToken constructor.
     *
     * @param string $fileName
     * @param int $height
     * @param int $width
     */
    public function __construct(string $fileName, int $height, int $width)
    {
        $this->fileName = $fileName;
        $this->height = $height;
        $this->width = $width;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName(string $fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $height
     */
    public function setHeight(int $height)
    {
        $this->height = $height;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     */
    public function setWidth(int $width)
    {
        $this->width = $width;
    }

    /**
     * @return string
     */
    public function getRetinaToken(): string
    {
        return $this->createToken([$this->getFileName(), $this->getHeight() * 2, $this->getWidth() * 2]);
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->createToken([$this->getFileName(), $this->getHeight(), $this->getWidth()]);
    }

    /**
     * @param string $token
     *
     * @throws InvalidTokenException
     *
     * @return self
     */
    public static function fromToken(string $token): self
    {
        return new self(...self::extractToken($token, 3));
    }
}
