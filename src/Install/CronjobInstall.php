<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install;

use Generator;
use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Service\Attribute\Install\CronjobInstallAttribute;
use GibsonOS\Core\Service\AttributeService;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use JsonException;
use ReflectionException;

class CronjobInstall extends AbstractInstall implements PriorityInterface
{
    public function __construct(
        ServiceManager $serviceManagerService,
        private readonly AttributeService $attributeService,
        private readonly ReflectionManager $reflectionManager
    ) {
        parent::__construct($serviceManagerService);
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
                $this->reflectionManager->getReflectionClass($className),
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
