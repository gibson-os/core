<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event\Describer;

use GibsonOS\Core\Dto\Event\Describer\Method;
use GibsonOS\Core\Dto\Event\Describer\Parameter\IntParameter;

class TestDescriber implements DescriberInterface
{
    public function getTitle(): string
    {
        return 'test';
    }

    public function getTriggers(): array
    {
        return [];
    }

    public function getMethods(): array
    {
        return [
            'returnParameter' => (new Method('Gibt aus was rein kommt'))
                ->setParameters([new IntParameter('Wert')])
                ->setReturnTypes([new IntParameter('Wert')]),
            'echo' => (new Method('Ausgeben'))
                ->setParameters([new IntParameter('Wert')]),
        ];
    }
}
