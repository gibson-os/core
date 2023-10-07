<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Manager\ModelManager;
use MDO\Client;
use MDO\Manager\TableManager;

class ModelService
{
    public function __construct(
        private readonly Client $client,
        private readonly TableManager $tableManager,
        private readonly ModelManager $modelManager,
    ) {
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getTableManager(): TableManager
    {
        return $this->tableManager;
    }

    public function getModelManager(): ModelManager
    {
        return $this->modelManager;
    }
}
