<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Image;

use GibsonOS\Core\Dto\Image;
use GibsonOS\Core\Enum\Image\Axis;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\Image\CreateError;
use GibsonOS\Core\Exception\Image\LoadError;
use Override;

class ManipulateService extends DrawService
{
    public function resize(Image $image, int $width, int $height): bool
    {
        // Wenn das bild breiter als hoch ist
        if ($this->getWidth($image) > $this->getHeight($image)) {
            $newHeight = (int) ($this->getHeight($image) / $this->getWidth($image)) * $width;
            $newWidth = $width;

            /* Höhe passt nicht in die übergebenen maße
               Beispiel: Bild: w=400, h=200
                         Neues Format: w=200, h=50
                         Ergibt oben: new w=200, new h=100
                         Passt nicht in die übergebene Maße
                         deswegen: new w=100; new h=50
            */
            if ($newHeight > $height) {
                $newWidth = (int) ($width / $newHeight) * $height;
                $newHeight = $height;
            }
        } else {
            // Wenn das Bild höher als breit ist
            $newWidth = (int) ($this->getWidth($image) / $this->getHeight($image)) * $height;
            $newHeight = $height;

            if ($newWidth > $width) {
                $newHeight = (int) ($height / $newWidth) * $width;
                $newWidth = $width;
            }
        }

        $width = (int) $newWidth;
        $height = (int) $newHeight;

        $newImage = imagescale($image->getImage(), $width, $height);

        if ($newImage === false) {
            return false;
        }

        $this->destroy($image);
        $image->setImage($newImage);

        return true;
    }

    /**
     * @throws CreateError
     */
    public function resizeCentered(Image $image, int $width, int $height): bool
    {
        if (!$this->resize($image, $width, $height)) {
            return false;
        }

        if ($this->getWidth($image) < $width) {
            $this->horizontalCentered($image, $width, $height);
        } elseif ($this->getHeight($image) < $height) {
            $this->verticalCentered($image, $width, $height);
        }

        return true;
    }

    /**
     * @throws CreateError
     */
    public function verticalCentered(Image $image, int $width, int $height): bool
    {
        $manipulate = $this->create($width, $height);
        $this->fillTransparent($manipulate);

        if ($this->copy($image, $manipulate, 0, (int) (($height - $this->getHeight($image)) / 2)) === false) {
            return false;
        }

        $image->setImage($manipulate->getImage());

        return true;
    }

    /**
     * @throws CreateError
     */
    public function horizontalCentered(Image $image, int $width, int $height): bool
    {
        $manipulate = $this->create($width, $height);
        $this->fillTransparent($manipulate);

        if ($this->copy($image, $manipulate, (int) (($width - $this->getWidth($image)) / 2)) === false) {
            return false;
        }

        $image->setImage($manipulate->getImage());

        return true;
    }

    /**
     * @throws CreateError
     */
    public function crop(Image $image, int $width, int $height, ?int $startX = null, ?int $startY = null, ?int $color = null): bool
    {
        if ($startX === null) {
            $startX = (int) (($this->getWidth($image) - $width) / 2);
        }
        if ($startY === null) {
            $startY = (int) (($this->getHeight($image) - $height) / 2);
        }

        $draw = $this->create($width, $height);

        if ($color !== null && $color !== 0) {
            $this->filledRectangle($draw, $color);
        }

        if (imagecopyresampled($draw->getImage(), $image->getImage(), 0, 0, $startX, $startY, $width, $height, $width, $height) === false) {
            return false;
        }

        $image->setImage($draw->getImage());

        return true;
    }

    /**
     * @throws CreateError
     */
    public function cropResized(Image $image, int $width, int $height, ?int $startX = null, ?int $startY = null): bool
    {
        if ($this->getWidth($image) > $this->getHeight($image)) {
            $newWidth = (int) ($this->getWidth($image) / $this->getHeight($image)) * $height;
            $newHeight = $height;

            if ($newWidth < $width) {
                $newHeight = (int) ($height / $newWidth) * $width;
                $newWidth = $width;
            }
        } else {
            $newHeight = (int) ($this->getHeight($image) / $this->getWidth($image)) * $width;
            $newWidth = $width;

            if ($newHeight < $height) {
                $newWidth = (int) ($width / $newHeight) * $height;
                $newHeight = $height;
            }
        }

        return $this->resize($image, $newWidth, $newHeight) && $this->crop($image, $width, $height, $startX, $startY);
    }

    public function copy(Image $sourceImage, Image $destinationImage, int $destX = 0, int $destY = 0, int $srcX = 0, int $srcY = 0, int $srcWidth = -1, int $srcHeight = -1, int $dstWidth = -1, int $dstHeight = -1): bool
    {
        if ($srcWidth === -1) {
            $srcWidth = $this->getWidth($sourceImage);
        }

        if ($srcHeight === -1) {
            $srcHeight = $this->getHeight($sourceImage);
        }

        if ($dstWidth === -1) {
            $dstWidth = $this->getWidth($sourceImage);
        }

        if ($dstHeight === -1) {
            $dstHeight = $this->getHeight($sourceImage);
        }

        return imagecopyresampled($destinationImage->getImage(), $sourceImage->getImage(), $destX, $destY, $srcX, $srcY, $dstWidth, $dstHeight, $srcWidth, $srcHeight);
    }

    /**
     * @throws CreateError
     */
    public function mirror(Image $image, Axis $axis): Image
    {
        $width = $this->getWidth($image);
        $height = $this->getHeight($image);
        $newImage = $this->create($width, $height);

        match ($axis) {
            Axis::HORIZONTAL => imagecopyresampled($newImage->getImage(), $image->getImage(), 0, 0, $width - 1, 0, $width, $height, -$width, $height),
            Axis::VERTICAL => imagecopyresampled($newImage->getImage(), $image->getImage(), 0, 0, 0, $height - 1, $width, $height, $width, -$height),
        };

        return $newImage;
    }

    /**
     * @throws CreateError
     */
    public function rotate(Image $image, float $angle, int $backgroundColor = 0): Image
    {
        return new Image(
            imagerotate($image->getImage(), $angle, $backgroundColor) ?: throw new CreateError('Cannot rotate image'),
        );
    }

    /**
     * @throws CreateError
     */
    public function setOrientationByExif(Image $image): Image
    {
        return match ($this->getExifKey($image, 'Orientation')) {
            2 => $this->mirror($image, Axis::HORIZONTAL),
            3 => $this->rotate($image, 180),
            4 => $this->rotate($this->mirror($image, Axis::HORIZONTAL), 180),
            5 => $this->rotate($this->mirror($image, Axis::VERTICAL), 270),
            6 => $this->rotate($image, 270),
            7 => $this->rotate($this->mirror($image, Axis::VERTICAL), 90),
            8 => $this->rotate($image, 90),
            default => $image,
        };
    }

    /**
     * @throws CreateError
     * @throws FileNotFound
     * @throws LoadError
     */
    #[Override]
    public function load(string $filename, ?string $type = null): Image
    {
        return $this->setOrientationByExif(parent::load($filename, $type));
    }
}
