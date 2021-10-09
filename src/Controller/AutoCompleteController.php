<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\AutoComplete\AutoCompleteInterface;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\ServiceManagerService;

class AutoCompleteController extends AbstractController
{
    /**
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws FactoryError
     */
    public function autoComplete(
        ServiceManagerService $serviceManagerService,
        RequestService $requestService,
        string $autoCompleteClassname,
        string $id = '',
        string $name = ''
    ): AjaxResponse {
        $this->checkPermission(PermissionService::READ);

        /** @var AutoCompleteInterface $autoComplete */
        $autoComplete = $serviceManagerService->get($autoCompleteClassname);
        $parameters = $requestService->getRequestValues();

        if (!empty($id)) {
            return $this->returnSuccess($autoComplete->getById($id, $parameters));
        }

        return $this->returnSuccess($autoComplete->getByNamePart($name, $parameters));
    }
}
