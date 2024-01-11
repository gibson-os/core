<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Form;

use GibsonOS\Core\Model\ModelInterface;

abstract class AbstractModelConfig implements ConfigInterface
{
    public function __construct(private readonly ?ModelInterface $model = null)
    {
    }

    public function getModel(): ?ModelInterface
    {
        return $this->model;
    }
}
