<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Service\Attribute\ModelsFetcherAttribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class GetModels implements AttributeInterface
{
    /**
     * @param class-string<AbstractModel> $className
     * @param array<string, string>       $conditions
     */
    public function __construct(private readonly string $className, private readonly array $conditions = ['id' => 'id'])
    {
    }

    public function getAttributeServiceName(): string
    {
        return ModelsFetcherAttribute::class;
    }

    /**
     * @return class-string<AbstractModel>
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return array|string[]
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }
}
