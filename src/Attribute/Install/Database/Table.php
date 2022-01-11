<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute\Install\Database;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    public function __construct(
        private ?string $name = null,
        private string $engine = 'InnoDB',
        private string $charset = 'utf8'
    ) {
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): Table
    {
        $this->name = $name;

        return $this;
    }

    public function getEngine(): string
    {
        return $this->engine;
    }

    public function getCharset(): string
    {
        return $this->charset;
    }
}
