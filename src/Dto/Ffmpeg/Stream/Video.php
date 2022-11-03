<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Ffmpeg\Stream;

class Video implements \JsonSerializable
{
    private ?string $language = null;

    private ?string $codec = null;

    private ?string $colorSpace = null;

    private int $width = 0;

    private int $height = 0;

    private int $fps = 0;

    private bool $default = false;

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): Video
    {
        $this->language = $language;

        return $this;
    }

    public function getCodec(): ?string
    {
        return $this->codec;
    }

    public function setCodec(?string $codec): Video
    {
        $this->codec = $codec;

        return $this;
    }

    public function getColorSpace(): ?string
    {
        return $this->colorSpace;
    }

    public function setColorSpace(?string $colorSpace): Video
    {
        $this->colorSpace = $colorSpace;

        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): Video
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): Video
    {
        $this->height = $height;

        return $this;
    }

    public function getFps(): int
    {
        return $this->fps;
    }

    public function setFps(int $fps): Video
    {
        $this->fps = $fps;

        return $this;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function setDefault(bool $default): Video
    {
        $this->default = $default;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'language' => $this->getLanguage(),
            'codec' => $this->getCodec(),
            'colorSpace' => $this->getColorSpace(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
            'fps' => $this->getFps(),
            'default' => $this->isDefault(),
        ];
    }
}
