<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Service\PermissionService;

abstract class AbstractController
{
    /**
     * @var PermissionService
     */
    private $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function checkPermission(int $permission): void
    {
        //$this->permissionService->hasPermission($permission)
    }
}
