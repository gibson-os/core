<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception\Model;

use GibsonOS\Core\Exception\AbstractException;
use GibsonOS\Core\Model\ModelInterface;

class DeleteError extends AbstractException
{
    /**
     * @var ModelInterface
     */
    private $model;

    public function __construct($message = 'Datensatz konnte nicht gelöscht werden!', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return ModelInterface
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param ModelInterface $model
     *
     * @return DeleteError
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }
}
