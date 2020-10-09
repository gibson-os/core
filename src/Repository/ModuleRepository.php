<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Module as ModuleModel;

class ModuleRepository extends AbstractRepository
{
    /**
     * @throws DateTimeError
     * @throws SelectError
     */
    public function getByName(string $name): ModuleModel
    {
        $table = $this->getTable(ModuleModel::getTableName())
            ->setWhere('`name`=?')
            ->addWhereParameter($name)
        ;

        if (!$table->selectPrepared()) {
            $exception = new SelectError(sprintf('Modul mit dem Namen "%s" nicht gefunden!', $name));
            $exception->setTable($table);

            throw $exception;
        }

        $model = new ModuleModel();
        $model->loadFromMysqlTable($table);

        return $model;
    }
}
