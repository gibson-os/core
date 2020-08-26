<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event\Describer;

use GibsonOS\Core\Dto\Event\Describer\Method;
use GibsonOS\Core\Dto\Event\Describer\Parameter\IntParameter;
use GibsonOS\Core\Dto\Event\Describer\Parameter\StringParameter;
use GibsonOS\Core\Event\TestEvent;

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
                ->setParameters(['value' => new IntParameter('Wert')])
                ->setReturns(['value' => new IntParameter('Wert')]),
            'echo' => (new Method('Ausgeben'))
                ->setParameters(['value' => new StringParameter('Wert')]),
        ];
    }

    public function getEventClassName(): string
    {
        return TestEvent::class;
    }
}
