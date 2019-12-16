<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Image;

use GibsonOS\Core\Dto\Image;
use GibsonOS\Core\Exception\Image\CreateError;

/**
 * @deprecated
 *
 * @package GibsonOS\Core\Service\Image
 */
class ThumbnailService extends ManipulateService
{
    private const POSITIONS = [
        16 => 144,
        32 => 112,
        48 => 64,
        64 => 0,
        128 => 160,
        256 => 288,
    ];

    /**
     * @throws CreateError
     */
    public function generate(Image $image, int $width = 544, int $height = 256): Image
    {
        /** @var Image[] $images */
        $images = [];

        foreach (self::POSITIONS as $size => $position) {
            $imageClone = $this->cloneImage($image);
            $this->resizeCentered($imageClone, $size, $size);
            $images[$size] = $imageClone;
        }

        $thumbnail = $this->create($width, $height);
        $this->fillTransparent($thumbnail);

        foreach (self::POSITIONS as $size => $position) {
            $this->copy($images[$size], $thumbnail, $position);
        }

        return $thumbnail;
    }
}
