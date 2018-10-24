<?php declare(strict_types=1);

namespace OstMediaConnector\Commands;

use OstMediaConnector\Bundles\OstMediaConnectorBundle\Services\Structs\Media;
use OstMediaConnector\Utils\MultiCurl;
use Shopware\Bundle\StoreFrontBundle\Struct\Thumbnail;
use Shopware\Commands\ShopwareCommand;
use Shopware\Models\Article\Detail;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WarmImageCacheCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('mediaserver:cache:warm')->setDescription('Warm ImageCache');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \ReflectionException
     * @throws \RuntimeException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $details = $this->container->get('models')->getRepository(Detail::class)->findBy(['active' => true]);
        $imageSerivce = $this->container->get('ost_media_connector.services.image_service');

        $pb = new ProgressBar($output, \count($details));
        $pb->start();
        $pb->setFormat('very_verbose');

        $shopContext = $this->container->get('shopware_storefront.context_service')->createShopContext(1);
        $shopUrl = $shopContext->getBaseUrl() . $shopContext->getShop()->getHost() . $shopContext->getShop()->getUrl();

        $mc = new MultiCurl();

        /** @var Detail $detail */
        foreach ($details as $detail) {
            try {
                $images = $imageSerivce->getAll($detail->getNumber());
            } catch (\Exception $e) {
                continue;
            }

            /** @var Media $image */
            foreach ($images as $image) {
                if ($image === null) {
                    continue;
                }

                $thumbs = $image->getThumbnails();

                /** @var Thumbnail $thumb */
                foreach ($thumbs as $thumb) {
                    $sourceUrl = explode('/', $thumb->getSource());
                    $mc->addRequest($shopUrl . '/OstMediaConnector/image/' . $sourceUrl[\count($sourceUrl) - 2] . '/' . $sourceUrl[\count($sourceUrl) - 1]);

                    $retinaUrl = explode('/', $thumb->getRetinaSource());
                    $mc->addRequest($shopUrl . '/OstMediaConnector/image/' . $retinaUrl[\count($retinaUrl) - 2] . '/' . $retinaUrl[\count($retinaUrl) - 1]);
                }
            }

            $mc->execute();
            $mc->reset();

            $pb->advance();
        }
        $pb->finish();
        $output->writeln('');

        $output->writeln('<info>' . sprintf('Done') . '</info>');
    }
}
