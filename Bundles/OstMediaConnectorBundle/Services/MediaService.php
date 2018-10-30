<?php declare(strict_types=1);

namespace OstMediaConnector\Bundles\OstMediaConnectorBundle\Services;

use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\Token\InvalidTokenException;
use OstMediaConnector\Bundles\OstMediaConnectorBundle\Services\Structs\Media;

/**
 * Interface MediaService
 *
 * @package OstMediaConnector\Bundles\OstMediaConnectorBundle\Services
 */
interface MediaService
{
    /**
     * Returns a Media for an Ordernumber
     *
     * @param string $ordernumber
     * @param int $imageNumber
     *
     * @return Media|null
     */
    public function getMedia(string $ordernumber, int $imageNumber = 0);



    /**
     * @param string $token
     *
     * @throws InvalidTokenException
     *
     * @return resource|null
     */
    public function getResource(string $token);



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



    /**
     * Returns you all MediaProvider Instances
     *
     * @return array
     */
    public function getAllMediaProvider(): array;
}
