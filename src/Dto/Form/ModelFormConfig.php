<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Form;

use GibsonOS\Core\Model\ModelInterface;

/**
 * @template T of ModelInterface
 */
class ModelFormConfig
{
    /**
     * @param T|null $model
     */
    public function __construct(private readonly ?ModelInterface $model = null)
    {
    }

    /**
     * @return T|null
     */
    public function getModel(): ?ModelInterface
    {
        return $this->model;
    }
}
