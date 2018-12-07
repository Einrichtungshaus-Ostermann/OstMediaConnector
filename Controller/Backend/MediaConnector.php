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

namespace OstMediaConnector\Controller\Backend;

use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\MediaProvider\MediaProvider;
use Shopware_Controllers_Backend_ExtJs;

class MediaConnector extends Shopware_Controllers_Backend_ExtJs
{
    public function mediaProviderListAction()
    {
        $mediaProviderInstances = $this->container->get('ost_media_connector.services.image_service')->getAllMediaProvider();

        $data = [];
        /** @var MediaProvider $mediaProviderInstance */
        foreach ($mediaProviderInstances as $mediaProviderInstance) {
            $data[] = [
                'name'            => $mediaProviderInstance->getName(),
                'configParameter' => $mediaProviderInstance->getConfigParameter()
            ];
        }

        /* @noinspection PhpParamsInspection */
        $this->View()->assign(['success' => true, 'data' => $data, 'total' => count($data)]);
    }
}
