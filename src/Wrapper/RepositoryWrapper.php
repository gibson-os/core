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

class RepositoryWrapper
{
    public function __construct(
        private readonly Client $client,
        private readonly ModelManager $modelManager,
        private readonly TableManager $tableManager,
        private readonly ModelWrapper $modelWrapper,
        private readonly SelectService $selectService,
        private readonly ChildrenMapper $childrenMapper,
        private readonly ChildrenQuery $childrenQuery,
        private readonly PrimaryKeyExtractor $primaryKeyExtractor,
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

    public function getModelWrapper(): ModelWrapper
    {
        return $this->modelWrapper;
    }

    public function getSelectService(): SelectService
    {
        return $this->selectService;
    }

    public function getChildrenMapper(): ChildrenMapper
    {
        return $this->childrenMapper;
    }

    public function getChildrenQuery(): ChildrenQuery
    {
        return $this->childrenQuery;
    }

    public function getPrimaryKeyExtractor(): PrimaryKeyExtractor
    {
        return $this->primaryKeyExtractor;
    }
}
