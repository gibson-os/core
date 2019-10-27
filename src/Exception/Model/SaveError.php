<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception\Model;

use GibsonOS\Core\Exception\AbstractException;
use GibsonOS\Core\Model\AbstractModel;
use Throwable;

class SaveError extends AbstractException
{
    /**
     * @var AbstractModel
     */
    private $model;

    public function __construct($message = 'Datensatz konnte nicht gespeichert werden!', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return AbstractModel
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param AbstractModel $model
     *
     * @return SaveError
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }
}
