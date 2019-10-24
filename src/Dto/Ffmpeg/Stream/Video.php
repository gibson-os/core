<?php
namespace GibsonOS\Core\Dto\Ffmpeg\Stream;

class Video
{
    /**
     * @var string|null
     */
    private $language = null;
    /**
     * @var string|null
     */
    private $codec;
    /**
     * @var string|null
     */
    private $colorSpace;
    /**
     * @var int
     */
    private $width = 0;
    /**
     * @var int
     */
    private $height = 0;
    /**
     * @var int
     */
    private $fps = 0;
    /**
     * @var bool
     */
    private $default = false;

    /**
     * @return null|string
     */
    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * @param null|string $language
     * @return Video
     */
    public function setLanguage(?string $language): Video
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCodec(): ?String
    {
        return $this->codec;
    }

    /**
     * @param null|string $codec
     * @return Video
     */
    public function setCodec(?string $codec): Video
    {
        $this->codec = $codec;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getColorSpace(): ?string
    {
        return $this->colorSpace;
    }

    /**
     * @param null|string $colorSpace
     * @return Video
     */
    public function setColorSpace(?string $colorSpace): Video
    {
        $this->colorSpace = $colorSpace;
        return $this;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return Video
     */
    public function setWidth(int $width): Video
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $height
     * @return Video
     */
    public function setHeight(int $height): Video
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return int
     */
    public function getFps(): int
    {
        return $this->fps;
    }

    /**
     * @param int $fps
     * @return Video
     */
    public function setFps(int $fps): Video
    {
        $this->fps = $fps;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->default;
    }

    /**
     * @param bool $default
     * @return Video
     */
    public function setDefault(bool $default): Video
    {
        $this->default = $default;
        return $this;
    }
}