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
use Enlight_Template_Manager;

class TemplateSubscriber implements SubscriberInterface
{
    /**
     * @var Enlight_Template_Manager
     */
    private $template;



    public function __construct(Enlight_Template_Manager $template)
    {
        $this->template = $template;
    }



    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatch_Backend'  => 'registerViews',
            'Enlight_Controller_Action_PostDispatch_Frontend' => 'registerViews'
        ];
    }



    public function registerViews(\Enlight_Event_EventArgs $args)
    {
        $this->template->addTemplateDir(__DIR__ . '/../Resources/Views/');
    }
}
