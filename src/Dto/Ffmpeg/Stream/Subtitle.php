<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Ffmpeg\Stream;

use JsonSerializable;

class Subtitle implements JsonSerializable
{
    /**
     * @var string|null
     */
    private $language;

    /**
     * @var bool
     */
    private $default = false;

    /**
     * @var bool
     */
    private $forced = false;

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): Subtitle
    {
        $this->language = $language;

        return $this;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function setDefault(bool $default): Subtitle
    {
        $this->default = $default;

        return $this;
    }

    public function isForced(): bool
    {
        return $this->forced;
    }

    public function setForced(bool $forced): Subtitle
    {
        $this->forced = $forced;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'language' => $this->getLanguage(),
            'default' => $this->isDefault(),
            'forced' => $this->isForced(),
        ];
    }
}
