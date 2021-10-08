<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto;

class Image
{
    private string $filename;

    private int $quality = 80;

    /**
     * @param resource $resource
     */
    public function __construct(private $resource)
    {
    }

    /**
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param resource $resource
     */
    public function setResource($resource): Image
    {
        $this->resource = $resource;

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
