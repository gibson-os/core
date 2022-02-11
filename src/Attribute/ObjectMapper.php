<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Mapper\ObjectMapper as ObjectMapperClass;
use GibsonOS\Core\Mapper\ObjectMapperInterface;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class ObjectMapper
{
    /**
     * @param class-string|null                   $objectClassName
     * @param class-string<ObjectMapperInterface> $mapperClassName
     */
    public function __construct(private ?string $objectClassName = null, private string $mapperClassName = ObjectMapperClass::class)
    {
    }

    /**
     * @return class-string|null
     */
    public function getObjectClassName(): ?string
    {
        return $this->objectClassName;
    }

    /**
     * @return class-string<ObjectMapperInterface>
     */
    public function getMapperClassName(): string
    {
        return $this->mapperClassName;
    }
}
