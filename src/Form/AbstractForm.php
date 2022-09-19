<?php
declare(strict_types=1);

namespace GibsonOS\Core\Form;

use GibsonOS\Core\Dto\Form\Button;
use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use GibsonOS\Core\Mapper\ModelMapper;

abstract class AbstractForm
{
    abstract protected function getFields(): array;

    /**
     * @return Button[]
     */
    abstract public function getButtons(): array;

    /**
     * @var AbstractParameter[]
     */
    protected array $fields = [];

    public function __construct(protected readonly ModelMapper $modelMapper)
    {
        $this->fields = $this->getFields();
    }

    public function getForm(): array
    {
        return [
            'fields' => $this->fields,
            'buttons' => $this->getButtons(),
        ];
    }
}
