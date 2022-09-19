<?php
declare(strict_types=1);

namespace GibsonOS\Core\Form;

interface FormInterface
{
    public function getForm(): array;

    public function getButtons(): array;
}
