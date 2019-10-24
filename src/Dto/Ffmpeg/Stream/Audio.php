<?php
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

    /**
     * @return null|string
     */
    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * @param null|string $language
     * @return Audio
     */
    public function setLanguage($language): Audio
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getFormat(): ?string
    {
        return $this->format;
    }

    /**
     * @param null|string $format
     * @return Audio
     */
    public function setFormat(?string $format): Audio
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getFrequency(): ?string
    {
        return $this->frequency;
    }

    /**
     * @param null|string $frequency
     * @return Audio
     */
    public function setFrequency(?string $frequency): Audio
    {
        $this->frequency = $frequency;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getChannels(): ?string
    {
        return $this->channels;
    }

    /**
     * @param null|string $channels
     * @return Audio
     */
    public function setChannels(?string $channels): Audio
    {
        $this->channels = $channels;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getBitrate(): ?string
    {
        return $this->bitrate;
    }

    /**
     * @param null|string $bitrate
     * @return Audio
     */
    public function setBitrate(?string $bitrate): Audio
    {
        $this->bitrate = $bitrate;
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
     * @return Audio
     */
    public function setDefault(bool $default): Audio
    {
        $this->default = $default;
        return $this;
    }
}