<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Ffmpeg\Stream;

class Subtitle
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

    /**
     * @return string|null
     */
    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * @param string|null $language
     *
     * @return Subtitle
     */
    public function setLanguage(?string $language): Subtitle
    {
        $this->language = $language;

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
     *
     * @return Subtitle
     */
    public function setDefault(bool $default): Subtitle
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @return bool
     */
    public function isForced(): bool
    {
        return $this->forced;
    }

    /**
     * @param bool $forced
     *
     * @return Subtitle
     */
    public function setForced(bool $forced): Subtitle
    {
        $this->forced = $forced;

        return $this;
    }
}
