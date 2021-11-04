<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception\Model;

use GibsonOS\Core\Exception\AbstractException;
use GibsonOS\Core\Model\ModelInterface;
use Throwable;

class SaveError extends AbstractException
{
    /**
     * @var ModelInterface
     */
    private $model;

    public function __construct($message = 'Datensatz konnte nicht gespeichert werden!', $code = 0, Throwable $previous = null)
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
     * @return SaveError
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }
}
