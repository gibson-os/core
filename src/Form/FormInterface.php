<?php
declare(strict_types=1);

namespace GibsonOS\Core\Form;

use GibsonOS\Core\Dto\Form;
use GibsonOS\Core\Dto\Form\ConfigInterface;

interface FormInterface
{
    public function getForm(ConfigInterface $config = null): Form;
}
