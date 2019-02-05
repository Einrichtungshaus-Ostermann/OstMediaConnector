<?php declare(strict_types=1);
/**
 * Einrichtungshaus Ostermann GmbH & Co. KG - Media Connector
 *
 * Import Images from external Servers
 *
 * 1.0.0
 * - initial release
 *
 * @package   OstMediaConnector
 *
 * @author    Tim Windelschmidt <tim.windelschmidt@ostermann.de>
 * @copyright 2018 Einrichtungshaus Ostermann GmbH & Co. KG
 * @license   proprietary
 */

namespace OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\MediaProvider;

/**
 * Class MediaProviderListProvider
 *
 * @package OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\MediaProvider
 */
class MediaProviderListProvider implements MediaProviderList
{
    /**
     * @var MediaProvider[]
     */
    private $mediaProvider;



    /**
     * @var MediaProvider[]
     */
    private $configuredMediaProvider;



    /**
     * MediaProviderListProvider constructor.
     *
     * @param MediaProvider[] $mediaProvider
     */
    public function __construct(array $mediaProvider)
    {
        $sortedProvider = [];

        foreach ($mediaProvider as $provider) {
            $sortedProvider[$provider->getName()] = $provider;
        }

        $this->mediaProvider = $sortedProvider;

        /** @varOstMediaConnector\Models\MediaProvider[] $mediaProviderModelArray */
        $mediaProviderModelArray = Shopware()->Models()->getRepository(\OstMediaConnector\Models\MediaProvider::class)->findAll();

        $dataArray = [];
        foreach ($mediaProviderModelArray as $mediaProviderModel) {
            if ($mediaProviderModel === null) {
                continue;
            }

            $currentMediaProvider = $sortedProvider[$mediaProviderModel->getProviderName()];

            if ($currentMediaProvider === null) {
                continue;
            }

            $currentOstMediaConnectorConfig = json_decode($mediaProviderModel->getConfig(), true) ?? [];

            $dataArray[$mediaProviderModel->getPriority()] = [
                'class'  => \get_class($currentMediaProvider),
                'name'   => $currentMediaProvider->getName(),
                'config' => $currentOstMediaConnectorConfig,
                'query'  => $mediaProviderModel->getQuery()
            ];
        }

        \ksort($dataArray);


        $this->configuredMediaProvider = $dataArray;
    }



    /**
     * @return MediaProvider[]
     */
    public function getMediaProviderList(): array
    {
        return $this->mediaProvider;
    }



    /**
     * @param string $ordernumber
     *
     * @return MediaProvider[]
     */
    public function getConfiguredProviderList(string $ordernumber): array
    {
        $providerList = [];
        foreach ($this->configuredMediaProvider as $priority => $data) {
            if ($this->processQuery($ordernumber, $data['query'] === '' ? 'true' : $data['query'])) {
                $mediaProvider = $this->mediaProvider[$data['name']];

                if ($mediaProvider === null) {
                    continue;
                }

                $providerInstance = $this->createConfiguredInstance($mediaProvider, $data['config']);

                if ($providerInstance->count($ordernumber) === 0) {
                    continue;
                }

                $providerList[] = $providerInstance;
            }
        }

        return $providerList;
    }



    /**
     * @param string $ordernumber
     * @param string $query
     *
     * @return bool
     */
    private function processQuery(string $ordernumber, string $query): bool
    {
        $detail = Shopware()->Models()->getDBALQueryBuilder()
            ->select('*')
            ->from('s_articles_details')
            ->where('ordernumber = :number')
            ->setParameter(':number', $ordernumber)->execute()->fetch(\PDO::FETCH_ASSOC);

        if ($detail === null) {
            return false;
        }

        $detailAttributes = Shopware()->Models()->getDBALQueryBuilder()
            ->select('*')
            ->from('s_articles_attributes')
            ->where('articledetailsID = :detailsID')
            ->setParameter(':detailsID', $detail['id'])->execute()->fetch(\PDO::FETCH_ASSOC);

        $article = Shopware()->Models()->getDBALQueryBuilder()
            ->select('*')
            ->from('s_articles')
            ->where('id = :id')
            ->setParameter(':id', $detail['articleID'])->execute()->fetch(\PDO::FETCH_ASSOC);

        $articleAttributes = Shopware()->Models()->getDBALQueryBuilder()
            ->select('*')
            ->from('s_articles_attributes')
            ->where('articleId = :articleID')
            ->setParameter(':articleID', $article['id'])->execute()->fetch(\PDO::FETCH_ASSOC);

        $data = [];
        $data['article'] = $article;
        $data['article']['attributes'] = $articleAttributes;

        $data['article']['detail'] = $detail;
        $data['article']['detail']['attributes'] = $detailAttributes;

        $bracketRegex = '/\(((?:[a-zA-Z0-9.=! ]+)+)\)/';
        preg_match_all($bracketRegex, $query, $bracketMatches, PREG_SET_ORDER, 0);

        foreach ($bracketMatches as $bracketMatch) {
            $subQueryResult = $this->processQuery($ordernumber, $bracketMatch[1]) ? 'true' : 'false';

            $query = str_replace($bracketMatch[1], $subQueryResult, $query);
        }

        $dataRegex = '/(?:\n|^|\(|and\s|or\s)((?:[A-za-z0-9]*?\.{0,1}+)+)\s/';
        preg_match_all($dataRegex, $query, $dataMatches, PREG_SET_ORDER, 0);

        foreach ($dataMatches as $dataMatch) {
            $queryData = $this->getFromDot($data, $dataMatch[1]);

            if (!is_numeric($queryData)) {
                $queryData = '\'' . $queryData . '\'';
            }

            $query = str_replace($dataMatch[1], $queryData, $query);
        }

        $query = str_replace([' or ', ' and '], [' || ', ' && '], $query);

        // Evil eval coming
        /** @noinspection OneTimeUseVariablesInspection */
        $evalReturn = eval('return (' . $query . ');');
        // I hate my life :(

        return $evalReturn;
    }



    /**
     * @param $array
     * @param $key
     *
     * @return mixed|null
     */
    private function getFromDot($array, $key)
    {
        if (!\is_array($array)) {
            return null;
        }

        if ($key === null) {
            return $array;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        if (strpos($key, '.') === false) {
            return $array[$key] ?? null;
        }

        foreach (explode('.', $key) as $segment) {
            if (\is_array($array) && ($array !== null && array_key_exists($segment, $array))) {
                $array = $array[$segment];
            } else {
                return null;
            }
        }

        return $array;
    }



    /**
     * @param MediaProvider $mediaProvider
     * @param array $config
     *
     * @return MediaProvider
     */
    private function createConfiguredInstance(MediaProvider $mediaProvider, array $config): MediaProvider
    {
        $mediaProviderClone = clone $mediaProvider;
        $mediaProviderClone->setConfig($config);

        return $mediaProviderClone;
    }
}
