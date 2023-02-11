<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Setting;

class SettingRepository extends AbstractRepository
{
    public function __construct(#[GetTableName(Setting::class)] private readonly string $settingTableName)
    {
    }

    /**
     * @throws SelectError
     *
     * @return Setting[]
     */
    public function getAll(int $moduleId, ?int $userId): array
    {
        $parameters = [$moduleId];

        if ($userId !== null) {
            $parameters[] = $userId;
        }

        return $this->fetchAll(
            '`module_id`=? AND (`user_id` IS NULL' . ($userId === null ? '' : ' OR `user_id`=?') . ')',
            $parameters,
            Setting::class
        );
    }

    /**
     * @throws SelectError
     *
     * @return Setting[]
     */
    public function getAllByModuleName(string $moduleName, ?int $userId): array
    {
        $parameters = [$moduleName];

        if ($userId !== null) {
            $parameters[] = $userId;
        }

        $table = $this->getTable($this->settingTableName)
            ->appendJoin('module', '`' . $this->settingTableName . '`.`module_id`=`module`.`id`')
            ->setWhere(
                '`module`.`name`=? AND ' .
                '(`user_id` IS NULL' . ($userId === null ? '' : ' OR `user_id`=?') . ')'
            )
            ->setWhereParameters($parameters)
        ;

        return $this->getModels($table, Setting::class);
    }

    /**
     * @throws SelectError
     */
    public function getByKey(int $moduleId, ?int $userId, string $key): Setting
    {
        $parameters = [$moduleId, $key];

        if ($userId !== null) {
            $parameters[] = $userId;
        }

        return $this->fetchOne(
            '`module_id`=? AND `key`=? AND' .
            '(`user_id` IS NULL' . ($userId === null ? '' : ' OR `user_id`=?') . ')',
            $parameters,
            Setting::class
        );
    }

    /**
     * @throws SelectError
     */
    public function getByKeyAndModuleName(string $moduleName, ?int $userId, string $key): Setting
    {
        $parameters = [$moduleName, $key];

        if ($userId !== null) {
            $parameters[] = $userId;
        }

        $tableName = $this->settingTableName;
        $table = $this->getTable($tableName)
            ->appendJoin('module', '`' . $tableName . '`.`module_id`=`module`.`id`')
            ->setWhere(
                '`module`.`name`=? AND `' . $tableName . '`.`key`=? AND ' .
                '(`' . $tableName . '`.`user_id` IS NULL' . ($userId === null ? '' : ' OR `' . $tableName . '`.`user_id`=?') . ')'
            )
            ->setWhereParameters($parameters)
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
