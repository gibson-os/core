<?php
declare(strict_types=1);

namespace GibsonOS\Core\Wrapper;

use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ReflectionManager;
use MDO\Client;
use MDO\Manager\TableManager;
use MDO\Service\SelectService;

class RepositoryWrapper
{
    public function __construct(
        private readonly Client $client,
        private readonly ModelManager $modelManager,
        private readonly TableManager $tableManager,
        private readonly ModelWrapper $modelService,
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

    public function getModelService(): ModelWrapper
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
