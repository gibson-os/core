<?php
declare(strict_types=1);

namespace GibsonOS\Core\Wrapper;

use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Query\ChildrenQuery;
use MDO\Client;

class ModelWrapper
{
    public function __construct(
        private readonly Client $client,
        private readonly ServiceManager $serviceManager,
    ) {
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getModelManager(): ModelManager
    {
        return $this->serviceManager->get(ModelManager::class);
    }

    public function getChildrenQuery(): ChildrenQuery
    {
        return $this->serviceManager->get(ChildrenQuery::class);
    }
}
