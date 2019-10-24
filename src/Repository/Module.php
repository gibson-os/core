<?php
namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Module as ModuleModel;

class Module extends AbstractRepository
{
    /**
     * @param $name
     * @return ModuleModel
     * @throws SelectError
     */
    public static function getByName($name)
    {
        $table = self::getTable(ModuleModel::getTableName());
        $table->setWhere('`name`=' . self::escape($name));

        if (!$table->select()) {
            $exception = new SelectError('Modul mit dem Namen "' . $name . '" nicht gefunden!');
            $exception->setTable($table);

            throw $exception;
        }

        $model = new ModuleModel();
        $model->loadFromMysqlTable($table);

        return $model;
    }
}