<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Ffmpeg\Stream;

use JsonSerializable;
use Override;

class Subtitle implements JsonSerializable
{
    private ?string $language = null;

    private bool $default = false;

    private bool $forced = false;

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

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'language' => $this->getLanguage(),
            'default' => $this->isDefault(),
            'forced' => $this->isForced(),
        ];
    }
}
