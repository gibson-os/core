<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;

class SettingController extends AbstractController
{
    /**
     * @param array<string, array{permissionRequired: bool, method: string, items: array}> $requiredPermissions
     *
     * @throws JsonException
     */
    #[CheckPermission([Permission::READ])]
    public function getWindow(
        PermissionService $permissionService,
        SettingRepository $settingRepository,
        string $id,
        array $requiredPermissions = [],
    ): AjaxResponse {
        try {
            $windowSettings = $settingRepository->getByKeyAndModuleName(
                $this->requestService->getModuleName(),
                $this->sessionService->getUserId(),
                $id . '_window',
            );
        } catch (SelectError) {
            $windowSettings = null;
        }

        return $this->returnSuccess([
            'settings' => JsonUtility::decode($windowSettings instanceof Setting ? $windowSettings->getValue() : '[]'),
            'permissions' => $permissionService->getRequiredPermissions(
                $requiredPermissions,
                $this->sessionService->getUserId(),
            ),
        ]);
    }
}
