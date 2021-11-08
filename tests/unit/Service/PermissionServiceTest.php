<?php
declare(strict_types=1);

namespace Service;

use Codeception\Test\Unit;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\User\PermissionViewRepository;
use GibsonOS\Core\Service\PermissionService;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class PermissionServiceTest extends Unit
{
    use ProphecyTrait;

    /**
     * @var PermissionService
     */
    private $permissionService;

    /**
     * @var ObjectProphecy|PermissionViewRepository
     */
    private $permissionViewRepository;

    protected function _before()
    {
        $this->permissionViewRepository = $this->prophesize(PermissionViewRepository::class);

        $this->permissionService = new PermissionService($this->permissionViewRepository->reveal());
    }

    /**
     * @dataProvider getPermissionData
     */
    public function testGetPermission(
        int $permission,
        bool $denied,
        bool $read,
        bool $write,
        bool $delete,
        bool $manage,
        string $module,
        string $task = null,
        string $action = null
    ): void {
        $this->prophesizeGetPermission($permission, $module, $task, $action);

        $this->assertEquals($permission, $this->permissionService->getPermission($module, $task, $action, 42));
    }

    private function prophesizeGetPermission(
        int $permission,
        string $module,
        string $task = null,
        string $action = null
    ): void {
        $permissionModel = $this->prophesize(Permission::class);
        $permissionModel->getPermission()
            ->shouldBeCalledOnce()
            ->willReturn($permission)
        ;

        $this->permissionViewRepository->getPermissionByModule($module, 42)
            ->shouldBeCalledTimes($task === null ? 1 : 0)
            ->willReturn($permissionModel->reveal())
        ;
        $this->permissionViewRepository->getPermissionByTask($module, $task, 42)
            ->shouldBeCalledTimes($task !== null && $action === null ? 1 : 0)
            ->willReturn($permissionModel->reveal())
        ;
        $this->permissionViewRepository->getPermissionByAction($module, $task, $action, 42)
            ->shouldBeCalledTimes($action === null ? 0 : 1)
            ->willReturn($permissionModel->reveal())
        ;
    }

    /**
     * @dataProvider getPermissionData
     */
    public function testHasPermissionDenied(
        int $permission,
        bool $denied,
        bool $read,
        bool $write,
        bool $delete,
        bool $manage,
        string $module,
        string $task = null,
        string $action = null
    ): void {
        $this->prophesizeGetPermission($permission, $module, $task, $action);
        $this->assertEquals(
            $denied,
            $this->permissionService->hasPermission(Permission::DENIED, $module, $task, $action, 42)
        );
    }

    /**
     * @dataProvider getPermissionData
     */
    public function testHasPermissionRead(
        int $permission,
        bool $denied,
        bool $read,
        bool $write,
        bool $delete,
        bool $manage,
        string $module,
        string $task = null,
        string $action = null
    ): void {
        $this->prophesizeGetPermission($permission, $module, $task, $action);
        $this->assertEquals(
            $read,
            $this->permissionService->hasPermission(Permission::READ, $module, $task, $action, 42)
        );
    }

    /**
     * @dataProvider getPermissionData
     */
    public function testHasPermissionWrite(
        int $permission,
        bool $denied,
        bool $read,
        bool $write,
        bool $delete,
        bool $manage,
        string $module,
        string $task = null,
        string $action = null
    ): void {
        $this->prophesizeGetPermission($permission, $module, $task, $action);
        $this->assertEquals(
            $write,
            $this->permissionService->hasPermission(Permission::WRITE, $module, $task, $action, 42)
        );
    }

    /**
     * @dataProvider getPermissionData
     */
    public function testHasPermissionDelete(
        int $permission,
        bool $denied,
        bool $read,
        bool $write,
        bool $delete,
        bool $manage,
        string $module,
        string $task = null,
        string $action = null
    ): void {
        $this->prophesizeGetPermission($permission, $module, $task, $action);
        $this->assertEquals(
            $delete,
            $this->permissionService->hasPermission(Permission::DELETE, $module, $task, $action, 42)
        );
    }

    /**
     * @dataProvider getPermissionData
     */
    public function testHasPermissionManage(
        int $permission,
        bool $denied,
        bool $read,
        bool $write,
        bool $delete,
        bool $manage,
        string $module,
        string $task = null,
        string $action = null
    ): void {
        $this->prophesizeGetPermission($permission, $module, $task, $action);
        $this->assertEquals(
            $manage,
            $this->permissionService->hasPermission(Permission::MANAGE, $module, $task, $action, 42)
        );
    }

    /**
     * @dataProvider getPermissionData
     */
    public function testIsDenied(
        int $permission,
        bool $denied,
        bool $read,
        bool $write,
        bool $delete,
        bool $manage,
        string $module,
        string $task = null,
        string $action = null
    ): void {
        $this->prophesizeGetPermission($permission, $module, $task, $action);
        $this->assertEquals($denied, $this->permissionService->isDenied($module, $task, $action, 42));
    }

    /**
     * @dataProvider getPermissionData
     */
    public function testHasReadPermission(
        int $permission,
        bool $denied,
        bool $read,
        bool $write,
        bool $delete,
        bool $manage,
        string $module,
        string $task = null,
        string $action = null
    ): void {
        $this->prophesizeGetPermission($permission, $module, $task, $action);
        $this->assertEquals($read, $this->permissionService->hasReadPermission($module, $task, $action, 42));
    }

    /**
     * @dataProvider getPermissionData
     */
    public function testHasWritePermission(
        int $permission,
        bool $denied,
        bool $read,
        bool $write,
        bool $delete,
        bool $manage,
        string $module,
        string $task = null,
        string $action = null
    ): void {
        $this->prophesizeGetPermission($permission, $module, $task, $action);
        $this->assertEquals($write, $this->permissionService->hasWritePermission($module, $task, $action, 42));
    }

    /**
     * @dataProvider getPermissionData
     */
    public function testHasDeletePermission(
        int $permission,
        bool $denied,
        bool $read,
        bool $write,
        bool $delete,
        bool $manage,
        string $module,
        string $task = null,
        string $action = null
    ): void {
        $this->prophesizeGetPermission($permission, $module, $task, $action);
        $this->assertEquals($delete, $this->permissionService->hasDeletePermission($module, $task, $action, 42));
    }

    /**
     * @dataProvider getPermissionData
     */
    public function testHasManagePermission(
        int $permission,
        bool $denied,
        bool $read,
        bool $write,
        bool $delete,
        bool $manage,
        string $module,
        string $task = null,
        string $action = null
    ): void {
        $this->prophesizeGetPermission($permission, $module, $task, $action);
        $this->assertEquals($manage, $this->permissionService->hasManagePermission($module, $task, $action, 42));
    }

    public function getPermissionData(): array
    {
        return $this->getPermissionList();
    }

    private function getPermissionList(
        int $start = 0,
        int $startPermission = 0,
        string $text = '',
        int $denied = 0,
        int $read = 0,
        int $write = 0,
        int $delete = 0,
        int $manage = 0
    ): array {
        $data = [];
        $permissions = [
            [1, 'Denied', 1, 0, 0, 0, 0],
            [2, 'Read', 0, 1, 0, 0, 0],
            [4, 'Write', 0, 0, 1, 0, 0],
            [8, 'Delete', 0, 0, 0, 1, 0],
            [16, 'Manage', 0, 0, 0, 0, 1],
        ];

        for ($i = $start; $i < count($permissions); ++$i) {
            $data[$text . $permissions[$i][1] . ' Module'] = [
                $permissions[$i][0] + $startPermission,
                (bool) ($denied + $permissions[$i][2]),
                $denied + $permissions[$i][2] === 1 ? false : (bool) ($read + $permissions[$i][3]),
                $denied + $permissions[$i][2] === 1 ? false : (bool) ($write + $permissions[$i][4]),
                $denied + $permissions[$i][2] === 1 ? false : (bool) ($delete + $permissions[$i][5]),
                $denied + $permissions[$i][2] === 1 ? false : (bool) ($manage + $permissions[$i][6]),
                'herz',
            ];
            $data[$text . $permissions[$i][1] . ' Task'] = [
                $permissions[$i][0] + $startPermission,
                (bool) ($denied + $permissions[$i][2]),
                $denied + $permissions[$i][2] === 1 ? false : (bool) ($read + $permissions[$i][3]),
                $denied + $permissions[$i][2] === 1 ? false : (bool) ($write + $permissions[$i][4]),
                $denied + $permissions[$i][2] === 1 ? false : (bool) ($delete + $permissions[$i][5]),
                $denied + $permissions[$i][2] === 1 ? false : (bool) ($manage + $permissions[$i][6]),
                'herz',
                'aus',
            ];
            $data[$text . $permissions[$i][1] . ' Action'] = [
                $permissions[$i][0] + $startPermission,
                (bool) ($denied + $permissions[$i][2]),
                $denied + $permissions[$i][2] === 1 ? false : (bool) ($read + $permissions[$i][3]),
                $denied + $permissions[$i][2] === 1 ? false : (bool) ($write + $permissions[$i][4]),
                $denied + $permissions[$i][2] === 1 ? false : (bool) ($delete + $permissions[$i][5]),
                $denied + $permissions[$i][2] === 1 ? false : (bool) ($manage + $permissions[$i][6]),
                'herz',
                'aus',
                'gold',
            ];
            $data = array_merge($data, $this->getPermissionList(
                $i + 1,
                $permissions[$i][0] + $startPermission,
                $text . $permissions[$i][1] . ' + ',
                $denied + $permissions[$i][2],
                $read + $permissions[$i][3],
                $write + $permissions[$i][4],
                $delete + $permissions[$i][5],
                $manage + $permissions[$i][6]
            ));
        }

        return $data;
    }
}
