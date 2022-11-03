<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute\Install\Database;

use Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class View extends Table
{
    public function __construct(private string $query, private ?string $name = null)
    {
        parent::__construct($name);
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
