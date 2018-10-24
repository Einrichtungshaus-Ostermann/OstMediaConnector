<?php declare(strict_types=1);

/**
 * Einrichtungshaus Ostermann GmbH & Co. KG - Media Connector
 *
 * @package   OstMediaConnector
 *
 * @author    Tim Windelschmidt <tim.windelschmidt@ostermann.de>
 * @copyright 2018 Einrichtungshaus Ostermann GmbH & Co. KG
 * @license   proprietary
 */

namespace OstMediaConnector\Setup;

use Doctrine\ORM\Tools\SchemaTool;
use OstMediaConnector\Models\MediaProvider;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\UninstallContext;

class Uninstall
{
    /**
     * Main bootstrap object.
     *
     * @var Plugin
     */
    protected $plugin;



    /**
     * ...
     *
     * @var UninstallContext
     */
    protected $context;



    /**
     * ...
     *
     * @var ModelManager
     */
    protected $modelManager;



    /**
     * ...
     *
     * @var CrudService
     */
    protected $crudService;



    /**
     * ...
     *
     * @param Plugin           $plugin
     * @param UninstallContext $context
     * @param ModelManager     $modelManager
     * @param CrudService      $crudService
     */
    public function __construct(Plugin $plugin, UninstallContext $context, ModelManager $modelManager, CrudService $crudService)
    {
        // set params
        $this->plugin = $plugin;
        $this->context = $context;
        $this->modelManager = $modelManager;
        $this->crudService = $crudService;
    }



    /**
     * ...
     *
     * @throws \Exception
     */
    public function uninstall()
    {
        $tool = new SchemaTool($this->modelManager);
        $classes = [
            $this->modelManager->getClassMetadata(MediaProvider::class)
        ];

        $tool->dropSchema($classes);
    }
}
