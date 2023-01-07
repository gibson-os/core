<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetMappedModel;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Exception\Model\DeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\UserError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Model\User\Device;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\User\DeviceRepository;
use GibsonOS\Core\Repository\UserRepository;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\RedirectResponse;
use GibsonOS\Core\Service\Response\ResponseInterface;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Core\Service\TwigService;
use GibsonOS\Core\Service\UserService;
use GibsonOS\Core\Store\UserStore;
use GibsonOS\Core\Utility\StatusCode;

class UserController extends AbstractController
{
    public function __construct(
        RequestService $requestService,
        TwigService $twigService,
        SessionService $sessionService,
        private readonly PermissionService $permissionService
    ) {
        parent::__construct($requestService, $twigService, $sessionService);
    }

    /**
     * @throws SelectError
     * @throws \JsonException
     * @throws \ReflectionException
     */
    #[CheckPermission(Permission::MANAGE + Permission::READ)]
    public function index(UserStore $userStore): AjaxResponse
    {
        return $this->returnSuccess($userStore->getList());
    }

    /**
     * @throws UserError
     */
    public function login(
        UserService $userService,
        ?string $username,
        ?string $password
    ): RedirectResponse {
        if (empty($password) || empty($username)) {
            return new RedirectResponse($this->requestService->getBaseDir());
        }

        $userService->login($username, $password);

        return new RedirectResponse($this->requestService->getBaseDir());
    }

    /**
     * @throws SelectError
     */
    #[CheckPermission(Permission::READ, ['id' => Permission::READ + Permission::MANAGE])]
    public function settings(
        DeviceRepository $deviceRepository,
        #[GetModel] User $user = null
    ): AjaxResponse {
        if ($user === null) {
            $user = $this->sessionService->getUser();
        }

        if ($user === null) {
            return $this->returnFailure('User not found!');
        }

        $userJson = $user->jsonSerialize();
        $userJson['devices'] = $deviceRepository->findByUserId($user->getId() ?? 0);

        return $this->returnSuccess($userJson);
    }

    /**
     * @throws SaveError
     * @throws UserError
     */
    public function appLogin(
        UserService $userService,
        string $model,
        string $username,
        string $password,
        string $fcmToken
    ): AjaxResponse {
        if (empty($password) || empty($username)) {
            return $this->returnFailure('Login Error', StatusCode::UNAUTHORIZED);
        }

        $user = $userService->login($username, $password);
        $device = $userService->addDevice($user, $model, $fcmToken);

        return $this->returnSuccess([
            'id' => $user->getId(),
            'user' => $user->getUser(),
            'token' => $device->getToken(),
            'deviceId' => (int) $device->getId(),
        ]);
    }

    #[CheckPermission(Permission::WRITE)]
    public function logout(UserService $userService): ResponseInterface
    {
        $userService->logout();

        return new RedirectResponse($this->requestService->getBaseDir());
    }

    #[CheckPermission(Permission::READ)]
    public function sessionRefresh(): AjaxResponse
    {
        return $this->returnSuccess();
    }

    /**
     * @throws \JsonException
     * @throws \ReflectionException
     * @throws SaveError
     * @throws UserError
     *
     * @todo Model mapping?
     */
    #[CheckPermission(Permission::WRITE, ['add' => Permission::WRITE + Permission::MANAGE])]
    public function save(
        UserService $userService,
        UserRepository $userRepository,
        #[GetMappedModel] User $user,
        string $password,
        string $passwordRepeat,
        bool $add = false,
    ): AjaxResponse {
        if ($user->getId() === null) {
            if (!$add) {
                $user = $this->sessionService->getUser()
                    ?? throw new UserError('User not found!')
                ;
            } else {
                try {
                    $userRepository->getByUsername($user->getUser());

                    return $this->returnFailure('Benutzername existiert schon.');
                } catch (SelectError) {
                    // Do nothing
                }
            }
        }

        return $this->returnSuccess($userService->save(
            $user,
            $password,
            $passwordRepeat,
        ));
    }

    /**
     * @throws PermissionDenied
     */
    #[CheckPermission(Permission::DELETE, ['id' => Permission::DELETE + Permission::MANAGE])]
    public function deleteDevice(
        DeviceRepository $deviceRepository,
        #[GetModel] User $user,
        array $devices,
    ): AjaxResponse {
        $deviceRepository->deleteByIds($devices, $user->getId());

        return $this->returnSuccess();
    }

    /**
     * @throws \JsonException
     * @throws SaveError
     * @throws \ReflectionException
     */
    #[CheckPermission(Permission::MANAGE + Permission::WRITE)]
    public function savePermission(
        ModelManager $modelManager,
        int $permission,
        string $module,
        string $task = '',
        string $action = '',
        #[GetModel] User $user = null
    ): AjaxResponse {
        $modelManager->save(
            (new Permission())
            ->setUserId($user?->getId())
            ->setPermission($permission)
            ->setModule($module)
            ->setTask($task)
            ->setAction($action)
        );

        return $this->returnSuccess();
    }

    /**
     * @throws SaveError
     */
    #[CheckPermission(Permission::WRITE)]
    public function updateFcmToken(
        ModelManager $modelManager,
        #[GetModel(['token' => 'token'])] Device $device,
        string $fcmToken,
    ): AjaxResponse {
        $modelManager->saveWithoutChildren($device->setFcmToken($fcmToken));

        return $this->returnSuccess();
    }

    /**
     * @throws DeleteError
     * @throws \JsonException
     */
    #[CheckPermission(Permission::MANAGE + Permission::DELETE)]
    public function delete(ModelManager $modelManager, #[GetModel] User $user): AjaxResponse
    {
        $modelManager->delete($user);

        return $this->returnSuccess();
    }
}
