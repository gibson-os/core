<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install\Data;

use Generator;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\User\PermissionRepository;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use GibsonOS\Core\Service\ServiceManagerService;

class GeneralPermissionData extends AbstractInstall implements PriorityInterface
{
    public function __construct(
        ServiceManagerService $serviceManagerService,
        private PermissionRepository $permissionRepository
    ) {
        parent::__construct($serviceManagerService);
    }

    /**
     * @throws SaveError
     */
    public function install(string $module): Generator
    {
        try {
            $this->permissionRepository->getByModuleTaskAndAction('core', 'user', 'login');
        } catch (SelectError) {
            (new Permission())
                ->setModule('core')
                ->setTask('user')
                ->setAction('login')
                ->setPermission(Permission::READ)
                ->save()
            ;
        }

        yield new Success('Set general permission for core!');
    }

    public function getPart(): string
    {
        return InstallService::PART_DATA;
    }

    public function getModule(): ?string
    {
        return 'explorer';
    }

    public function getPriority(): int
    {
        return 0;
    }
}
