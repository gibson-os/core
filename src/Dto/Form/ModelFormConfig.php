<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Form;

use GibsonOS\Core\Model\AbstractModel;

/**
 * @template T of AbstractModel|null
 */
class ModelFormConfig
{
    /**
     * @param T|null $model
     */
    public function __construct(private readonly ?AbstractModel $model = null)
    {
    }

    /**
     * @return T|null
     */
    public function getModel(): ?AbstractModel
    {
        return $this->model;
    }
}
