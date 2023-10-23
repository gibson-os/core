<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Core;

use Codeception\Test\Unit;
use GibsonOS\Core\Enum\Permission as PermissionEnum;
use GibsonOS\Core\Install\Database\TableInstall;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Service\CommandService;
use GibsonOS\Core\Service\EnvService;
use GibsonOS\Core\Service\LoggerService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Core\Wrapper\ModelWrapper;
use GibsonOS\Mock\Service\TestSessionService;
use MDO\Client;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

abstract class FunctionalTest extends Unit
{
    use ProphecyTrait;

    protected string $databaseName = 'galaxy';

    protected ServiceManager $serviceManager;

    protected ModelWrapper $modelWrapper;

    protected ObjectProphecy|CommandService $commandService;

    protected function getDir(): string
    {
        return __DIR__;
    }

    protected function _before(): void
    {
        $this->serviceManager = new ServiceManager();
        $this->serviceManager->setInterface(LoggerInterface::class, LoggerService::class);
        $this->serviceManager->setService(SessionService::class, new TestSessionService());

        $envService = $this->serviceManager->get(EnvService::class);

        $envService->loadFile($this->getDir() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.env.test');
        $this->serviceManager->setService(EnvService::class, $envService);

        $client = new Client(
            $envService->getString('MYSQL_HOST'),
            $envService->getString('MYSQL_USER'),
            $envService->getString('MYSQL_PASS'),
        );
        $databaseName = $envService->getString('MYSQL_DATABASE');
        $client->execute(sprintf(
            'DROP DATABASE IF EXISTS `%s`',
            $databaseName,
        ));
        $client->execute(sprintf(
            'CREATE DATABASE `%s`',
            $databaseName,
        ));
        $client->useDatabase($databaseName);
        $this->serviceManager->setService(Client::class, $client);

        $this->initDatabase();

        $this->modelWrapper = $this->serviceManager->get(ModelWrapper::class);
    }

    protected function initDatabase(): void
    {
        $tableInstall = $this->serviceManager->get(TableInstall::class);

        foreach ($tableInstall->install(__DIR__ . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..') as $success) {
        }

        if (__DIR__ === $this->getDir()) {
            return;
        }

        foreach ($tableInstall->install($this->getDir() . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..') as $success) {
        }
    }

    protected function addUser(string $username = 'marvin'): User
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $user = (new User($this->modelWrapper))
            ->setUser($username)
            ->setPassword('galaxy')
        ;
        $modelManager->saveWithoutChildren($user);
        $module = (new Module($this->modelWrapper))
            ->setName('core')
        ;
        $modelManager->saveWithoutChildren($module);
        $modelManager->saveWithoutChildren(
            (new Permission($this->modelWrapper))
                ->setModule($module)
                ->setPermission(PermissionEnum::READ->value + PermissionEnum::WRITE->value + PermissionEnum::DELETE->value + PermissionEnum::MANAGE->value)
                ->setUser($user),
        );

        return $user;
    }

    protected function checkSuccessResponse(AjaxResponse $response, mixed $data = null, int $total = null): void
    {
        $body = json_decode($response->getBody(), true);
        $this->assertTrue($body['success']);
        $this->assertFalse($body['failure']);
        $this->assertEquals($total, $body['total'] ?? null);
        $this->assertEquals($data, $body['data']);
    }

    protected function checkErrorResponse(AjaxResponse $response, string $message): void
    {
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertTrue($body['failure']);
        $this->assertEquals($message, $body['msg']);
    }
}
