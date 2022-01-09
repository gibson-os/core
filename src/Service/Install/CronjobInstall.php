<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Install;

use Generator;
use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Service\Attribute\Install\CronjobInstallAttribute;
use GibsonOS\Core\Service\AttributeService;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use GibsonOS\Core\Service\ServiceManagerService;
use JsonException;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;

class CronjobInstall extends AbstractInstall implements PriorityInterface
{
    public function __construct(
        DirService $dirService,
        ServiceManagerService $serviceManagerService,
        LoggerInterface $logger,
        private AttributeService $attributeService
    ) {
        parent::__construct($dirService, $serviceManagerService, $logger);
    }

    /**
     * @throws FactoryError
     * @throws GetError
     * @throws ReflectionException
     * @throws SaveError
     * @throws JsonException
     */
    public function install(string $module): Generator
    {
        foreach ($this->getFiles($this->dirService->addEndSlash($module) . 'src' . DIRECTORY_SEPARATOR . 'Command') as $file) {
            $className = $this->serviceManagerService->getNamespaceByPath($file);
            $attributes = $this->attributeService->getAttributesByClassName(
                new ReflectionClass($className),
                Cronjob::class
            );

            foreach ($attributes as $attribute) {
                /** @var CronjobInstallAttribute $service */
                $service = $attribute->getService();
                $service->execute($attribute->getAttribute(), $className);
            }
        }

        yield new Success(sprintf('Cronjobs installed for module "%s"!', $module));
    }

    public function getPart(): string
    {
        return InstallService::PART_CRONJOB;
    }

    public function getPriority(): int
    {
        return -100;
    }
}
