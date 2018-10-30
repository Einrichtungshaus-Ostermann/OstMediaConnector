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

namespace OstMediaConnector\Subscribers;

use Enlight\Event\SubscriberInterface;

class ControllerPath implements SubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_OstMediaConnector' => 'onGetControllerPathFrontend',
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_OstMediaConnector'  => 'onGetControllerPathBackend'
        ];
    }



    /**
     * Register the backend controller
     *
     * @param \Enlight_Event_EventArgs $args
     *
     * @return string
     * @Enlight\Event Enlight_Controller_Dispatcher_ControllerPath_Backend_OstMediaConnector
     */
    public function onGetControllerPathBackend(\Enlight_Event_EventArgs $args)
    {
        return __DIR__ . '/../Controllers/Backend/OstMediaConnector.php';
    }



    /**
     * Register the frontend controller
     *
     * @param \Enlight_Event_EventArgs $args
     *
     * @return string
     * @Enlight\Event Enlight_Controller_Dispatcher_ControllerPath_Frontend_OstMediaConnector
     */
    public function onGetControllerPathFrontend(\Enlight_Event_EventArgs $args)
    {
        return __DIR__ . '/../Controllers/Frontend/OstMediaConnector.php';
    }
}
