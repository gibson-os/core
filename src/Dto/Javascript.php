<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto;

class Javascript
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var
     */
    private $content;

    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * @var string[]
     */
    private $beforeLoad = [];

    public function __construct(string $filename, string $namespace, string $content)
    {
        $this->filename = $filename;
        $this->namespace = $namespace;
        $this->content = $content;
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

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     *
     * @return Javascript
     */
    public function setContent($content)
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

    public function __toString(): string
    {
        return
            '/* ' . $this->getFilename() . ' */' . PHP_EOL .
            $this->getContent()
        ;
    }
}
