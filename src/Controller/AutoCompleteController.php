<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\AutoComplete\AutoCompleteInterface;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\Response\AjaxResponse;

class AutoCompleteController extends AbstractController
{
    /**
     * @param class-string $autoCompleteClassname
     *
     * @throws FactoryError
     */
    #[CheckPermission(Permission::READ)]
    public function autoComplete(
        ServiceManager $serviceManagerService,
        RequestService $requestService,
        string $autoCompleteClassname,
        string $id = null,
        string $name = null
    ): AjaxResponse {
        /** @var AutoCompleteInterface $autoComplete */
        $autoComplete = $serviceManagerService->get($autoCompleteClassname);
        $parameters = $requestService->getRequestValues();

        if ($id !== null) {
            return $this->returnSuccess($autoComplete->getById($id, $parameters));
        }

        return $this->returnSuccess($autoComplete->getByNamePart($name ?? '', $parameters));
    }
}
