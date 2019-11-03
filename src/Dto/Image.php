<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto;

class Image
{
    /**
     * @var resource
     */
    private $resource;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var int
     */
    private $quality = 80;

    /**
     * Image constructor.
     *
     * @param resource $resource
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
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
     *
     * @return Image
     */
    public function setResource($resource): Image
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     *
     * @return Image
     */
    public function setFilename(string $filename): Image
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuality(): int
    {
        return $this->quality;
    }

    /**
     * @param int $quality
     *
     * @return Image
     */
    public function setQuality(int $quality): Image
    {
        $this->quality = $quality;

        return $this;
    }
}
