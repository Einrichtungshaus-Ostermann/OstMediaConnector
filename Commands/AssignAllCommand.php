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
     * @throws \RuntimeException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $liveImageService = $this->container->get('ost_media_connector.services.live_image_service');

        $mediaApi = $this->container->get('shopware.api.media');
        $models = $this->container->get('models');
        $mediaApi->setManager($models);


        $qb = $this->container->get('dbal_connection')->createQueryBuilder();
        $qb->select('articleID', 'ordernumber')
            ->from('s_articles_details');
        $result = $qb->execute()->fetchAll(\PDO::FETCH_ASSOC);

        $progressBar = new ProgressBar($output, \count($result));
        //$progressBar->setRedrawFrequency(20);
        $progressBar->start();


        foreach ($result as $row) {
            $number = $row['ordernumber'];

            try {
                /** @var Detail|null $detail */
                $detail = $models->getRepository(Detail::class)->findOneBy(['number' => $number]);
            } catch (\Exception $exception) {
                $output->writeln($exception->getMessage());
                continue;
            }

            if ($detail === null) {
                continue;
            }

            if (empty($detail->getArticle()->getName())) {
                continue;
            }

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
