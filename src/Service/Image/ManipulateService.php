<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Image;

use GibsonOS\Core\Dto\Image;
use GibsonOS\Core\Exception\Image\CreateError;

class ManipulateService extends DrawService
{
    /**
     * @param Image $image
     * @param int   $width
     * @param int   $height
     *
     * @throws CreateError
     *
     * @return bool
     */
    public function resize(Image $image, int $width, int $height): bool
    {
        // Wenn das bild breiter als hoch ist
        if ($this->getWidth($image) > $this->getHeight($image)) {
            $newHeight = ($this->getHeight($image) / $this->getWidth($image)) * $width;
            $newWidth = $width;

            /* Höhe passt nicht in die übergebenen maße
               Beispiel: Bild: w=400, h=200
                         Neues Format: w=200, h=50
                         Ergibt oben: new w=200, new h=100
                         Passt nicht in die übergebene Maße
                         deswegen: new w=100; new h=50
            */
            if ($newHeight > $height) {
                $newWidth = ($width / $newHeight) * $height;
                $newHeight = $height;
            }
        } else {
            // Wenn das Bild höher als breit ist
            $newWidth = ($this->getWidth($image) / $this->getHeight($image)) * $height;
            $newHeight = $height;

            if ($newWidth > $width) {
                $newHeight = ($height / $newWidth) * $width;
                $newWidth = $width;
            }
        }

        $width = (int) $newWidth;
        $height = (int) $newHeight;

        $newImage = $this->create($width, $height);

        if (
            !imagecopyresampled(
                $newImage->getResource(),
                $image->getResource(),
                0,
                0,
                0,
                0,
                $width,
                $height,
                $this->getWidth($image),
                $this->getHeight($image)
            )
        ) {
            return false;
        }

        $this->destroy($image);
        $image->setResource($newImage->getResource());

        return true;
    }

    /**
     * @param Image $image
     * @param int   $width
     * @param int   $height
     *
     * @throws CreateError
     *
     * @return bool
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
     * @param Image $image
     * @param int   $width
     * @param int   $height
     *
     * @throws CreateError
     *
     * @return Image
     */
    public function verticalCentered(Image $image, int $width, int $height): Image
    {
        $manipulate = $this->create($width, $height);
        $this->copy(
            $image,
            $manipulate,
            0,
            ($height - $this->getHeight($image)) / 2
        );

        return $manipulate;
    }

    /**
     * @param Image $image
     * @param int   $width
     * @param int   $height
     *
     * @throws CreateError
     *
     * @return bool
     */
    public function horizontalCentered(Image $image, int $width, int $height): bool
    {
        $manipulate = $this->create($width, $height);

        if ($this->copy(
            $image,
            $manipulate,
            ($width - $this->getWidth($image)) / 2
        ) === false) {
            return false;
        }

        $image->setResource($manipulate->getResource());

        return true;
    }

    /**
     * @param Image    $image
     * @param int      $width
     * @param int      $height
     * @param int|null $startX
     * @param int|null $startY
     * @param int|null $color
     *
     * @throws CreateError
     *
     * @return bool
     */
    public function crop(
        Image $image,
        int $width,
        int $height,
        int $startX = null,
        int $startY = null,
        int $color = null
    ): bool {
        if ($startX === null) {
            $startX = ($this->getWidth($image) - $width) / 2;
        }
        if ($startY === null) {
            $startY = ($this->getHeight($image) - $height) / 2;
        }

        $draw = $this->create($width, $height);

        if (!empty($color)) {
            $this->filledRectangle($draw, $color);
        }

        if (imagecopyresampled(
            $draw->getResource(),
            $image->getResource(),
            0,
            0,
            $startX,
            $startY,
            $width,
            $height,
            $width,
            $height
        ) === false) {
            return false;
        }

        $image->setResource($draw->getResource());

        return true;
    }

    /**
     * @param Image    $image
     * @param int      $width
     * @param int      $height
     * @param int|null $startX
     * @param int|null $startY
     *
     * @throws CreateError
     *
     * @return bool
     */
    public function cropResized(Image $image, int $width, int $height, int $startX = null, int $startY = null): bool
    {
        if ($this->getWidth($image) > $this->getHeight($image)) {
            $newWidth = ($this->getWidth($image) / $this->getHeight($image)) * $height;
            $newHeight = $height;

            if ($newWidth < $width) {
                $newHeight = ($height / $newWidth) * $width;
                $newWidth = $width;
            }
        } else {
            $newHeight = ($this->getHeight($image) / $this->getWidth($image)) * $width;
            $newWidth = $width;

            if ($newHeight < $height) {
                $newWidth = ($width / $newHeight) * $height;
                $newHeight = $height;
            }
        }

        if ($this->resize($image, (int) $newWidth, (int) $newHeight)) {
            if ($this->crop($image, $width, $height, $startX, $startY)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Image $sourceImage
     * @param Image $destinationImage
     * @param int   $destX
     * @param int   $destY
     * @param int   $srcX
     * @param int   $srcY
     * @param int   $srcWidth
     * @param int   $srcHeight
     * @param int   $dstWidth
     * @param int   $dstHeight
     *
     * @return bool
     */
    public function copy(
        Image $sourceImage,
        Image $destinationImage,
        int $destX = 0,
        int $destY = 0,
        int $srcX = 0,
        int $srcY = 0,
        int $srcWidth = -1,
        int $srcHeight = -1,
        int $dstWidth = -1,
        int $dstHeight = -1
    ): bool {
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

        return imagecopyresampled(
            $destinationImage->getResource(),
            $sourceImage->getResource(),
            $destX,
            $destY,
            $srcX,
            $srcY,
            $dstWidth,
            $dstHeight,
            $srcWidth,
            $srcHeight
        );
    }
}
