<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Event\Describer\Parameter;

class BoolParameter extends AbstractParameter
{
    public function __construct(string $title)
    {
        parent::__construct($title, 'bool');
    }

    protected function getTypeConfig(): array
    {
        return [];
    }
}
