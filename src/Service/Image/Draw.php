<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Image;

use GibsonOS\Core\Service\AbstractService;
use GibsonOS\Core\Service\Image;

class Draw extends AbstractService
{
    /**
     * @var Image
     */
    private $image;

    /**
     * @param Image $image
     */
    public function __construct(Image $image)
    {
        $this->image = $image;
    }

    /**
     * @param int $color
     * @param int $startX
     * @param int $startY
     * @param int $stopX
     * @param int $stopY
     *
     * @return bool
     */
    public function filledRectangle(
        int $color,
        int $startX = 0,
        int $startY = 0,
        int $stopX = -1,
        int $stopY = -1
    ): bool {
        if ($stopX === -1) {
            $stopX = $this->getImage()->getWidth();
        }

        if ($stopY === -1) {
            $stopY = $this->getImage()->getHeight();
        }

        return imagefilledrectangle($this->getImage()->getResource(), $startX, $startY, $stopX, $stopY, $color);
    }

    /**
     * Schreibt einen Text in das Bild.
     *
     * @param string $text
     * @param int    $color
     * @param string $fontfile
     * @param int    $size
     * @param int    $startX
     * @param int    $startY
     * @param int    $angle
     *
     * @return array
     */
    public function setTtfText($text, $color, $fontfile, $size, $startX = 0, $startY = 0, $angle = 0)
    {
        if ($startY == 0) {
            $startY = $size;
        }

        return imagettftext($this->getImage()->getResource(), $size, $angle, $startX, $startY, $color, $fontfile, $text);
    }

    /**
     * Schreibt einen Text in das Bild.
     *
     * @param string $text
     * @param string $fontfile
     * @param int    $size
     * @param int    $angle
     *
     * @return array
     */
    public function setTfbBox($text, $fontfile, $size, $angle = 0)
    {
        return imagettfbbox($size, $angle, $fontfile, $text);
    }

    /**
     * @return Image
     */
    public function getImage()
    {
        return $this->image;
    }

    public function __clone()
    {
        $this->image = clone $this->image;
    }
}
