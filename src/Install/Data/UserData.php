<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install\Data;

use Generator;
use GibsonOS\Core\Dto\Install\Input;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\UserError;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Install\SingleInstallInterface;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Repository\UserRepository;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use GibsonOS\Core\Service\ServiceManagerService;
use GibsonOS\Core\Service\UserService;

class UserData extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    public function __construct(
        ServiceManagerService $serviceManagerService,
        private UserRepository $userRepository,
        private UserService $userService
    ) {
        parent::__construct($serviceManagerService);
    }

    /**
     * @throws SaveError
     * @throws SelectError
     * @throws UserError
     */
    public function install(string $module): Generator
    {
        if ($this->userRepository->getCount() !== 0) {
            return;
        }

        yield $usernameInput = new Input('What should the username be?');
        yield $passwordInput = new Input('What should the password be?');
        yield $passwordRepeatInput = new Input('Repeat password');

        $permissionAll = User\Permission::READ + User\Permission::WRITE + User\Permission::DELETE + User\Permission::MANAGE;
        $user = $this->userService->save(
            new User(),
            $usernameInput->getValue() ?? '',
            $passwordInput->getValue() ?? '',
            $passwordRepeatInput->getValue() ?? ''
        );

        foreach ($this->moduleRepository->getAll() as $module) {
            (new User\Permission())
                ->setUser($user)
                ->setModuleModel($module)
                ->setPermission($permissionAll)
                ->save()
            ;
        }
    }

    public function getPart(): string
    {
        return InstallService::PART_DATA;
    }

    public function getPriority(): int
    {
        return 0;
    }
}