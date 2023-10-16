<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core;

use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Wrapper\ModelWrapper;
use MDO\Client;
use MDO\Manager\TableManager;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

trait ModelManagerTrait
{
    use ProphecyTrait;

    protected Client|ObjectProphecy $client;

    protected ModelWrapper|ObjectProphecy $modelWrapper;

    protected ModelManager|ObjectProphecy $modelManager;

    protected TableManager|ObjectProphecy $tableManager;

    public function loadModelManager(): void
    {
        $this->client = $this->prophesize(Client::class);
        $this->tableManager = $this->prophesize(TableManager::class);
        $this->modelManager = $this->prophesize(ModelManager::class);
        $this->modelWrapper = $this->prophesize(ModelWrapper::class);

        $serviceManager = (new ServiceManager());
        $serviceManager->setService(Client::class, $this->client->reveal());
        //        $modelManager = $serviceManager->get(ModelManager::class);
        //        $this->modelManager->loadFromRecord(Argument::any(), Argument::any(), Argument::any())
        //            ->will(function (array $args) use ($modelManager): void {
        //                $modelManager->loadFromRecord($args[0], $args[1], $args[2]);
        //            })
        //        ;
    }
}
