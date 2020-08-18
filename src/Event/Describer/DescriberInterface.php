<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event\Describer;

use GibsonOS\Core\Dto\Event\Describer\Method;
use GibsonOS\Core\Dto\Event\Describer\Trigger;

interface DescriberInterface
{
    public function getTitle(): string;

    /**
     * Liste der Möglichen Events.
     *
     * @return Trigger[]
     */
    public function getTriggers(): array;

    /**
     * Liste der Möglichen Kommandos.
     *
     * @return Method[]
     */
    public function getMethods(): array;
}
