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

namespace OstMediaConnector\Controllers\Frontend;

use Enlight_Controller_Action;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\ResourceCache\ResourceNotFoundException;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\ResourceCache\Structs\ResourceToken;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\Token\InvalidTokenException;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Services\MediaService;
use Symfony\Component\HttpKernel\KernelEvents;

class OstMediaConnector extends Enlight_Controller_Action
{
    /** @var MediaService */
    private $imageService;



    public function preDispatch()
    {
        /** @var \Shopware\Components\Plugin $plugin */
        $plugin = $this->get('kernel')->getPlugins()['OstMediaConnector'];
        $this->imageService = $this->container->get('ost_media_connector.services.image_service');
    }



    public function imageAction()
    {
        $params = $this->request->getParams();
        $token = array_keys($params)[3];

        try {
            try {
                $resource = $this->imageService->getResource($token);
            } catch (InvalidTokenException $invalidTokenException) {
                error_log($invalidTokenException->getMessage() . ' - ' . $invalidTokenException->getToken());

                return;
            }

            $this->response->setHeader('Content-type', 'image/jpg');

            ob_start();
            imagepng($resource);
            $contents = ob_get_contents();
            ob_end_clean();

            $this->response->setBody($contents);
        } catch (ResourceNotFoundException $resourceNotFoundException) {
            $this->redirect('/frontend/_public/src/img/no-picture.jpg');
        }

        Shopware()->Events()->addListener(KernelEvents::TERMINATE, function () use ($token) {
            $resourceToken = ResourceToken::fromToken($token);
            //TODO: Check if File is correct MD5
        });
    }
}
