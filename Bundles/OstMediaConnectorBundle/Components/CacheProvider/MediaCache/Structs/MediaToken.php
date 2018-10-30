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

use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\Token\AbstractToken;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\Token\InvalidTokenException;

/**
 * Class MediaToken
 *
 * @package OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\Cache\MediaCache\Structs
 */
class MediaToken extends AbstractToken
{
    /**
     * @var int
     */
    protected $orderNumber;



    /**
     * @var string
     */
    protected $fileName;



    /**
     * MediaToken constructor.
     *
     * @param string $ordernumber
     * @param string $fileName
     */
    public function __construct(string $ordernumber, string $fileName)
    {
        $this->orderNumber = $ordernumber;
        $this->fileName = $fileName;
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
        return new self(...self::extractToken($token));
    }



    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->createToken([$this->getFileName(), $this->getOrderNumber()]);
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
     * @return string
     */
    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }



    /**
     * @param string $ordernumber
     */
    public function setOrderNumber(string $ordernumber)
    {
        $this->orderNumber = $ordernumber;
    }
}
