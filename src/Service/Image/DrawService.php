<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Image;

use GibsonOS\Core\Dto\Image as ImageDto;
use GibsonOS\Core\Service\ImageService;

class DrawService extends ImageService
{
    /**
     * @param ImageDto $image
     * @param int      $color
     * @param int      $startX
     * @param int      $startY
     * @param int      $stopX
     * @param int      $stopY
     *
     * @return bool
     */
    public function filledRectangle(
        ImageDto $image,
        int $color,
        int $startX = 0,
        int $startY = 0,
        int $stopX = -1,
        int $stopY = -1
    ): bool {
        if ($stopX === -1) {
            $stopX = $this->getWidth($image);
        }

        if ($stopY === -1) {
            $stopY = $this->getHeight($image);
        }

        return imagefilledrectangle($image->getResource(), $startX, $startY, $stopX, $stopY, $color);
    }

    /**
     * Schreibt einen Text in das Bild.
     *
     * @param ImageDto $image
     * @param string   $text
     * @param int      $color
     * @param string   $fontFile
     * @param int      $size
     * @param int      $startX
     * @param int      $startY
     * @param int      $angle
     *
     * @return array
     */
    public function setTtfText(
        ImageDto $image,
        string $text,
        int $color,
        string $fontFile,
        int $size,
        int $startX = 0,
        int $startY = 0,
        int $angle = 0
    ): array {
        if ($startY == 0) {
            $startY = $size;
        }

        return imagettftext($image->getResource(), $size, $angle, $startX, $startY, $color, $fontFile, $text);
    }

    /**
     * Schreibt einen Text in das Bild.
     *
     * @param string $text
     * @param string $fontFile
     * @param int    $size
     * @param int    $angle
     *
     * @return array
     */
    public function setTfbBox(string $text, string $fontFile, int $size, int $angle = 0): array
    {
        return imagettfbbox($size, $angle, $fontFile, $text);
    }
}
