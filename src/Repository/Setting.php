<?php
namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Setting as SettingModel;

class Setting extends AbstractRepository
{
    /**
     * @param int $moduleId
     * @param int $userId
     * @return SettingModel[]
     * @throws SelectError
     */
    public static function getAll($moduleId, $userId)
    {
        $table = self::getTable(SettingModel::getTableName());
        $table->setWhere(
            '`module_id`=' . self::escape($moduleId) . ' AND ' .
            '(`user_id`=' . self::escape($userId) . ' OR `user_id`=0)'
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
     * @param int $moduleId
     * @param int $userId
     * @param string $key
     * @return SettingModel
     * @throws SelectError
     */
    public static function getByKey($moduleId, $userId, $key)
    {
        $table = self::getTable(SettingModel::getTableName());
        $table->setWhere(
            '`module_id`=' . self::escape($moduleId) . ' AND ' .
            '(`user_id`=' . self::escape($userId) . ' OR `user_id`=0) AND ' .
            '`key`=' . self::escape($key)
        );
        $table->setOrderBy('`user_id`');
        $table->setLimit(1);

        if (!$table->select()) {
            $exception = new SelectError('Einstellung mit dem Key "' . $key . '" konnte nicht geladen werden!');
            $exception->setTable($table);

            throw $exception;
        }

        $model = new SettingModel();
        $model->loadFromMysqlTable($table);

        return $model;
    }
}