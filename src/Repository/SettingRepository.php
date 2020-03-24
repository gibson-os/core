<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Setting as SettingModel;

class SettingRepository extends AbstractRepository
{
    /**
     * @throws DateTimeError
     * @throws SelectError
     * @throws GetError
     *
     * @return SettingModel[]
     */
    public function getAll(int $moduleId, int $userId): array
    {
        $table = $this->getTable(SettingModel::getTableName());
        $table->setWhere(
            '`module_id`=' . $this->escape((string) $moduleId) . ' AND ' .
            '(`user_id`=' . $this->escape((string) $userId) . ' OR `user_id`=0)'
        );

        if (!$table->select()) {
            $exception = new SelectError('Einstellungen konnten nicht geladen werden!');
            $exception->setTable($table);

            throw $exception;
        }

        $models = [];

        do {
            $model = new SettingModel();
            $model->loadFromMysqlTable($table);
            $models[] = $model;
        } while ($table->next());

        return $models;
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws SelectError
     */
    public function getByKey(int $moduleId, int $userId, string $key): SettingModel
    {
        $table = $this->getTable(SettingModel::getTableName());
        $table->setWhere(
            '`module_id`=' . $this->escape((string) $moduleId) . ' AND ' .
            '(`user_id`=' . $this->escape((string) $userId) . ' OR `user_id`=0) AND ' .
            '`key`=' . $this->escape($key)
        );
        $table->setOrderBy('`user_id`');
        $table->setLimit(1);

        if (!$table->select()) {
            $exception = new SelectError(sprintf(
                'Einstellung mit dem Key "%s" konnte nicht geladen werden!',
                $key
            ));
            $exception->setTable($table);

            throw $exception;
        }

        $model = new SettingModel();
        $model->loadFromMysqlTable($table);

        return $model;
    }
}
