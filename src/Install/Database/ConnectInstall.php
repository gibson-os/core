<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install\Database;

use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Dto\Install\Input;
use GibsonOS\Core\Exception\InstallException;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Install\SingleInstallInterface;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class ConnectInstall extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    /**
     * @throws InstallException
     */
    public function install(string $module): \Generator
    {
        yield $hostInput = $this->getEnvInput('MYSQL_HOST', 'What is the MySQL hostname?');
        yield $userInput = $this->getEnvInput('MYSQL_USER', 'What is the MySQL username?');
        yield $passwordInput = $this->getEnvInput('MYSQL_PASS', 'What is the MySQL password?');

        $user = $userInput->getValue() ?? '';
        $password = $passwordInput->getValue() ?? '';

        yield $installUserInput = new Input('What is the MySQL install username?', $user);
        yield $installPasswordInput = new Input('What is the MySQL install password?', $password);
        yield $databaseInput = $this->getEnvInput('MYSQL_DATABASE', 'What is the MySQL database name?');

        $host = $hostInput->getValue() ?? '';
        $mysqlDatabase = $this->serviceManagerService->get(\mysqlDatabase::class);
        $mysqlDatabase->host = $host;
        $mysqlDatabase->user = $installUserInput->getValue() ?? '';
        $mysqlDatabase->pass = $installPasswordInput->getValue() ?? '';

        if (!$mysqlDatabase->openDB()) {
            throw new InstallException(sprintf(
                'Database connection can not be established! Error: %s',
                $mysqlDatabase->error()
            ));
        }

        $this->serviceManagerService->setService($mysqlDatabase::class, $mysqlDatabase);
        $database = $databaseInput->getValue() ?? '';

        if (!$mysqlDatabase->useDatabase($database)) {
            if (!$mysqlDatabase->sendQuery('CREATE DATABASE `' . $database . '` COLLATE utf8_general_ci')) {
                throw new InstallException(sprintf(
                    'Database "%s" could not be created! Error: %s',
                    $database,
                    $mysqlDatabase->error()
                ));
            }

            if (!$mysqlDatabase->useDatabase($database)) {
                throw new InstallException(sprintf(
                    'Database "%s" could not be open! Error: %s',
                    $database,
                    $mysqlDatabase->error()
                ));
            }
        }

        $mysqlUserDatabase = new \mysqlDatabase($host, $user, $password);

        if (
            !$mysqlUserDatabase->openDB() &&
            !$mysqlDatabase->sendQuery("CREATE USER '" . $user . "'@'%' IDENTIFIED BY '" . $password . "'") &&
            !$mysqlDatabase->sendQuery("GRANT USAGE ON *.* TO  '" . $user . "'@'%' IDENTIFIED BY '" . $password . "' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0")
        ) {
            throw new InstallException(sprintf(
                'MySQL User "%s" could not be created! Error: %s',
                $user,
                $mysqlDatabase->error()
            ));
        }

        if (
            !$mysqlUserDatabase->useDatabase($database) &&
            !$mysqlDatabase->sendQuery('GRANT SELECT, INSERT, UPDATE, DELETE ON `' . $database . "`.* TO '" . $user . "'@'%'")
        ) {
            throw new InstallException(sprintf(
                'MySQL User "%s" could not be connected with "%s"! Error: %s',
                $user,
                $database,
                $mysqlDatabase->error()
            ));
        }

        $mysqlUserDatabase->closeDB();

        yield (new Configuration('Database connection established!'))
            ->setValue('MYSQL_HOST', $host)
            ->setValue('MYSQL_USER', $user)
            ->setValue('MYSQL_PASS', $password)
            ->setValue('MYSQL_DATABASE', $database)
        ;
    }

    public function getPart(): string
    {
        return InstallService::PART_DATABASE;
    }

    public function getPriority(): int
    {
        return 900;
    }
}
