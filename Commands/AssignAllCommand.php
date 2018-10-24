<?php declare(strict_types=1);

namespace OstMediaConnector\Commands;

use OstMediaConnector\Bundles\OstMediaConnectorBundle\Services\Structs\Media as OstMediaConnectorMedia;
use Shopware\Commands\ShopwareCommand;
use Shopware\Models\Article\Detail;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AssignAllCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('mediaserver:assign:all');
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
        $liveImageService = $this->container->get('ost_media_connector.services.live_image_service');

        $mediaApi = $this->container->get('shopware.api.media');
        $models = $this->container->get('models');
        $mediaApi->setManager($models);

        /** @var Detail[] $details */
        $details = $models->getRepository(Detail::class)->findAll();

        $progressBar = new ProgressBar($output, \count($details));
        //$progressBar->setRedrawFrequency(20);
        $progressBar->start();


        foreach ($details as $detail) {
            /** @var OstMediaConnectorMedia[] $images */
            $images = $liveImageService->getAll($detail->getNumber());

            foreach ($images as $number => $image) {
                if ($image === null) {
                    continue;
                }

                $params = [
                    'album'       => '-1',
                    'file'        => $image->getFile(),
                    'description' => $detail->getArticle()->getName()
                ];

                $media = $mediaApi->create($params);

                $detail->getArticle()->getImages()->add($media);
            }

            $models->flush();

            $progressBar->advance();
        }
        $progressBar->finish();
    }
}
