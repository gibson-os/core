<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install\Database;

use Generator;
use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Dto\Install\Input;
use GibsonOS\Core\Exception\InstallException;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Install\SingleInstallInterface;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use MDO\Client;
use MDO\Exception\ClientException;

class ConnectInstall extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    /**
     * @throws InstallException
     */
    public function install(string $module): Generator
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
        /** @var Client $client */
        $client = $this->serviceManagerService->get(Client::class);
        $client->close();

        try {
            $client->connect(
                $host,
                $installUserInput->getValue() ?? '',
                $installPasswordInput->getValue() ?? '',
            );
        } catch (ClientException $exception) {
            throw new InstallException(sprintf(
                'Database connection can not be established! Error: %s',
                $exception->getMessage(),
            ));
        }

        $this->serviceManagerService->setService($client::class, $client);
        $database = $databaseInput->getValue() ?? '';

        if (!$client->useDatabase($database)) {
            try {
                $client->execute('CREATE DATABASE `' . $database . '` COLLATE utf8_general_ci');
            } catch (ClientException $exception) {
                throw new InstallException(
                    sprintf(
                        'Database "%s" could not be created! Error: %s',
                        $database,
                        $client->getError(),
                    ),
                    previous: $exception,
                );
            }

            if (!$client->useDatabase($database)) {
                throw new InstallException(sprintf(
                    'Database "%s" could not be open! Error: %s',
                    $database,
                    $client->getError(),
                ));
            }
        }

        try {
            $mysqlUserClient = new Client($host, $user, $password, $database);
        } catch (ClientException) {
            try {
                $client->execute("CREATE USER '" . $user . "'@'%' IDENTIFIED BY '" . $password . "'");
                $client->execute("GRANT USAGE ON *.* TO  '" . $user . "'@'%' IDENTIFIED BY '" . $password . "' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0");
                $client->execute('GRANT SELECT, INSERT, UPDATE, DELETE ON `' . $database . "`.* TO '" . $user . "'@'%'");
            } catch (ClientException) {
                throw new InstallException(sprintf(
                    'MySQL User "%s" could not be created! Error: %s',
                    $user,
                    $client->getError(),
                ));
            }

            $mysqlUserClient = new Client($host, $user, $password, $database);
        }

        $mysqlUserClient->useDatabase($database);
        $mysqlUserClient->close();

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
