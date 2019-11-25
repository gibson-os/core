<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Module as ModuleModel;

class ModuleRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     * @throws DateTimeError
     * @throws GetError
     */
    public static function getByName(string $name): ModuleModel
    {
        $table = self::getTable(ModuleModel::getTableName());
        $table->setWhere('`name`=' . self::escape($name));

        if (!$table->select()) {
            $exception = new SelectError(sprintf('Modul mit dem Namen "%s" nicht gefunden!', $name));
            $exception->setTable($table);

            throw $exception;
        }

        $model = new ModuleModel();
        $model->loadFromMysqlTable($table);

        return $model;
    }
}
