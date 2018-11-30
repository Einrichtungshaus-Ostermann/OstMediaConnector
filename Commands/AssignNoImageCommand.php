<?php declare(strict_types=1);

namespace OstMediaConnector\Commands;

use Doctrine\Common\Collections\ArrayCollection;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Services\Structs\Media as OstMediaConnectorMedia;
use Shopware\Commands\ShopwareCommand;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Configurator\Option;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Image;
use Shopware\Models\Media\Media;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AssignNoImageCommand extends ShopwareCommand
{
    /** @var ModelManager */
    private $models;

    /** @var \Shopware\Components\Api\Resource\Media */
    private $mediaApi;

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
        $this->mediaApi = $this->container->get('shopware.api.media');
        $this->models = $this->container->get('models');
        $this->mediaApi->setManager($this->models);
        $liveImageService = $this->container->get('ost_media_connector.services.live_image_service');

        $qb = $this->container->get('dbal_connection')->createQueryBuilder();
        $qb->select('id')
            ->from('s_articles')
            ->where('id NOT IN (SELECT articleID FROM s_articles_img where articleID is not null)');
        $result = $qb->execute()->fetchAll(\PDO::FETCH_ASSOC);

        $progressBar = new ProgressBar($output, \count($result));
        $progressBar->start();

        foreach ($result as $articleRow) {
            $articleId = $articleRow['id'];

            /** @var Article|null $article */
            $article = $this->models->getRepository(Article::class)->findOneBy(['id' => $articleId]);

            if ($article === null) {
                continue;
            }

            $mainDetailId = $article->getMainDetail()->getId();
            $imagePosition = 1;
            /** @var Detail $detail */
            foreach ($article->getDetails() as $detailIndex => $detail) {

                /** @var OstMediaConnectorMedia[] $images */
                $images = $liveImageService->getAll($detail->getNumber());
                foreach ($images as $imageIndex => $image) {
                    if ($image === null) {
                        continue;
                    }

                    if (!$detail->getConfiguratorOptions()->isEmpty()) {
                        if ($detail->getId() === $mainDetailId) {
                            $isMain = true;
                        } else {
                            $isMain = false;
                        }
                    } else {
                        $isMain = ($imagePosition === 1);
                    }

                    $media = $this->importImage($image, $article->getId(), $isMain, $imagePosition);

                    if (!$detail->getConfiguratorOptions()->isEmpty() && $detail->getId() !== $mainDetailId) {
                        $this->createImageRules($media, $detail);
                    }

                    $imagePosition++;
                }
                $imagePosition++;
            }
            /** @noinspection DisconnectedForeachInstructionInspection */
            $progressBar->advance();
        }
        $progressBar->finish();
    }

    /**
     * @param Media $media
     * @param Detail $detail
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function createImageRules(Media $media, Detail $detail)
    {
        /** @var Image $articleImage */
        $articleImage = $this->models->getRepository(Image::class)->findOneBy(['media' => $media->getId()]);

        $this->doWeirdShopwareArticleImageParentStuff($media, $detail, $articleImage->getPosition(), $articleImage->getId());

        $this->models->getDBALQueryBuilder()->insert('s_article_img_mappings')
            ->values([
                'image_id' => ':image_id',
            ])
            ->setParameters([
                'image_id' => $articleImage->getId()
            ])->execute();


        /** @var Image\Mapping $articleImageMapping */
        $articleImageMapping = $this->models->getRepository(Image\Mapping::class)->findOneBy(['image' => $articleImage->getId()]);

        /** @var Image\Rule[] $rules */
        $rules = [];
        /** @var Option[] $options */
        $options = $detail->getConfiguratorOptions();
        foreach ($options as $option) {
            $rule = new Image\Rule();
            $rule->setOption($option);
            $rule->setMapping($articleImageMapping);

            $this->models->persist($rule);
            $this->models->flush($rule);
            $rules[] = $rule;
        }

        $articleImageMapping->setRules(new ArrayCollection($rules));
        $this->models->flush($articleImageMapping);
    }

    private function doWeirdShopwareArticleImageParentStuff(Media $media, Detail $detail, int $position, int $parentId)
    {
        $this->models->getDBALQueryBuilder()->insert('s_articles_img')
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
     * @param OstMediaConnectorMedia $image
     * @param int $articleId
     * @param int|null $detailId
     * @param bool $isMain
     * @param int $position
     * @return Media
     * @throws \Shopware\Components\Api\Exception\ValidationException
     */
    private function importImage(OstMediaConnectorMedia $image, int $articleId, bool $isMain, int $position): Media
    {
        $params = [
            'album' => -1,
            'file' => $image->getFile(),
            'description' => ' '
        ];

        $media = $this->mediaApi->create($params);

        $this->models->getDBALQueryBuilder()->insert('s_articles_img')
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
