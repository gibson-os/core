<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute\Install;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Attribute\Install\Cronjob as CronjobAttribute;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\CronjobRepository;
use GibsonOS\Core\Service\Attribute\AttributeServiceInterface;
use GibsonOS\Core\Service\CronjobService;
use JsonException;

class CronjobInstallAttribute implements InstallAttributeInterface, AttributeServiceInterface
{
    public function __construct(
        private CronjobService $cronjobService,
        private CronjobRepository $cronjobRepository,
        #[GetEnv('APACHE_USER')] private string $apacheUser
    ) {
    }

    /**
     * @throws SaveError
     * @throws JsonException
     */
    public function execute(AttributeInterface $attribute, string $className): void
    {
        if (!$attribute instanceof CronjobAttribute) {
            return;
        }

        $user = $attribute->getUser() ?? $this->apacheUser;

        try {
            $this->cronjobRepository->getByCommandAndUser($className, $user);
        } catch (SelectError) {
            $this->cronjobService->add(
                $className,
                $user,
                $attribute->getHours(),
                $attribute->getMinutes(),
                $attribute->getSeconds(),
                $attribute->getDaysOfMonth(),
                $attribute->getDaysOfWeek(),
                $attribute->getMonths(),
                $attribute->getYears(),
                $attribute->getArguments(),
                $attribute->getOptions()
            );
        }
    }
}
