<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\RequestService;

abstract class AbstractController
{
    /**
     * @var PermissionService
     */
    private $permissionService;

    /**
     * @var RequestService
     */
    private $requestService;

    public function __construct(PermissionService $permissionService, RequestService $requestService)
    {
        $this->permissionService = $permissionService;
        $this->requestService = $requestService;
    }

    protected function checkPermission(int $permission): void
    {
        //$this->permissionService->hasPermission($permission)
    }
}
