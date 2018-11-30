<?php declare(strict_types=1);

namespace OstMediaConnector\Commands;

use OstMediaConnector\Bundles\OstMediaConnectorBundle\Services\Structs\Media as OstMediaConnectorMedia;
use Shopware\Commands\ShopwareCommand;
use Shopware\Models\Article\Detail;
use Shopware\Models\Media\Album;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AssignNoImageCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('mediaserver:assign:noimage');
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

        $qb = $this->container->get('dbal_connection')->createQueryBuilder();
        $qb->select('articleID', 'ordernumber')
            ->from('s_articles_details')
            ->where('articleID NOT IN (SELECT articleID FROM s_articles_img)');
        $result = $qb->execute()->fetchAll(\PDO::FETCH_ASSOC);

        $progressBar = new ProgressBar($output, \count($result));
        //$progressBar->setRedrawFrequency(20);
        $progressBar->start();

        /** @var Album $album */
        $album = $models->getRepository(Album::class)->find(Album::ALBUM_ARTICLE);

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

            /** @var OstMediaConnectorMedia[] $images */
            $images = $liveImageService->getAll($number);

            foreach ($images as $index => $image) {
                if ($image === null) {
                    continue;
                }

                $params = [
                    'album'       => -1,
                    'file'        => $image->getFile(),
                    'description' => $detail->getArticle()->getName()
                ];

                $media = $mediaApi->create($params);

                $imageNumber = $index;
                ++$imageNumber;

                $models->getDBALQueryBuilder()->insert('s_articles_img')
                    ->values([
                        'articleID'         => ':articleID',
                        'img'               => ':img',
                        'main'              => ':main',
                        'description'       => ':description',
                        'position'          => ':position',
                        'width'             => ':width',
                        'height'            => ':height',
                        'relations'         => ':relations',
                        'extension'         => ':extension',
                        'parent_id'         => ':parent_id',
                        'article_detail_id' => ':article_detail_id',
                        'media_id'          => ':media_id',
                    ])->setParameters([
                        'articleID'         => $detail->getArticle()->getId(),
                        'img'               => $media->getName(),
                        'main'              => $imageNumber,
                        'description'       => $media->getDescription(),
                        'position'          => $imageNumber,
                        'width'             => 0,
                        'height'            => 0,
                        'relations'         => '',
                        'extension'         => $media->getExtension(),
                        'parent_id'         => null,
                        'article_detail_id' => $detail->getId(),
                        'media_id'          => $media->getId(),
                    ])->execute();
            }

            $progressBar->advance();
        }
        $progressBar->finish();
    }
}
