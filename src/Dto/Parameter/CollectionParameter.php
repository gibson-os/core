<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

class CollectionParameter extends AbstractParameter
{
    /**
     * @var AbstractParameter[]
     */
    private array $parameters = [];

    /**
     * @param class-string<AbstractParameter> $className
     */
    public function __construct(string $title, private string $className)
    {
        parent::__construct($title, 'gosCoreComponentPanel');
    }

    protected function getTypeConfig(): array
    {
        return [];
    }

    public function getAllowedOperators(): array
    {
        return array_intersect(...array_map(
            fn (AbstractParameter $parameter) => $parameter->getAllowedOperators(),
            $this->parameters
        ));
    }
}
