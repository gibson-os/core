<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\Setting;
use mysqlTable;

/**
 * @method Setting   fetchOne(string $where, array $parameters, string $abstractModelClassName = AbstractModel::class)
 * @method Setting[] fetchAll(string $where, array $parameters, string $abstractModelClassName = AbstractModel::class, int $limit = null, int $offset = null, string $orderBy = null)
 * @method Setting   getModel(mysqlTable $table, string $abstractModelClassName)
 * @method Setting[] getModels(mysqlTable $table, string $abstractModelClassName)
 */
class SettingRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     *
     * @return Setting[]
     */
    public function getAll(int $moduleId, int $userId): array
    {
        return $this->fetchAll('`module_id`=? AND (`user_id`=? OR `user_id`=0)', [$moduleId, $userId], Setting::class);
    }

    /**
     * @throws SelectError
     *
     * @return Setting[]
     */
    public function getAllByModuleName(string $moduleName, int $userId): array
    {
        $table = $this->getTable(Setting::getTableName())
            ->appendJoin('module', '`' . Setting::getTableName() . '`.`module_id`=`module`.`id`')
            ->setWhere(
                '`module`.`name`=? AND ' .
                '(`user_id`=? OR `user_id`=0)'
            )
            ->setWhereParameters([$moduleName, $userId])
        ;

        return $this->getModels($table, Setting::class);
    }

    /**
     * @throws SelectError
     */
    public function getByKey(int $moduleId, int $userId, string $key): Setting
    {
        return $this->fetchOne(
            '`module_id`=? AND ' .
            '(`user_id`=? OR `user_id`=?) AND ' .
            '`key`=?',
            [$moduleId, $userId, 0, $key],
            Setting::class
        );
    }

    /**
     * @throws SelectError
     */
    public function getByKeyAndModuleName(string $moduleName, int $userId, string $key): Setting
    {
        $tableName = Setting::getTableName();
        $table = $this->getTable($tableName)
            ->appendJoin('module', '`' . $tableName . '`.`module_id`=`module`.`id`')
            ->setWhere(
                '`module`.`name`=? AND ' .
                '(`' . $tableName . '`.`user_id`=? OR `' . $tableName . '`.`user_id`=0) AND ' .
                '`' . $tableName . '`.`key`=?'
            )
            ->setWhereParameters([$moduleName, $userId, $key])
            ->setOrderBy('`user_id` DESC')
            ->setLimit(1)
        ;

        if (!$table->selectPrepared()) {
            $exception = new SelectError(sprintf(
                'Einstellung mit dem Key "%s" konnte nicht geladen werden!',
                $key
            ));
            $exception->setTable($table);

            throw $exception;
        }

        return $this->getModel($table, Setting::class);
    }
}
