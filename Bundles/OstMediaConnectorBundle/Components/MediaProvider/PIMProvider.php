<?php declare(strict_types=1);

namespace OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\MediaProvider;

class PIMProvider implements MediaProvider
{
    /**
     * @var string[]
     */
    private $imageFolders;
    /**
     * @var string
     */
    private $imageServerPath;
    /**
     * @var string
     */
    private $variantFolderName;
    /**
     * @var string[]
     */
    private $variantImageNames;

    /**
     * @param string $ordernumber
     *
     * @return array
     */
    public function getAll(string $ordernumber): array
    {
        $images = [];
        for ($i = 0; $i < $this->count($ordernumber); ++$i) {
            $images[] = $this->get($ordernumber, $i);
        }

        return $images;
    }

    /**
     * @param string $ordernumber
     *
     * @return int
     */
    public function count(string $ordernumber): int
    {
        $count = 0;

        if ($this->isVariantArticle($ordernumber)) {
            $iteratorArray = $this->variantImageNames;
        } else {
            $iteratorArray = $this->variantImageNames;
        }

        foreach ($iteratorArray as $key => $item) {
            if ($this->fileExist($this->getFilePath($ordernumber, $key))) {
                ++$count;
            } else {
                break;
            }
        }

        return $count;
    }

    /**
     * Returns a Media for an Ordernumber
     *
     * @param string $ordernumber
     * @param int $imageNumber
     *
     * @return string|null
     */
    public function get(string $ordernumber, int $imageNumber = 0)
    {
        if ($imageNumber > \count($this->imageFolders)) {
            return null;
        }

        $path = $this->getFilePath($ordernumber, $imageNumber);

        if ($this->fileExist($path)) {
            return $path;
        }

        return null;
    }

    public function getName(): string
    {
        return 'PIM Media Provider';
    }

    public function getConfigParameter(): array
    {
        return [
            [
                'name'    => 'imageFolders',
                'type'    => 'string',
                'default' => '',
            ],
            [
                'name'    => 'variantImageNames',
                'type'    => 'string',
                'default' => '',
            ],
            [
                'name'    => 'variantFolderName',
                'type'    => 'string',
                'default' => '',
            ],
            [
                'name'    => 'imageServerPath',
                'type'    => 'string',
                'default' => '',
            ]
        ];
    }

    public function getConfig(): array
    {
        return [
            'imageFolders'      => $this->imageFolders,
            'imageServerPath'   => $this->imageServerPath,
            'variantImageNames' => $this->variantImageNames,
            'variantFolderName' => $this->variantFolderName,
        ];
    }

    public function setConfig(array $config)
    {
        if (\is_array($config['imageFolders'])) {
            $this->imageFolders = $config['imageFolders'];
        } else {
            preg_match_all('/"(.+?)"/', $config['imageFolders'], $matches);
            $this->imageFolders = $matches[1];
        }

        if (\is_array($config['variantImageNames'])) {
            $this->variantImageNames = $config['variantImageNames'];
        } else {
            preg_match_all('/"(.+?)"/', $config['variantImageNames'], $matches);
            $this->variantImageNames = $matches[1];
        }

        $this->imageServerPath = $config['imageServerPath'];
        $this->variantFolderName = $config['variantFolderName'];
    }



    private function isVariantArticle(string $ordernumber)
    {
        return strpos($ordernumber, '-') !== false;
    }



    private function getVariantFileName(string $ordernumber, string $imageName)
    {
        list($ordernumber, $variantNumber) = explode('-', $ordernumber);

        return $this->imageServerPath . '/' . $this->variantFolderName .
            '/' . $this->padOrdernumber($ordernumber) . '-' . $this->padVariantNumber($variantNumber) . '_' . $imageName . '.' . 'jpg';
    }



    private function getFilePath(string $ordernumber, int $imageNumber)
    {
        if ($this->isVariantArticle($ordernumber)) {
            return $this->getVariantFileName($ordernumber, $this->variantImageNames[$imageNumber]);
        }

        return $this->imageServerPath . '/' . $this->imageFolders[$imageNumber] . '/' . $this->padOrdernumber($ordernumber) . '.' . 'jpg';
    }



    /**
     * @param string $variantNumber
     *
     * @return string
     */
    private function padOrdernumber(string $variantNumber): string
    {
        return str_pad($variantNumber, 7, '0', STR_PAD_LEFT);
    }



    /**
     * @param string $variantNumber
     *
     * @return string
     */
    private function padVariantNumber(string $variantNumber): string
    {
        return str_pad($variantNumber, 5, '0', STR_PAD_LEFT);
    }



    /**
     * @param $path
     *
     * @return bool
     */
    private function fileExist($path): bool
    {
        $headers = @get_headers($path)[0];

        return strpos($headers, '404') === false;
    }
}
