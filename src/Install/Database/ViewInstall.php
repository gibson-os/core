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
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use MDO\Client;
use MDO\Exception\ClientException;
use Override;
use ReflectionException;

class ViewInstall extends AbstractInstall implements PriorityInterface
{
    public function __construct(
        ServiceManager $serviceManagerService,
        private readonly Client $client,
        private readonly ReflectionManager $reflectionManager,
    ) {
        parent::__construct($serviceManagerService);
    }

    /**
     * @throws FactoryError
     * @throws GetError
     * @throws ReflectionException
     * @throws InstallException
     */
    #[Override]
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

            /** @var AbstractModel $model */
            $model = new $className($this->modelWrapper);
            $viewName = $model->getTableName();
            $viewAttribute->setName($viewName);
            $query = sprintf('DROP VIEW IF EXISTS `%s`', $viewName);
            $this->logger->debug($query);

            try {
                $this->client->execute($query);
            } catch (ClientException) {
                throw new InstallException(sprintf(
                    'Drop view "%s" failed! Error: %s',
                    $viewName,
                    $this->client->getError(),
                ));
            }

            $query = sprintf('CREATE VIEW `%s` AS %s', $viewName, $viewAttribute->getQuery());
            $this->logger->debug($query);

            try {
                $this->client->execute($query);
            } catch (ClientException) {
                throw new InstallException(sprintf(
                    'Create view "%s" failed! Error: %s',
                    $viewName,
                    $this->client->getError(),
                ));
            }

            yield new Success(sprintf('View "%s" installed!', $viewName));
        }
    }

    #[Override]
    public function getPart(): string
    {
        return InstallService::PART_DATABASE;
    }

    #[Override]
    public function getPriority(): int
    {
        return 697;
    }
}
