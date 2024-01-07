<?php
declare(strict_types=1);

namespace GibsonOS\Core\Form;

use GibsonOS\Core\Dto\Form;

abstract class AbstractForm
{
    abstract public function getForm(): Form;
}
