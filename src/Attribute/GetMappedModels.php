<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Service\Attribute\ModelsMapperAttribute;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class GetMappedModels extends GetObject
{
    /**
     * @param class-string<AbstractModel> $className
     * @param array<string, string>       $conditions
     * @param array<string, string>       $mapping
     */
    public function __construct(private string $className, private array $conditions = ['id' => 'id'], array $mapping = [])
    {
        parent::__construct($mapping);
    }

    public function getAttributeServiceName(): string
    {
        return ModelsMapperAttribute::class;
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
