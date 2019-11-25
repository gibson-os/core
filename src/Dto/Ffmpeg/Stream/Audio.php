<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Ffmpeg\Stream;

class Audio
{
    /**
     * @var string|null
     */
    private $language;

    /**
     * @var string|null
     */
    private $format;

    /**
     * @var string|null
     */
    private $frequency;

    /**
     * @var string|null
     */
    private $channels;

    /**
     * @var string|null
     */
    private $bitrate;

    /**
     * @var bool
     */
    private $default = false;

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * @param string|null $language
     */
    public function setLanguage($language): Audio
    {
        $this->language = $language;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(?string $format): Audio
    {
        $this->format = $format;

        return $this;
    }

    public function getFrequency(): ?string
    {
        return $this->frequency;
    }

    public function setFrequency(?string $frequency): Audio
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function getChannels(): ?string
    {
        return $this->channels;
    }

    public function setChannels(?string $channels): Audio
    {
        $this->channels = $channels;

        return $this;
    }

    public function getBitrate(): ?string
    {
        return $this->bitrate;
    }

    public function setBitrate(?string $bitrate): Audio
    {
        $this->bitrate = $bitrate;

        return $this;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function setDefault(bool $default): Audio
    {
        $this->default = $default;

        return $this;
    }
}
