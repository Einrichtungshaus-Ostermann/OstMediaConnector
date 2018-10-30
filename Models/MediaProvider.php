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

namespace OstMediaConnector\Models;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="s_plugins_mediaconnector_mediaprovider")
 */
class MediaProvider extends ModelEntity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id;



    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $providerName;



    /**
     * @ORM\Column(type="integer", unique=true)
     *
     * @var int
     */
    private $priority;



    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    private $query;



    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $config;



    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }



    /**
     * @return string
     */
    public function getProviderName(): string
    {
        return $this->providerName;
    }



    /**
     * @param string $providerName
     */
    public function setProviderName(string $providerName)
    {
        $this->providerName = $providerName;
    }



    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }



    /**
     * @param int $priority
     */
    public function setPriority(int $priority)
    {
        $this->priority = $priority;
    }



    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }



    /**
     * @param string $query
     */
    public function setQuery(string $query)
    {
        $this->query = $query;
    }



    /**
     * @return string
     */
    public function getConfig(): string
    {
        return $this->config;
    }



    /**
     * @param string $config
     */
    public function setConfig(string $config)
    {
        $this->config = $config;
    }
}
