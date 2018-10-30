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

/**
 * Class MediaProviderListProvider
 *
 * @package OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\MediaProvider
 */
interface MediaProviderList
{
    /**
     * @return MediaProvider[]
     */
    public function getMediaProviderList(): array;



    /**
     * @param string $ordernumber
     *
     * @return array
     */
    public function getConfiguredProviderList(string $ordernumber): array;
}
