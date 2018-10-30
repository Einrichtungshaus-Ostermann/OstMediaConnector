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

namespace OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaAssociationCache;

/**
 * Interface MediaAssociationCache
 *
 * @package OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\MediaAssociationCache
 */
interface MediaAssociationCache
{
    /**
     * @param string $ordernumber
     * @param int $imageNumber
     *
     * @return bool
     */
    public function hasMediaAssociation(string $ordernumber, int $imageNumber): bool;



    /**
     * @param string $ordernumber
     * @param int $imageNumber
     *
     * @throws MediaAssociationNotFoundException
     *
     * @return string
     */
    public function getMediaAssociation(string $ordernumber, int $imageNumber): string;



    /**
     * @param string $ordernumber
     * @param int $imageNumber
     * @param string $path
     *
     * @return bool
     */
    public function storeMediaAssociation(string $ordernumber, int $imageNumber, string $path): bool;
}
