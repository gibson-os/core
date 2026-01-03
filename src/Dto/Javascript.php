<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto;

use Override;
use Stringable;

class Javascript implements Stringable
{
    private bool $loaded = false;

    /**
     * @var string[]
     */
    private array $beforeLoad = [];

    public function __construct(private string $filename, private string $namespace, private string $content)
    {
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): Javascript
    {
        $this->filename = $filename;

        return $this;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function setNamespace(string $namespace): Javascript
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): Javascript
    {
        $this->content = $content;

        return $this;
    }

    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    public function setLoaded(bool $loaded): Javascript
    {
        $this->loaded = $loaded;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getBeforeLoad(): array
    {
        return $this->beforeLoad;
    }

    /**
     * @param string[] $beforeLoad
     */
    public function setBeforeLoad(array $beforeLoad): Javascript
    {
        $this->beforeLoad = $beforeLoad;

        return $this;
    }

    #[Override]
    public function __toString(): string
    {
        return
            '/* ' . $this->getFilename() . ' */' . PHP_EOL .
            $this->getContent()
        ;
    }
}
