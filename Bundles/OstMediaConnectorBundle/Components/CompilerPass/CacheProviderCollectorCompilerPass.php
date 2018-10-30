<?php declare(strict_types=1);

namespace OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CompilerPass;

use Shopware\Components\DependencyInjection\Compiler\TagReplaceTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class MediaProviderCollectorPass.
 */
class CacheProviderCollectorCompilerPass implements CompilerPassInterface
{
    use TagReplaceTrait;



    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $this->replaceArgumentWithTaggedServices($container, 'ost_media_connector.services.cache_provider_selector', 'ost_media_connector.components.media_cache', 0);
    }
}
