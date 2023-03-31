<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core;

use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ServiceManager;
use mysqlDatabase;
use mysqlRegistry;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

trait ModelManagerTrait
{
    use ProphecyTrait;

    protected mysqlDatabase|ObjectProphecy $mysqlDatabase;

    private ModelManager|ObjectProphecy $modelManager;

    public function loadModelManager(): void
    {
        $this->mysqlDatabase = $this->prophesize(mysqlDatabase::class);
        $this->modelManager = $this->prophesize(ModelManager::class);

        mysqlRegistry::getInstance()->reset();
        mysqlRegistry::getInstance()->set('database', $this->mysqlDatabase->reveal());

        $serviceManager = (new ServiceManager());
        $serviceManager->setService(mysqlDatabase::class, $this->mysqlDatabase->reveal());
        $modelManager = $serviceManager->get(ModelManager::class);
        $this->modelManager->loadFromMysqlTable(Argument::any(), Argument::any())
            ->will(function (array $args) use ($modelManager): void {
                $modelManager->loadFromMysqlTable($args[0], $args[1]);
            })
        ;
    }
}
