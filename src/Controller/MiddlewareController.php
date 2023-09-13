<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckMiddlewarePermission;
use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Repository\ModuleRepository;
use GibsonOS\Core\Service\Response\AjaxResponse;

class MiddlewareController extends AbstractController
{
    /**
     * @param Setting|null $middlewareToken
     *
     * @throws SaveError
     * @throws SelectError
     */
    #[CheckMiddlewarePermission([Permission::WRITE])]
    public function postConfirm(
        string $token,
        ModuleRepository $moduleRepository,
        ModelManager $modelManager,
        #[GetSetting('middlewareToken', 'core')]
        Setting $middlewareToken = null,
    ): AjaxResponse {
        $middlewareToken ??= (new Setting())
            ->setModule($moduleRepository->getByName('core'))
            ->setKey('middlewareToken')
        ;
        $middlewareToken->setValue($token);
        $modelManager->saveWithoutChildren($middlewareToken);

        return $this->returnSuccess();
    }
}
