<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute\Event;

use Attribute;
use GibsonOS\Core\Dto\Parameter\AbstractParameter;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ReturnValue
{
    /**
     * @param class-string<AbstractParameter> $className
     * @param array<string, array>            $options
     */
    public function __construct(
        private string $className,
        private ?string $title = null,
        private array $options = [],
        private ?string $key = null
    ) {
    }

    /**
     * @return class-string<AbstractParameter>
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }
}
