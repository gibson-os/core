<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Core;

use Codeception\Test\Unit;
use GibsonOS\Core\Install\Database\TableInstall;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Service\CommandService;
use GibsonOS\Core\Service\EnvService;
use GibsonOS\Core\Service\LoggerService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Mock\Service\TestSessionService;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

abstract class FunctionalTest extends Unit
{
    use ProphecyTrait;

    protected string $databaseName = 'galaxy';

    protected ServiceManager $serviceManager;

    protected ObjectProphecy|CommandService $commandService;

    protected function _before(): void
    {
        $this->serviceManager = new ServiceManager();
        $this->serviceManager->setInterface(LoggerInterface::class, LoggerService::class);
        $this->serviceManager->setService(SessionService::class, new TestSessionService());

        $envService = $this->serviceManager->get(EnvService::class);
        $envService->loadFile(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.env.test');
        $this->serviceManager->setService(EnvService::class, $envService);

        $mysqlDatabase = new \mysqlDatabase(
            $envService->getString('MYSQL_HOST'),
            $envService->getString('MYSQL_USER'),
            $envService->getString('MYSQL_PASS'),
        );
        $mysqlDatabase->openDB();
        $databaseName = $envService->getString('MYSQL_DATABASE');
        $mysqlDatabase->sendQuery(sprintf(
            'DROP DATABASE IF EXISTS `%s`',
            $databaseName
        ));
        $mysqlDatabase->sendQuery(sprintf(
            'CREATE DATABASE `%s`',
            $databaseName
        ));
        $mysqlDatabase->useDatabase($databaseName);
        $this->serviceManager->setService(\mysqlDatabase::class, $mysqlDatabase);
        \mysqlRegistry::getInstance()->set('database', $mysqlDatabase);

        $this->initDatabase();
    }

    protected function initDatabase(): void
    {
        $tableInstall = $this->serviceManager->get(TableInstall::class);

        foreach ($tableInstall->install(__DIR__ . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..') as $success) {
        }
    }

    protected function addUser(): User
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $user = (new User())
            ->setUser('marvin')
            ->setPassword('galaxy')
        ;
        $modelManager->saveWithoutChildren($user);
        $modelManager->saveWithoutChildren(
            (new Permission())
            ->setModule('core')
            ->setPermission(Permission::READ + Permission::WRITE + Permission::DELETE + Permission::MANAGE)
            ->setUser($user)
        );

        return $user;
    }

    protected function checkAjaxResponse(AjaxResponse $response, mixed $data, int $total): void
    {
        $body = json_decode($response->getBody(), true);
        $this->assertTrue($body['success']);
        $this->assertFalse($body['failure']);
        $this->assertEquals($total, $body['total']);
        $this->assertEquals($data, $body['data']);
    }
}
