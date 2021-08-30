<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event\Describer;

use GibsonOS\Core\Dto\Event\Describer\Method;
use GibsonOS\Core\Dto\Event\Describer\Trigger;
use GibsonOS\Core\Dto\Parameter\BoolParameter;
use GibsonOS\Core\Dto\Parameter\IntParameter;
use GibsonOS\Core\Dto\Parameter\StringParameter;
use GibsonOS\Core\Event\NetworkEvent;

class NetworkDescriber implements DescriberInterface
{
    public const TRIGGER_BEFORE_PING = 'beforePing';

    public const TRIGGER_AFTER_PING = 'afterPing';

    public function getTitle(): string
    {
        return 'Netzwerk';
    }

    public function getTriggers(): array
    {
        return [
            self::TRIGGER_BEFORE_PING => (new Trigger('Vor dem pingen'))
                ->setParameters([
                    'host' => new StringParameter('Host'),
                    'timeout' => new IntParameter('Timeout'),
                    'result' => new BoolParameter('Erreichbar'),
                ]),
            self::TRIGGER_AFTER_PING => (new Trigger('Nach dem pingen'))
                ->setParameters([
                    'host' => new StringParameter('Host'),
                    'timeout' => new IntParameter('Timeout'),
                    'result' => new BoolParameter('Erreichbar'),
                ]),
        ];
    }

    public function getMethods(): array
    {
        return [
            'ping' => (new Method('Ping'))
                ->setParameters([
                    'host' => new StringParameter('Host'),
                    'timeout' => new IntParameter('Timeout'),
                ])
        ];
    }

    public function getEventClassName(): string
    {
        return NetworkEvent::class;
    }
}