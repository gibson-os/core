<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ReflectionManager;
use MDO\Client;
use MDO\Manager\TableManager;
use MDO\Service\SelectService;

class RepositoryService
{
    public function __construct(
        private readonly Client $client,
        private readonly ModelManager $modelManager,
        private readonly TableManager $tableManager,
        private readonly ModelService $modelService,
        private readonly SelectService $selectService,
        private readonly ReflectionManager $reflectionManager,
    ) {
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getModelManager(): ModelManager
    {
        return $this->modelManager;
    }

    public function getTableManager(): TableManager
    {
        return $this->tableManager;
    }

    public function getModelService(): ModelService
    {
        return $this->modelService;
    }

    public function getSelectService(): SelectService
    {
        return $this->selectService;
    }

    public function getReflectionManager(): ReflectionManager
    {
        return $this->reflectionManager;
    }
}
