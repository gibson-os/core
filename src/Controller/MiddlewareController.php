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
use GibsonOS\Core\Wrapper\ModelWrapper;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class MiddlewareController extends AbstractController
{
    /**
     * @throws SaveError
     * @throws SelectError
     * @throws JsonException
     * @throws ClientException
     * @throws RecordException
     * @throws ReflectionException
     */
    #[CheckMiddlewarePermission([Permission::WRITE])]
    public function postConfirm(
        string $token,
        ModuleRepository $moduleRepository,
        ModelManager $modelManager,
        ModelWrapper $modelWrapper,
        #[GetSetting('middlewareToken', 'core')]
        ?Setting $middlewareToken = null,
    ): AjaxResponse {
        $middlewareToken ??= (new Setting($modelWrapper))
            ->setModule($moduleRepository->getByName('core'))
            ->setKey('middlewareToken')
        ;
        $middlewareToken->setValue($token);
        $modelManager->saveWithoutChildren($middlewareToken);

        return $this->returnSuccess();
    }
}
