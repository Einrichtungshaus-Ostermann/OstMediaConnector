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

use OstMediaConnector\Models\MediaProvider;
use Shopware_Controllers_Backend_Application;

class MediaConnectorConfig extends Shopware_Controllers_Backend_Application
{
    protected $model = MediaProvider::class;

    protected $alias = 'mediaprovider';
}
