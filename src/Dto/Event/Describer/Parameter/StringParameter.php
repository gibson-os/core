<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Event\Describer\Parameter;

class StringParameter extends AbstractParameter
{
    public function __construct(string $title)
    {
        parent::__construct($title, 'string');
    }

    protected function getTypeConfig(): array
    {
        return [];
    }
}
