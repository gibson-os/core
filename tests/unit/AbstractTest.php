<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest;

use Codeception\Test\Unit;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Service\LoggerService;
use mysqlDatabase;
use mysqlRegistry;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class AbstractTest extends Unit
{
    use ProphecyTrait;

    protected ServiceManager $serviceManager;

    /**
     * @var ObjectProphecy|mysqlDatabase
     */
    protected $database;

    /**
     * @var ObjectProphecy|ModelManager
     */
    protected $modelManager;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->database = $this->prophesize(mysqlDatabase::class);
        mysqlRegistry::getInstance()->set('database', $this->database->reveal());
        $this->modelManager = $this->prophesize(ModelManager::class);
        $this->modelManager->save(Argument::any())->shouldNotBeCalled();
        $this->modelManager->delete(Argument::any())->shouldNotBeCalled();

        $this->serviceManager = new ServiceManager();
        $this->serviceManager->setInterface(LoggerInterface::class, LoggerService::class);
        $this->serviceManager->setService(mysqlDatabase::class, $this->database->reveal());
        $this->serviceManager->setService(ModelManager::class, $this->modelManager->reveal());
        putenv('TIMEZONE=Europe/Berlin');
        putenv('MYSQL_HOST=gos_mysql');
        putenv('MYSQL_DATABASE=gibson_os_test');
        putenv('MYSQL_USER=root');
        putenv('MYSQL_PASS=67yhnkMR');
        putenv('DATE_LATITUDE=51.2642156');
        putenv('DATE_LONGITUDE=6.8001438');
    }
}
