<?php declare(strict_types=1);

namespace OstMediaConnector\Commands;

use Doctrine\Common\Collections\ArrayCollection;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Services\Structs\Media as OstMediaConnectorMedia;
use Shopware\Commands\ShopwareCommand;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\ConfigReader;
use Shopware\Components\Plugin\ConfigWriter;
use Shopware\Models\Article\Detail;
use Shopware\Models\Media\Media;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop\Shop;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AssignChangedCommand extends ShopwareCommand
{
    /**
     * @var ModelManager
     */
    private $modelManager;



    /**
     * @var ConfigReader
     */
    private $configReader;



    /**
     * @var ConfigWriter
     */
    private $configWriter;



    public function __construct(ModelManager $modelManager, ConfigReader $configReader, ConfigWriter $configWriter)
    {
        parent::__construct();

        $this->modelManager = $modelManager;
        $this->configReader = $configReader;
        $this->configWriter = $configWriter;
    }



    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('ost-media-connector:assign:changed-images');
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
        $pluginName = 'OstMediaConnector';
        $liveImageService = $this->container->get('ost_media_connector.services.live_image_service');
        $plugin = $this->modelManager->getRepository(Plugin::class)->findOneBy(['name' => $pluginName]);

        if ($plugin === null) {
            return;
        }

        $shop = $this->modelManager->getReference(Shop::class, 1);

        if (!($shop instanceof Shop)) {
            return;
        }

        $mediaApi = $this->container->get('shopware.api.media');
        $models = $this->container->get('models');
        $mediaApi->setManager($models);

        $config = $this->configReader->getByPluginName($pluginName, $shop);
        $lastRun = $config['lastRun'];

        if ($lastRun === null) {
            $lastRun = new \DateTime('now');
        } elseif (\is_array($lastRun)) {
            $lastRun = new \DateTime($lastRun['date']);
        }

        $qb = $this->container->get('dbal_connection')->createQueryBuilder();
        $qb->select('id')
            ->from('s_articles')
            ->where('changetime >= :day_start')
            ->setParameter('day_start', date('Y-m-d 00:00:00', $lastRun->getTimestamp()));
        $result = $qb->execute()->fetchAll(\PDO::FETCH_ASSOC);

        $progressBar = new ProgressBar($output, \count($result));
        //$progressBar->setRedrawFrequency(20);
        $progressBar->start();

        foreach ($result as $row) {
            $id = $row['id'];

            try {
                /** @var Detail|null $detail */
                $detail = $models->getRepository(Detail::class)->findOneBy(['article' => $id]);
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

            $removeAll = count($images) !== $detail->getArticle()->getImages()->count();

            if (!$removeAll) {
                /** @var Media $image */
                foreach ($detail->getArticle()->getImages()->toArray() as $image) {
                    foreach ($images as $apiImage) {
                        $removeAll = $image->getFileSize() !== $apiImage->getFileSize();
                    }
                }
            }

            foreach ($detail->getArticle()->getImages()->toArray() as $image) {
                if ($removeAll) {
                    $models->remove($image);
                }
            }
            $models->flush();

            if ($removeAll) {
                $detail->getArticle()->setImages(new ArrayCollection());
            }

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

        $this->configWriter->saveConfigElement($plugin, 'lastRun', new \DateTime('now'), $shop);
    }
}
