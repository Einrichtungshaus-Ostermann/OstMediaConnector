<?php declare(strict_types=1);

namespace OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\Worker;

class ImageWorker implements MediaWorker
{
    public function getFileSize(string $path): int
    {
        return filesize($path);
    }



    /**
     * @param string $imagePath
     *
     * @return null|resource
     */
    public function getBinaryImage(string $imagePath)
    {
        $fileType = strtoupper(pathinfo($imagePath, PATHINFO_EXTENSION));

        switch ($fileType) {
            case 'JPG':
            case 'JPEG':
                return imagecreatefromjpeg($imagePath);
                break;

            case'PNG':
                return imagecreatefrompng($imagePath);
                break;

            case'GIF':
                return imagecreatefromgif($imagePath);
                break;

            default:
                return null;
        }
    }

    public function getResolution(string $path): array
    {
        return getimagesize($path);
    }

    /**
     * @param string $imagePath
     * @param null $requestedWidth
     *
     * @return resource|null
     */
    public function getResizedBinary(string $imagePath, $requestedWidth = null)
    {
        $binaryImage = $this->getBinaryImage($imagePath);

        if ($binaryImage === null) {
            return null;
        }

        //Size Calculation
        $sourceX = 0;
        $sourceY = 0;

        list($originalWidth, $originalHeight) = $this->getResolution($imagePath);

        if ($requestedWidth === null) {
            $requestedWidth = $originalWidth;
        }

        if ($requestedWidth > $originalWidth) {
            $requestedWidth = $originalWidth;
        }

        /*if ($originalWidth > $originalHeight) {
            $sourceX = ($originalWidth - $originalHeight) / 2;
        } else {
            $sourceY = ($originalHeight - $originalWidth) / 2;
        }*/

        $newWidth = $requestedWidth;
        $newHeight = ($originalHeight / $originalWidth) * $newWidth;


        //Image Generation
        $newBinaryImage = imagecreatetruecolor($newWidth, $newHeight);

        imagesavealpha($newBinaryImage, true);

        $trans_colour = imagecolorallocate($newBinaryImage, 0, 0, 0);
        imagefill($newBinaryImage, 0, 0, $trans_colour);

        imagecopyresampled($newBinaryImage, $binaryImage, 0, 0, $sourceX, $sourceY, $newWidth, $newHeight, $originalWidth, $originalHeight);

        return $newBinaryImage;
    }

    public function getResized(string $path, int $width)
    {
        return $this->getResizedBinary($path, $width);
    }
}
