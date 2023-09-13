<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install\Database;

use Generator;
use GibsonOS\Core\Attribute\Install\Database\View;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\InstallException;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Model\ModelInterface;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use mysqlDatabase;
use ReflectionException;

class ViewInstall extends AbstractInstall implements PriorityInterface
{
    public function __construct(
        ServiceManager $serviceManagerService,
        private mysqlDatabase $mysqlDatabase,
        private ReflectionManager $reflectionManager,
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
            $reflectionClass = $this->reflectionManager->getReflectionClass($className);
            $viewAttribute = $this->reflectionManager->getAttribute($reflectionClass, View::class);

            if ($viewAttribute === null) {
                continue;
            }

            /** @var ModelInterface $model */
            $model = new $className();
            $viewName = $model->getTableName();
            $viewAttribute->setName($viewName);
            $query = 'DROP VIEW IF EXISTS `' . $viewName . '`';
            $this->logger->debug($query);

            if ($this->mysqlDatabase->sendQuery($query) === false) {
                throw new InstallException(sprintf(
                    'Drop view "%s" failed! Error: %s',
                    $viewName,
                    $this->mysqlDatabase->error(),
                ));
            }

            $query = 'CREATE VIEW `' . $viewName . '` AS ' . $viewAttribute->getQuery();
            $this->logger->debug($query);

            if ($this->mysqlDatabase->sendQuery($query) === false) {
                throw new InstallException(sprintf(
                    'Create view "%s" failed! Error: %s',
                    $viewName,
                    $this->mysqlDatabase->error(),
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
