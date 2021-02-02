<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto;

class Css
{
    private string $filename;

    private string $content;

    public function __construct(string $filename, string $content)
    {
        $this->filename = $filename;
        $this->content = $content;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): Css
    {
        $this->filename = $filename;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): Css
    {
        $this->content = $content;

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
