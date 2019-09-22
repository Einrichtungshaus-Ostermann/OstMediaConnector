<?php declare(strict_types=1);

/**
 * Einrichtungshaus Ostermann GmbH & Co. KG - Media Connector
 *
 * Import Images from external Servers
 *
 * 1.0.0
 * - initial release
 *
 * 1.0.1
 * - changed console commands
 *
 * 1.0.2
 * - fixed plugin name and description
 *
 * 1.0.3
 * - fixed configuration
 *
 * 1.0.4
 * -  add local pim media source
 *
 * 1.0.5
 * - fixed fetching faulty headers
 *
 * 1.1.0
 * - removed everything except sync command
 *
 * 1.1.1
 * - fixed wrong service.xml
 *
 * 1.1.2
 * - removed leftover database table
 *
 * 1.1.3
 * - added oms support
 *
 * 1.2.0
 * - removed everything
 *
 * @package   OstMediaConnector
 *
 * @author    Tim Windelschmidt <tim.windelschmidt@ostermann.de>
 * @copyright 2018 Einrichtungshaus Ostermann GmbH & Co. KG
 * @license   proprietary
 */

namespace OstMediaConnector;

use Shopware\Components\Plugin;

class OstMediaConnector extends Plugin
{
}
