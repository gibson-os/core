<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Image;

use GibsonOS\Core\Dto\Image as ImageDto;
use GibsonOS\Core\Service\ImageService;

class DrawService extends ImageService
{
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
     */
    public function setTfbBox(string $text, string $fontFile, int $size, int $angle = 0): array
    {
        return imagettfbbox($size, $angle, $fontFile, $text);
    }
}
