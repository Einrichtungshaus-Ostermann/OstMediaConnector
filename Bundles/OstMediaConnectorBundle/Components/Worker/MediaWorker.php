<?php declare(strict_types=1);

namespace OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\Worker;

interface MediaWorker
{
    public function getResolution(string $path): array;

    public function getResized(string $path, int $width);

    public function getFileSize(string $path): int;
}
