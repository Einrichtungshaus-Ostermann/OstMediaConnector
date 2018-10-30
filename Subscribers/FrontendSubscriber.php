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
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Services\MediaService;
use Shopware\Components\Compatibility\LegacyStructConverter;

class FrontendSubscriber implements SubscriberInterface
{
    /**
     * @var LegacyStructConverter
     */
    private $legacyStructConverter;



    /**
     * @var MediaService
     */
    private $mediaService;



    /**
     * @var array
     */
    private $config;



    public function __construct(LegacyStructConverter $legacyStructConverter, MediaService $mediaService, array $config)
    {
        $this->legacyStructConverter = $legacyStructConverter;
        $this->mediaService = $mediaService;
        $this->config = $config;
    }



    public static function getSubscribedEvents()
    {
        return [
            'Legacy_Struct_Converter_Convert_Product'      => 'onConvertProduct',
            'Legacy_Struct_Converter_Convert_List_Product' => 'onConvertListProduct',
        ];
    }



    public function onConvertProduct(\Enlight_Event_EventArgs $args)
    {
        if (!$this->config['liveHook']) {
            return;
        }

        $sArticle = $args->getReturn();

        $ordernumber = $sArticle['ordernumber'];

//        Shopware()->Events()->addListener(KernelEvents::TERMINATE, function () {
//            //TODO: Check Image correct
//        });

        $imageStructs = $this->mediaService->getAll($ordernumber);

        $convertedImageStructs = [];
        foreach ($imageStructs as $imageStruct) {
            $convertedImageStructs[] = $this->legacyStructConverter->convertMediaStruct($imageStruct);
        }

        $images = $convertedImageStructs;

        $sArticle['image'] = [];
        $sArticle['images'] = [];

        if (\count($images) >= 1) {
            $firstImage = array_shift($images);
            $firstImage['main'] = true;

            $sArticle['image'] = $firstImage;
        }

        if (\count($images) >= 1) {
            $sArticle['images'] = $images;
        }

        $args->setReturn($sArticle);
    }



    public function onConvertListProduct(\Enlight_Event_EventArgs $args)
    {
        if (!$this->config['liveHook']) {
            return;
        }

        $sArticle = $args->getReturn();

        $ordernumber = $sArticle['ordernumber'];

        $imageStruct = $this->mediaService->getMedia($ordernumber);

        $image = $this->legacyStructConverter->convertMediaStruct($imageStruct);

        $image['main'] = true;

        $sArticle['image'] = $image;

        $args->setReturn($sArticle);
    }
}
