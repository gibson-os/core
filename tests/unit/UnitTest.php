<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core;

use Codeception\Test\Unit;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Service\CommandService;
use GibsonOS\Core\Service\LoggerService;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\SessionService;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Promise\ReturnPromise;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

abstract class UnitTest extends Unit
{
    use ProphecyTrait;

    protected string $databaseName = 'galaxy';

    protected ServiceManager $serviceManager;

    protected ObjectProphecy|\mysqlDatabase $database;

    protected ObjectProphecy|ModelManager $modelManager;

    protected ObjectProphecy|RequestService $requestService;

    protected ObjectProphecy|SessionService $sessionService;

    protected ObjectProphecy|CommandService $commandService;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->database = $this->prophesize(\mysqlDatabase::class);
        $this->database->getDatabaseName()->willReturn($this->databaseName);
        \mysqlRegistry::getInstance()->set('database', $this->database->reveal());
        $this->modelManager = $this->prophesize(ModelManager::class);
        $this->modelManager->save(Argument::any())->shouldNotBeCalled();
        $this->modelManager->saveWithoutChildren(Argument::any())->shouldNotBeCalled();
        $this->modelManager->delete(Argument::any())->shouldNotBeCalled();
        $this->requestService = $this->prophesize(RequestService::class);
        $this->requestService->getRequestValue(Argument::any())->willThrow(RequestError::class);
        $this->sessionService = $this->prophesize(SessionService::class);
        $this->commandService = $this->prophesize(CommandService::class);

        $this->serviceManager = new ServiceManager();
        $this->serviceManager->setInterface(LoggerInterface::class, LoggerService::class);
        $this->serviceManager->setService(\mysqlDatabase::class, $this->database->reveal());
        $this->serviceManager->setService(RequestService::class, $this->requestService->reveal());
        $this->serviceManager->setService(SessionService::class, $this->sessionService->reveal());
        $this->serviceManager->setService(CommandService::class, $this->commandService->reveal());

        putenv('TIMEZONE=Europe/Berlin');
        putenv('MYSQL_HOST=gos_mysql');
        putenv('MYSQL_DATABASE=gibson_os_test');
        putenv('MYSQL_USER=root');
        putenv('MYSQL_PASS=Arthur');
        putenv('DATE_LATITUDE=51.2642156');
        putenv('DATE_LONGITUDE=6.8001438');

        $modelManager = $this->serviceManager->get(ModelManager::class);
        $this->modelManager->loadFromMysqlTable(Argument::any(), Argument::any())
            ->will(function (array $args) use ($modelManager): void {
                $modelManager->loadFromMysqlTable($args[0], $args[1]);
            })
        ;
        $this->serviceManager->setService(ModelManager::class, $this->modelManager->reveal());
    }

    protected function showFieldsFromMapModel(): void
    {
        $this->database->sendQuery('SHOW FIELDS FROM `' . $this->databaseName . '`.`gibson_o_s_mock_dto_mapper_map_model`')
            ->willReturn(true)
        ;

        $fields = [
            ['id', 'bigint(20) unsigned', 'NO', 'PRI', null, 'auto_increment'],
            ['nullable_int_value', 'bigint(20)', 'YES', '', null, ''],
            ['string_enum_value', 'enum(\'NO\', \'YES\')', 'NO', '', null, ''],
            ['int_value', 'bigint(20)', 'NO', '', null, ''],
            ['parent_id', 'bigint(20) unsigned', 'YES', '', null, ''],
            [],
        ];
        $this->database->fetchRow()
            ->will(new ReturnPromise($fields))
        ;
    }
}
