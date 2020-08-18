<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event\Describer;

use GibsonOS\Core\Dto\Event\Describer\Method;
use GibsonOS\Core\Dto\Event\Describer\Parameter\IntParameter;

class TimeDescriber implements DescriberInterface
{
    public function getTitle(): string
    {
        return 'Zeit';
    }

    /**
     * Liste der Möglichen Events.
     */
    public function getTriggers(): array
    {
        return [];
    }

    /**
     * Liste der Möglichen Kommandos.
     *
     * @return Method[]
     */
    public function getMethods(): array
    {
        return [
            'sleep' => (new Method('Warten (s)'))
                ->setParameters([
                    'seconds' => (new IntParameter('Sekunden'))
                        ->setRange(1),
                ]),
            'usleep' => (new Method('Warten (ms)'))
                ->setParameters([
                    'microseconds' => (new IntParameter('Mikrosekunden'))
                        ->setRange(1),
                ]),
        ];
    }
}
