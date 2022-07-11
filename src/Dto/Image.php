<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto;

use GdImage;

class Image
{
    private string $filename;

    private int $quality = 80;

    public function __construct(private GdImage $image)
    {
    }

    public function getImage(): GdImage
    {
        return $this->image;
    }

    public function setImage(GdImage $image): Image
    {
        $this->image = $image;

        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): Image
    {
        $this->filename = $filename;

        return $this;
    }

    public function getQuality(): int
    {
        return $this->quality;
    }

    public function setQuality(int $quality): Image
    {
        $this->quality = $quality;

        return $this;
    }
}
