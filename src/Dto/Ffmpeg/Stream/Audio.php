<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Ffmpeg\Stream;

use JsonSerializable;

class Audio implements JsonSerializable
{
    private ?string $language;

    private ?string $format;

    private ?string $frequency;

    private ?string $channels;

    private ?string $bitRate;

    private bool $default = false;

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): Audio
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

    public function getBitRate(): ?string
    {
        return $this->bitRate;
    }

    public function setBitRate(?string $bitRate): Audio
    {
        $this->bitRate = $bitRate;

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

    public function jsonSerialize(): array
    {
        return [
            'language' => $this->getLanguage(),
            'format' => $this->getFormat(),
            'frequency' => $this->getFrequency(),
            'channels' => $this->getChannels(),
            'bitRate' => $this->getBitRate(),
            'default' => $this->isDefault(),
        ];
    }
}
