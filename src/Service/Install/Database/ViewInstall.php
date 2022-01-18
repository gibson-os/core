<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Install\Database;

use Generator;
use GibsonOS\Core\Attribute\Install\Database\View;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\InstallException;
use GibsonOS\Core\Model\ModelInterface;
use GibsonOS\Core\Service\Install\AbstractInstall;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use GibsonOS\Core\Service\ServiceManagerService;
use mysqlDatabase;
use ReflectionClass;
use ReflectionException;

class ViewInstall extends AbstractInstall implements PriorityInterface
{
    public function __construct(
        ServiceManagerService $serviceManagerService,
        private mysqlDatabase $mysqlDatabase,
    ) {
        parent::__construct($serviceManagerService);
    }

    /**
     * @throws FactoryError
     * @throws GetError
     * @throws ReflectionException
     * @throws InstallException
     */
    public function install(string $module): Generator
    {
        $path = $this->dirService->addEndSlash($module) . 'src' . DIRECTORY_SEPARATOR . 'Model';

        foreach ($this->getFiles($path) as $file) {
            $className = $this->serviceManagerService->getNamespaceByPath($file);
            $reflectionClass = new ReflectionClass($className);
            $viewAttributes = $reflectionClass->getAttributes(View::class);

            if (count($viewAttributes) === 0) {
                continue;
            }

            /** @var ModelInterface $model */
            $model = new $className();
            $viewName = $model->getTableName();
            /** @var View $viewAttribute */
            $viewAttribute = $viewAttributes[0]->newInstance();
            $viewAttribute->setName($viewName);
            $query = 'DROP VIEW IF EXISTS `' . $viewName . '`';
            $this->logger->debug($query);

            if ($this->mysqlDatabase->sendQuery($query) === false) {
                throw new InstallException(sprintf(
                    'Drop view "%s" failed! Error: %s',
                    $viewName,
                    $this->mysqlDatabase->error()
                ));
            }

            $query = 'CREATE VIEW `' . $viewName . '` AS ' . $viewAttribute->getQuery();
            $this->logger->debug($query);

            if ($this->mysqlDatabase->sendQuery($query) === false) {
                throw new InstallException(sprintf(
                    'Create view "%s" failed! Error: %s',
                    $viewName,
                    $this->mysqlDatabase->error()
                ));
            }

            yield new Success(sprintf('View "%s" installed!', $viewName));
        }
    }

    public function getPart(): string
    {
        return InstallService::PART_DATABASE;
    }

    public function getPriority(): int
    {
        return 697;
    }
}
