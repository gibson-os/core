<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

use Override;

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

    #[Override]
    protected function getTypeConfig(): array
    {
        return [];
    }

    #[Override]
    public function getAllowedOperators(): array
    {
        return array_intersect(...array_map(
            fn (AbstractParameter $parameter) => $parameter->getAllowedOperators(),
            $this->parameters,
        ));
    }
}
