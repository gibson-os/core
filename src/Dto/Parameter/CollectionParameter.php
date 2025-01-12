<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

class CollectionParameter extends AbstractParameter
{
    /**
     * @var AbstractParameter[]
     */
    private array $parameters = [];

    public function __construct()
    {
        parent::__construct('', 'gosCoreComponentPanel');
    }

    protected function getTypeConfig(): array
    {
        return [];
    }

    public function getAllowedOperators(): array
    {
        return array_intersect(...array_map(
            fn (AbstractParameter $parameter) => $parameter->getAllowedOperators(),
            $this->parameters,
        ));
    }
}
