<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use Generator;
use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Store\DriveStore;

class DriveController extends AbstractController
{
    /**
     * @throws SelectError
     */
    #[CheckPermission(Permission::READ)]
    public function index(DriveStore $driveStore): AjaxResponse
    {
        /** @var Generator $drives */
        $drives = $driveStore->getList();

        return new AjaxResponse([
            'success' => true,
            'failure' => false,
            'data' => [...$drives],
        ]);
    }
}
