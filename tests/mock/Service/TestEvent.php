<?php
declare(strict_types=1);

namespace GibsonOS\Mock\Service;

use GibsonOS\Core\Attribute\Event;
use GibsonOS\Core\Dto\Parameter\StringParameter;
use GibsonOS\Core\Event\AbstractEvent;

class TestEvent extends AbstractEvent
{
    #[Event\Trigger('No hope! That will end in tears!')]
    public const TRIGGER_MARVIN = 'marvin';

    #[Event\Trigger('Hitchhiker')]
    public const TRIGGER_FORD = 'ford';

    public string $arthur;

    #[Event\Method('Out to galaxy')]
    public function test(#[Event\Parameter(StringParameter::class)] string $arthur): string
    {
        $this->arthur = $arthur;

        return $this->arthur;
    }
}
