<?php declare(strict_types=1);

namespace OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\MediaProvider;

class PIMLocalProvider extends PIMProvider
{

    public function getName(): string
    {
        return 'PIM Local Media Provider';
    }

    /**
     * @param $path
     *
     * @return bool
     */
    protected function fileExist($path): bool
    {
        return file_exists($path);
    }
}
