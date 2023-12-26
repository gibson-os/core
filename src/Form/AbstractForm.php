<?php
declare(strict_types=1);

namespace GibsonOS\Core\Form;

use GibsonOS\Core\Dto\Parameter\AbstractParameter;

abstract class AbstractForm
{
    /**
     * @return AbstractParameter[]
     */
    abstract protected function getFields(): array;

    abstract public function getForm(): array;
}
