<?php declare(strict_types=1);

namespace OstMediaConnector\Commands;

use Shopware\Commands\ShopwareCommand;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Configurator\Option;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Image;
use Shopware\Models\Media\Media;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncImages extends ShopwareCommand
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /** @var \Shopware\Components\Api\Resource\Media */
    private $mediaApi;
    /**
     * @var array
     */
    private $config;

    public function __construct(ModelManager $modelManager, array $config)
    {
        parent::__construct();

        $this->modelManager = $modelManager;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('ost-media-connector:sync-images')
            ->addOption('dry-run', 'd');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $companyArticleNumberImageMap = $this->getNumberImageMap();
        $this->mediaApi = $this->container->get('shopware.api.media');
        $this->mediaApi->setManager($this->modelManager);

        $progressBar = new ProgressBar($output);
        ProgressBar::setPlaceholderFormatterDefinition('memory', function (ProgressBar $bar) {
            return "\033[" . '41;37' . 'm ' . Helper::formatMemory(memory_get_usage()) . " \033[0m";
        });

        if (!$output->isVerbose()) {
            $progressBar->setBarWidth(100);
            $progressBar->setFormat(" \033[44;37m %message:-37s% \033[0m\n %current%/%max% %bar% %percent:3s%%\n 🏁  %remaining:-10s% %memory:37s%");
            $progressBar->setBarCharacter("\033[32m●\033[0m");
            $progressBar->setEmptyBarCharacter("\033[31m●\033[0m");
            $progressBar->setProgressCharacter("\033[32m➤ \033[0m");
        }

        $articleData = $this->getData($progressBar);

        $progressBar->start(count($articleData));
//        $progressBar->setRedrawFrequency($progressBar->getMaxSteps() / 100);
        $progressBar->setMessage('Checking Articles');

        $info = [
            'new' => [],
            'changed' => [],
            'unchanged' => [],
            'remove' => []
        ];
        foreach ($articleData as $orderNumber => $articleEntry) {
            $articleNumberImageMap = $companyArticleNumberImageMap[$articleEntry['companyID']];

            /*
             * Check if there are Images on PIM Media for the article.
             * If not found delete all s_articles_img entries for the article.
             */
            if (!isset($articleNumberImageMap[$orderNumber])) {
                if (count($articleEntry['media']) !== 0) {
                    $info['remove'][] = $orderNumber;

                    if ($output->isVerbose()) {
                        $output->writeln('Removing all Images from ' . $orderNumber);
                    }
                    if ($articleEntry['isVariant'] && !$articleEntry['isMainVariant']) {
                        $progressBar->setMessage('Removing all Detail Images from ' . $orderNumber);

                        if (!$input->getOption('dry-run')) {
                            $qb = $this->getQueryBuilder();
                            $qb->delete('s_articles_img')
                                ->andWhere('article_detail_id = :article_detail_id')
                                ->setParameter(':article_detail_id', $articleEntry['articleDetailID'])
                                ->execute();
                            unset($qb);
                        }

                    } else {
                        $progressBar->setMessage('Removing all Article Images from ' . $orderNumber);

                        if (!$input->getOption('dry-run')) {
                            $qb = $this->getQueryBuilder();
                            $qb->delete('s_articles_img')
                                ->andWhere('articleID = :articleID')
                                ->setParameter(':articleID', $articleEntry['articleID'])
                                ->execute();
                            unset($qb);
                        }
                    }
                }

                continue;
            }

            $pimImages = $articleNumberImageMap[$orderNumber];

            /*
             * Iterate over all Images and remove images that arent existing anymore
             */
            foreach ($articleEntry['media'] as $pos => $image) {
                $logString = $orderNumber . '.' . ($pos - 1);

                if (!isset($pimImages[$pos - 1])) {
                    $message = 'Removing ' . $orderNumber . ' - Image ' . $pos;
                    if ($output->isVerbose()) {
                        $output->writeln($message);
                    }

                    $progressBar->setMessage($message);
                    if (!$input->getOption('dry-run')) {
                        $this->deleteImage($articleEntry, $pos);
                    }

                    $info['remove'][] = $logString;
                }
            }

            foreach ($pimImages as $i => $pimImage) {
                $imageIndex = $i + 1;
                $logString = $logString = $orderNumber . '.' . $imageIndex;

                if (count($articleEntry['media']) !== 0 && isset($articleEntry['media'][$imageIndex])) {
                    $checkImage = $articleEntry['media'][$imageIndex];

                    if ((int)$checkImage['file_size'] === filesize($pimImage)) {
                        $info['unchanged'][] = $logString;
                        // Nothing changed. Can continue
                        continue;
                    }

                    if (!$input->getOption('dry-run')) {
                        $this->deleteImage($articleEntry, $imageIndex);
                    }

                    $message = 'Replacing ' . $orderNumber . ' - Image ' . $imageIndex;
                    $info['changed'][] = $logString;
                } else {
                    $message = 'Adding ' . $orderNumber . ' - Image ' . $imageIndex;
                    $info['new'][] = $logString;
                }

                if ($articleEntry['isVariant'] !== 1) {
                    if ($articleEntry['isMainVariant']) {
                        $isMain = true;
                    } else {
                        $isMain = false;
                    }
                } else {
                    $isMain = ($imageIndex === 1);
                }

                if ($output->isVerbose()) {
                    $output->writeln($message);
                    var_dump($articleData[$orderNumber]);
                    var_dump($articleNumberImageMap[$orderNumber]);
                }
                $progressBar->setMessage($message);
                //TODO: Import new Image
                try {
                    if (!$input->getOption('dry-run')) {
                        $this->importImage($pimImage, $articleEntry, $isMain, $imageIndex);
                    }
                } catch (\Exception $e) {
                    $output->writeln($e->getMessage());
                }
            }

            /* @noinspection DisconnectedForeachInstructionInspection */
            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln('');
        $output->writeln(sprintf('New: %d - Changed: %d - Unchanged: %d - Remove: %d', count($info['new']), count($info['changed']), count($info['unchanged']), count($info['remove'])));
    }

    private function deleteImage(array $articleEntry, int $position)
    {
        if ($articleEntry['isVariant'] && !$articleEntry['isMainVariant']) {
            $qb = $this->getQueryBuilder();
            $qb->delete('s_articles_img')
                ->where('position = :position')
                ->andWhere('article_detail_id = :article_detail_id')
                ->setParameter(':position', $position)
                ->setParameter(':article_detail_id', $articleEntry['articleDetailID'])
                ->execute();
            unset($qb);
        } else {
            $qb = $this->getQueryBuilder();
            $qb->delete('s_articles_img')
                ->where('position = :position')
                ->andWhere('articleID = :articleID')
                ->setParameter(':position', $position)
                ->setParameter(':articleID', $articleEntry['articleID'])
                ->execute();
            unset($qb);
        }
    }

    private function getData(ProgressBar $progressBar)
    {
        $progressBar->start(8);

        $progressBar->setMessage('Loading Articles');
        $progressBar->advance();
        $qb = $this->getQueryBuilder();
        $qb->select('article.id, article.main_detail_id, attributes.attr1')
            ->from('s_articles', 'article')
            ->innerJoin('article', 's_articles_attributes', 'attributes', 'article.id = attributes.articleID')
            ->where('active = 1')
            ->andWhere('attributes.attr1 != ""');
        $articlesResult = $qb->execute()->fetchAll(\PDO::FETCH_ASSOC);
        unset($qb);

        $progressBar->setMessage('Sorting Articles');
        $progressBar->advance();
        $articles = [];
        array_walk($articlesResult, function ($article) use (&$articles) {
            $article['detail_count'] = 0;
            $articles[$article['id']] = $article;
        });
        unset($articlesResult);

        $progressBar->setMessage('Loading Details');
        $progressBar->advance();
        $qb = $this->getQueryBuilder();
        $qb->select('id, articleID,ordernumber')
            ->from('s_articles_details')
            ->where('active = 1');
        $articleDetailsResult = $qb->execute()->fetchAll(\PDO::FETCH_ASSOC);
        unset($qb);

        $progressBar->setMessage('Sorting Details');
        $progressBar->advance();
        $articleDetails = [];
        array_walk($articleDetailsResult, function ($articleDetail) use (&$articleDetails, &$articles) {
            $articleID = $articleDetail['articleID'];
            if (!isset($articles[$articleID])) {
                return;
            }

            $articles[$articleID]['detail_count']++;
            $articleDetails[$articleDetail['id']] = $articleDetail;
        });
        unset($articleDetailsResult);

        $progressBar->setMessage('Loading Images');
        $progressBar->advance();
        $qb = $this->getQueryBuilder();
        $qb->select('*')
            ->from('s_articles_img');
        $articlesImageResult = $qb->execute()->fetchAll(\PDO::FETCH_ASSOC);
        unset($qb);

        $progressBar->setMessage('Sorting Images');
        $progressBar->advance();
        $articleImages = [];
        $combinedImages = [];
        // Sorts so the s_articles_img.id is the Key and assign normal Images
        array_walk($articlesImageResult, function ($articleImage) use (&$articleImages, &$combinedImages, $articles, $articleDetails) {
            $articleID = $articleImage['articleID'];
            if ($articleID === null) {
                return;
            }

            // Sort the s_articles_img to have the id as key for the parent resolving
            $articleImages[$articleImage['id']] = $articleImage;
        });

        // Resolves Parent from s_articles_img
        array_walk($articlesImageResult, function ($articleImage) use (&$combinedImages, $articleImages, $articleDetails) {
            // Variant Article Association
            if ($articleImage['article_detail_id'] === null) {
                return;
            }

            $detailID = $articleImage['article_detail_id'];

            // Variant Article Association
            if (!isset($articleDetails[$detailID])) {
                return;
            }

            $orderNumber = $articleDetails[$detailID]['ordernumber'];
            $parentID = $articleImage['parent_id'];

            if (isset($articleImages[$parentID])) {
                $combinedImages[$orderNumber][$articleImage['position']] = $articleImages[$parentID];
            }
        });

        // Article Association
        array_walk($articlesImageResult, function ($articleImage) use (&$combinedImages, $articleDetails, $articles) {
            $articleID = $articleImage['articleID'];
            if ($articleID === null) {
                return;
            }

            if (!isset($articles[$articleID])) {
                return;
            }

            $article = $articles[$articleID];
            $mainDetailID = $article['main_detail_id'];

            if (!isset($articleDetails[$mainDetailID])) {
                return; // Invalid Article
            }

            $orderNumber = $articleDetails[$mainDetailID]['ordernumber'];

            $combinedImages[$orderNumber][$articleImage['position']] = $articleImage;
        });
        unset($articlesImageResult, $articleImages);

        $progressBar->setMessage('Loading Media');
        $progressBar->advance();
        $qb = $this->getQueryBuilder();
        $qb->select('*')
            ->from('s_media');
        $mediaResult = $qb->execute()->fetchAll(\PDO::FETCH_ASSOC);
        unset($qb);

        $progressBar->setMessage('Sorting Media');
        $progressBar->advance();
        $mediaEntries = [];
        array_walk($mediaResult, function ($media) use (&$mediaEntries) {
            $mediaEntries[$media['id']] = $media;
        });
        unset($mediaResult);

        $progressBar->setMessage('Building Data Tree');
        $data = [];
        array_walk($articleDetails, function ($detail) use (&$data, $articles, $articleDetails, $combinedImages, $mediaEntries) {
            $orderNumber = $detail['ordernumber'];
            $articleID = $detail['articleID'];
            $article = $articles[$articleID];

            $mainDetailID = $article['main_detail_id'];
            if (!isset($articleDetails[$mainDetailID])) {
                return; // Invalid Article
            }

            $isMainVariant = $detail['id'] === $mainDetailID;
            $isVariant = $article['detail_count'] !== 1;

            $media = [];
            if (isset($combinedImages[$orderNumber])) {
                foreach ($combinedImages[$orderNumber] as $detailImage) {
                    $article = $articles[$articleID];

                    if (!isset($mediaEntries[$detailImage['media_id']])) {
                        continue;
                    }

                    $media[$detailImage['position']] = $mediaEntries[$detailImage['media_id']];
                }
            }

            $data[$orderNumber] = [
                'companyID' => $article['attr1'],
                'ordernumber' => $orderNumber,
                'isVariant' => $isVariant,
                'articleID' => $articleID,
                'articleDetailID' => $detail['id'],
                'isMainVariant' => $isMainVariant,
                'media' => $media
            ];
        });

        return $data;
    }

    private function getQueryBuilder()
    {
        return $this->modelManager->getDBALQueryBuilder();
    }

    private function importImage(string $imagePath, array $articleEntry, bool $isMainImage, int $position)
    {
        $media = $this->importMedia($imagePath, (int)$articleEntry['articleID'], $isMainImage, $position);

        if ($articleEntry['isVariant']) {
            $this->createImageRules($media, $this->modelManager->find(Detail::class, $articleEntry['articleDetailID']));
        }
    }

    private function getNumberImageMap()
    {
        $imageFolders = explode(',', $this->config['imageFolders']);
        $variantFolderName = $this->config['variantFolderName'];
        $baseFolder = $this->config['baseFolder'];
        $companies = [
            1 => 'ostermann',
            3 => 'trends'
        ];

        $numberToImages = [];
        foreach ($companies as $companyID => $company) {
            $companyImages = [];

            foreach ($imageFolders as $imageNumber => $imageFolder) {
                $path = $baseFolder . DIRECTORY_SEPARATOR . $company . DIRECTORY_SEPARATOR . $imageFolder;

                if ($handle = opendir($path)) {
                    while (($entry = readdir($handle)) !== false) {
                        if ($entry !== '.' && $entry !== '..') {
                            $articleNumber = ltrim(pathinfo($entry, PATHINFO_FILENAME), '0');
                            $companyImages[$articleNumber][$imageNumber] = $path . DIRECTORY_SEPARATOR . $entry;
                        }
                    }

                    closedir($handle);
                }
            }

            $path = $baseFolder . DIRECTORY_SEPARATOR . $company . DIRECTORY_SEPARATOR . $variantFolderName;
            $re = '/0+(\d+)-0+(\d+)_b0+(\d+)/';
            if ($handle = opendir($path)) {
                while (($entry = readdir($handle)) !== false) {
                    if ($entry !== '.' && $entry !== '..') {
                        preg_match($re, pathinfo($entry, PATHINFO_FILENAME), $matches);

                        if (count($matches) === 0) {
                            continue;
                        }

                        $articleNumber = $matches[1];
                        if (isset($companyImages[$articleNumber])) {
                            unset($companyImages[$articleNumber]);
                        }

                        $articleNumber .= '-' . $matches[2];
                        $imageNumber = ((int)$matches[3]) - 1;

                        $companyImages[$articleNumber][$imageNumber] = $path . DIRECTORY_SEPARATOR . $entry;
                    }
                }

                closedir($handle);
            }

            foreach ($companyImages as $number => &$images) {
                $keys = array_keys($images);
                foreach ($keys as $shouldPos => $isPos) {
                    if ($shouldPos !== $isPos) {
                        array_splice($images, $shouldPos);
                        break;
                    }
                }
            }

            $numberToImages[$companyID] = $companyImages;
        }


        return $numberToImages;
    }

    /**
     * @param Media $media
     * @param Detail $detail
     */
    private function createImageRules(Media $media, Detail $detail): void
    {
        /** @var Image $articleImage */
        $articleImage = $this->modelManager->getRepository(Image::class)->findOneBy(['media' => $media->getId()]);

        $this->doWeirdShopwareArticleImageParentStuff($media, $detail, $articleImage->getPosition(), $articleImage->getId());

        $this->modelManager->getDBALQueryBuilder()->insert('s_article_img_mappings')
            ->values([
                'image_id' => ':image_id',
            ])
            ->setParameters([
                'image_id' => $articleImage->getId()
            ])->execute();

        /** @var Image\Mapping $articleImageMapping */
        $articleImageMapping = $this->modelManager->getRepository(Image\Mapping::class)->findOneBy(['image' => $articleImage->getId()]);

        /** @var Option[] $options */
        $options = $detail->getConfiguratorOptions();
        foreach ($options as $option) {
            $this->modelManager->getDBALQueryBuilder()->insert('s_article_img_mapping_rules')
                ->values([
                    'mapping_id' => ':mapping_id',
                    'option_id' => ':option_id',
                ])
                ->setParameters([
                    'mapping_id' => $articleImageMapping->getId(),
                    'option_id' => $option->getId(),
                ])
                ->execute();
        }
    }

    private function doWeirdShopwareArticleImageParentStuff(Media $media, Detail $detail, int $position, int $parentId): void
    {
        $this->modelManager->getDBALQueryBuilder()->insert('s_articles_img')
            ->values([
                'articleID' => ':articleID',
                'img' => ':img',
                'main' => ':main',
                'description' => ':description',
                'position' => ':position',
                'width' => ':width',
                'height' => ':height',
                'relations' => ':relations',
                'extension' => ':extension',
                'parent_id' => ':parent_id',
                'article_detail_id' => ':article_detail_id',
                'media_id' => ':media_id',
            ])->setParameters([
                'articleID' => null,
                'img' => null,
                'main' => '2',
                'description' => $media->getDescription(),
                'position' => $position,
                'width' => 0,
                'height' => 0,
                'relations' => '',
                'extension' => $media->getExtension(),
                'parent_id' => $parentId,
                'article_detail_id' => $detail->getId(),
                'media_id' => null,
            ])->execute();
    }

    /**
     * @param string $imagePath
     * @param int $articleId
     * @param bool $isMain
     * @param int $position
     *
     * @return Media
     * @throws ValidationException
     *
     */
    private function importMedia(string $imagePath, int $articleId, bool $isMain, int $position): Media
    {
        $params = [
            'album' => -1,
            'file' => $imagePath,
            'description' => ' '
        ];

        $media = $this->mediaApi->create($params);

        $this->modelManager->getDBALQueryBuilder()->insert('s_articles_img')
            ->values([
                'articleID' => ':articleID',
                'img' => ':img',
                'main' => ':main',
                'description' => ':description',
                'position' => ':position',
                'width' => ':width',
                'height' => ':height',
                'relations' => ':relations',
                'extension' => ':extension',
                'parent_id' => ':parent_id',
                'article_detail_id' => ':article_detail_id',
                'media_id' => ':media_id',
            ])->setParameters([
                'articleID' => $articleId,
                'img' => $media->getName(),
                'main' => $isMain ? '1' : '2',
                'description' => $media->getDescription(),
                'position' => $position,
                'width' => 0,
                'height' => 0,
                'relations' => '',
                'extension' => $media->getExtension(),
                'parent_id' => null,
                'article_detail_id' => null,
                'media_id' => $media->getId(),
            ])->execute();

        return $media;
    }
}
