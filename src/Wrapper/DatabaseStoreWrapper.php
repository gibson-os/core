<?php
declare(strict_types=1);

namespace GibsonOS\Core\Wrapper;

use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Mapper\Model\ChildrenMapper;
use GibsonOS\Core\Query\ChildrenQuery;
use MDO\Client;
use MDO\Extractor\PrimaryKeyExtractor;
use MDO\Manager\TableManager;
use MDO\Service\SelectService;

class DatabaseStoreWrapper
{
    public function __construct(
        private readonly TableManager $tableManager,
        private readonly Client $client,
        private readonly ModelManager $modelManager,
        private readonly ChildrenQuery $childrenQuery,
        private readonly SelectService $selectService,
        private readonly ModelWrapper $modelWrapper,
        private readonly PrimaryKeyExtractor $primaryKeyExtractor,
        private readonly ChildrenMapper $childrenMapper,
    ) {
    }

    public function getTableManager(): TableManager
    {
        return $this->tableManager;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getModelManager(): ModelManager
    {
        return $this->modelManager;
    }

    public function getChildrenQuery(): ChildrenQuery
    {
        return $this->childrenQuery;
    }

    public function getSelectService(): SelectService
    {
        return $this->selectService;
    }

    public function getModelWrapper(): ModelWrapper
    {
        return $this->modelWrapper;
    }

    public function getPrimaryKeyExtractor(): PrimaryKeyExtractor
    {
        return $this->primaryKeyExtractor;
    }

    public function getChildrenMapper(): ChildrenMapper
    {
        return $this->childrenMapper;
    }
}
