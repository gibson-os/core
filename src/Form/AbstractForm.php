<?php
declare(strict_types=1);

namespace GibsonOS\Core\Form;

use GibsonOS\Core\Dto\Form\Button;
use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use GibsonOS\Core\Mapper\ModelMapper;

abstract class AbstractForm
{
    /**
     * @return AbstractParameter[]
     */
    abstract protected function getFields(): array;

    abstract protected function getForm(): array;

    /**
     * @return array<string, Button>
     */
    abstract public function getButtons(): array;

    public function __construct(protected readonly ModelMapper $modelMapper)
    {
    }
}
