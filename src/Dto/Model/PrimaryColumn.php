<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Model;

use GibsonOS\Core\Attribute\Install\Database\Column;
use ReflectionProperty;

class PrimaryColumn
{
    public function __construct(
        private readonly ReflectionProperty $reflectionProperty,
        private readonly Column $column,
    ) {
    }

    public function getReflectionProperty(): ReflectionProperty
    {
        return $this->reflectionProperty;
    }

    public function getColumn(): Column
    {
        return $this->column;
    }
}
