<?php declare(strict_types=1);

namespace OstMediaConnector\Commands;

use Shopware\Commands\ShopwareCommand;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Configurator\Option;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Image;
use Shopware\Models\Media\Media;
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
        $this->setName('ost-media-connector:sync-images');
    }


    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $articleNumberImageMap = $this->getNumberImageMap();
        $this->mediaApi = $this->container->get('shopware.api.media');
        $this->mediaApi->setManager($this->modelManager);

        $qb = $this->container->get('dbal_connection')->createQueryBuilder();
        $qb->select('id')
            ->from('s_articles');
        $articleIds = $qb->execute()->fetchAll(\PDO::FETCH_ASSOC);

        $progressBar = new ProgressBar($output, \count($articleIds));
        $progressBar->start();

        foreach ($articleIds as $articleId) {
            $articleId = $articleId['id'];

            /** @var Article|null $article */
            $article = $this->modelManager->find(Article::class, $articleId);
            if ($article === null) {
                continue; // Invalid Article
            }

            $mainDetail = $article->getMainDetail();
            if ($mainDetail === null) {
                continue; // Invalid Article
            }

            /** @var Detail $detail */
            foreach ($article->getDetails() as $detail) {
                /*
                 * Check if there are Images on PIM Media for the article.
                 * If not found delete all s_articles_img entries for the article.
                 */
                if (!isset($articleNumberImageMap[$detail->getNumber()])) {
                    if ($detail->getImages()->isEmpty() && $article->getImages()->isEmpty()) {
                        continue;
                    }

                    $output->writeln('Removing all Images from ' . $detail->getNumber());

                    $qb->delete('s_articles_img')
                        ->andWhere('articleID = :articleID')
                        ->setParameter(':articleID', (int) $articleId)
                        ->execute();


                    $qb->delete('s_articles_img')
                        ->andWhere('article_detail_id = :article_detail_id')
                        ->setParameter(':article_detail_id', (int) $detail->getId())
                        ->execute();
                    continue;
                }

                $pimImages = $articleNumberImageMap[$detail->getNumber()];
                $articleImages = $article->getImages();

                /*
                 * Iterate over all Images and remove images that arent existing anymore
                 */
                foreach ($article->getImages()->toArray() as $pos => $image) {
                    if (!isset($pimImages[$pos])) {
                        $output->writeln('Removing ' . $detail->getNumber() . ' - Image ' . $pos);

                        $qb->delete('s_articles_img')
                            ->where('position = :position')
                            ->andWhere('articleID = :articleID')
                            ->setParameter(':position', $pos)
                            ->setParameter(':articleID', (int) $articleId)
                            ->execute();

                        $qb->delete('s_articles_img')
                            ->where('position = :position')
                            ->andWhere('article_detail_id = :article_detail_id')
                            ->setParameter(':position', $pos)
                            ->setParameter(':article_detail_id', (int) $detail->getId())
                            ->execute();
                    }
                }

                foreach ($pimImages as $imageIndex => $pimImage) {
                    if (!$articleImages->isEmpty() && $articleImages->containsKey($imageIndex)) {
                        /** @var Image $articleImage */
                        $articleImage = $articleImages->get($imageIndex);

                        if ($articleImage->getMedia()->getFileSize() === filesize($pimImage)) {
                            // Nothing changed. Can continue
                            continue;
                        }

                        $qb->delete('s_articles_img')
                            ->where('position = :position')
                            ->andWhere('articleID = :articleID')
                            ->setParameter(':position', $imageIndex + 1)
                            ->setParameter(':articleID', (int) $articleId)
                            ->execute();

                        $qb->delete('s_articles_img')
                            ->where('position = :position')
                            ->andWhere('article_detail_id = :article_detail_id')
                            ->setParameter(':position', $imageIndex + 1)
                            ->setParameter(':article_detail_id', (int) $detail->getId())
                            ->execute();
                    }

                    if (!$detail->getConfiguratorOptions()->isEmpty()) {
                        if ($detail->getId() === $mainDetail->getId()) {
                            $isMain = true;
                        } else {
                            $isMain = false;
                        }
                    } else {
                        $isMain = ($imageIndex === 1);
                    }

                    //TODO: Import new Image
                    $output->writeln('Would import ' . $detail->getNumber() . ' - Image ' . ($imageIndex + 1));
                    $this->importImage($pimImage, $detail, $isMain, $imageIndex + 1);
                }
            }

            /* @noinspection DisconnectedForeachInstructionInspection */
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->modelManager->flush();
    }

    private function importImage(string $imagePath, Detail $detail, bool $isMain, int $position)
    {
        $media = $this->importMedia($imagePath, $detail->getArticleId(), $isMain, $position);

        if (!$isMain && !$detail->getConfiguratorOptions()->isEmpty()) {
            $this->createImageRules($media, $detail);
        }
    }

    private function getNumberImageMap()
    {
        $imageFolders = explode(',', $this->config['imageFolders']);
        $variantFolderName = $this->config['variantFolderName'];
        $baseFolder = $this->config['baseFolder'];

        $numberToImages = [];

        foreach ($imageFolders as $imageNumber => $imageFolder) {
            $path = $baseFolder . DIRECTORY_SEPARATOR . $imageFolder;

            if ($handle = opendir($path)) {
                while (($entry = readdir($handle)) !== false) {
                    if ($entry !== '.' && $entry !== '..') {
                        $articleNumber = ltrim(pathinfo($entry, PATHINFO_FILENAME), '0');
                        $numberToImages[$articleNumber][$imageNumber] = $path . DIRECTORY_SEPARATOR . $entry;
                    }
                }

                closedir($handle);
            }
        }

        $path = $baseFolder . DIRECTORY_SEPARATOR . $variantFolderName;
        $re = '/0+(\d+)-0+(\d+)_b0+(\d+)/';
        if ($handle = opendir($path)) {
            while (($entry = readdir($handle)) !== false) {
                if ($entry !== '.' && $entry !== '..') {
                    preg_match($re, pathinfo($entry, PATHINFO_FILENAME), $matches);

                    if (count($matches) === 0) {
                        continue;
                    }

                    $articleNumber = $matches[1] . '-' . $matches[2];
                    $imageNumber = ((int) $matches[3]) - 1;

                    $numberToImages[$articleNumber][$imageNumber] = $path . DIRECTORY_SEPARATOR . $entry;
                }
            }

            closedir($handle);
        }

        foreach ($numberToImages as $number => &$images) {
            $keys = array_keys($images);
            for ($i = 0, $iMax = count($images); $i < $iMax; ++$i) {
                if (!isset($keys[$i]) || $i !== $keys[$i]) {
                    array_splice($images, 0, $i);
                }
            }
        }

        return $numberToImages;
    }

    /**
     * @param Media  $media
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
                    'option_id'  => ':option_id',
                ])
                ->setParameters([
                    'mapping_id' => $articleImageMapping->getId(),
                    'option_id'  => $option->getId(),
                ])
                ->execute();
        }
    }

    private function doWeirdShopwareArticleImageParentStuff(Media $media, Detail $detail, int $position, int $parentId): void
    {
        $this->modelManager->getDBALQueryBuilder()->insert('s_articles_img')
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
                'articleID'         => null,
                'img'               => null,
                'main'              => '2',
                'description'       => $media->getDescription(),
                'position'          => $position,
                'width'             => 0,
                'height'            => 0,
                'relations'         => '',
                'extension'         => $media->getExtension(),
                'parent_id'         => $parentId,
                'article_detail_id' => $detail->getId(),
                'media_id'          => null,
            ])->execute();
    }

    /**
     * @param string $imagePath
     * @param int    $articleId
     * @param bool   $isMain
     * @param int    $position
     *
     * @throws ValidationException
     *
     * @return Media
     */
    private function importMedia(string $imagePath, int $articleId, bool $isMain, int $position): Media
    {
        $params = [
            'album'       => -1,
            'file'        => $imagePath,
            'description' => ' '
        ];

        $media = $this->mediaApi->create($params);

        $this->modelManager->getDBALQueryBuilder()->insert('s_articles_img')
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
                'articleID'         => $articleId,
                'img'               => $media->getName(),
                'main'              => $isMain ? '1' : '2',
                'description'       => $media->getDescription(),
                'position'          => $position,
                'width'             => 0,
                'height'            => 0,
                'relations'         => '',
                'extension'         => $media->getExtension(),
                'parent_id'         => null,
                'article_detail_id' => null,
                'media_id'          => $media->getId(),
            ])->execute();

        return $media;
    }
}
