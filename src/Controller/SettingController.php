<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;

class SettingController extends AbstractController
{
    /**
     * @param array<string, array{permissionRequired: bool, items: array}> $requiredPermissions
     *
     * @throws JsonException
     * @throws SelectError
     */
    #[CheckPermission(Permission::READ)]
    public function window(
        PermissionService $permissionService,
        SettingRepository $settingRepository,
        string $id,
        array $requiredPermissions = []
    ): AjaxResponse {
        try {
            $windowSettings = $settingRepository->getByKeyAndModuleName(
                $this->requestService->getModuleName(),
                $this->sessionService->getUserId() ?? 0,
                $id . '_window'
            );
        } catch (SelectError) {
            $windowSettings = null;
        }

        return $this->returnSuccess([
            'settings' => JsonUtility::decode($windowSettings === null ? '[]' : $windowSettings->getValue()),
            'permissions' => $permissionService->getRequiredPermissions(
                $requiredPermissions,
                $this->sessionService->getUserId() ?? 0
            ),
        ]);
    }
}
