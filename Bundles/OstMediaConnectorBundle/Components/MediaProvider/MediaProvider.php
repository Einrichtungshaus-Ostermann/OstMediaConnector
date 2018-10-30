<?php declare(strict_types=1);

namespace OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\MediaProvider;

interface MediaProvider
{
    /**
     * Returns a Media for an Ordernumber
     *
     * @param string $ordernumber
     * @param int $imageNumber
     *
     * @return string|null
     */
    public function get(string $ordernumber, int $imageNumber = 0);



    /**
     * @param string $ordernumber
     *
     * @return array
     */
    public function getAll(string $ordernumber): array;



    /**
     * @param string $ordernumber
     *
     * @return int
     */
    public function count(string $ordernumber): int;



    public function getName(): string;



    public function getConfigParameter(): array;



    public function getConfig(): array;



    public function setConfig(array $config);
}
