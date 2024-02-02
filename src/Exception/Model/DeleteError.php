<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception\Model;

use GibsonOS\Core\Exception\AbstractException;
use GibsonOS\Core\Model\ModelInterface;
use Throwable;

class DeleteError extends AbstractException
{
    private ModelInterface $model;

    public function __construct($message = 'Datensatz konnte nicht gelöscht werden!', $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getModel(): ModelInterface
    {
        return $this->model;
    }

    /**
     * @return DeleteError
     */
    public function setModel(ModelInterface $model)
    {
        $this->model = $model;

        return $this;
    }
}
