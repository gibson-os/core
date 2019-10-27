<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Image;

use GibsonOS\Core\Exception\SetError;

/**
 * @deprecated
 *
 * @package GibsonOS\Core\Service\Image
 */
class Thumbnail
{
    const POSITIONS = [
        16 => 144,
        32 => 112,
        48 => 64,
        64 => 0,
        128 => 160,
        256 => 288,
    ];

    /**
     * @var Manipulate
     */
    private $manipulate;

    /**
     * @param Manipulate $manipulate
     */
    public function __construct(Manipulate $manipulate)
    {
        $this->manipulate = $manipulate;
    }

    /**
     * @return Manipulate
     */
    public function getManipulate(): Manipulate
    {
        return $this->manipulate;
    }

    /**
     * @throws SetError
     */
    public function create()
    {
        /** @var Manipulate[] $images */
        $images = [];

        foreach (self::POSITIONS as $size => $position) {
            $image = clone $this->getManipulate();
            $image->resizeCentered($size, $size);
            $images[$size] = $image;
        }

        $this->manipulate->getImage()->create(544, 256);

        foreach (self::POSITIONS as $size => $position) {
            $this->manipulate->copy($images[$size]->getImage(), $position);
        }
    }

    public function __clone()
    {
        $this->manipulate = clone $this->manipulate;
    }
}
