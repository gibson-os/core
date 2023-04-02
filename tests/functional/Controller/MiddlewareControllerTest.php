<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Core\Controller;

use GibsonOS\Core\Controller\MiddlewareController;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Repository\ModuleRepository;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Test\Functional\Core\FunctionalTest;

class MiddlewareControllerTest extends FunctionalTest
{
    private MiddlewareController $middlewareController;

    protected function _before(): void
    {
        parent::_before();

        $this->middlewareController = $this->serviceManager->get(MiddlewareController::class);
    }

    public function testConfirm(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $module = (new Module())->setName('core');
        $modelManager->saveWithoutChildren($module);

        $this->checkSuccessResponse(
            $this->middlewareController->confirm(
                'galaxy',
                $this->serviceManager->get(ModuleRepository::class),
                $modelManager,
                (new Setting())
                    ->setValue('marvin')
                    ->setModule($module)
                    ->setKey('middlewareToken'),
            )
        );

        $settingRepository = $this->serviceManager->get(SettingRepository::class);
        $setting = $settingRepository->getByKeyAndModuleName('core', null, 'middlewareToken');

        $this->assertEquals('galaxy', $setting->getValue());
    }

    public function testConfirmWithoutMiddlewareToken(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $module = (new Module())->setName('core');
        $modelManager->saveWithoutChildren($module);

        $this->checkSuccessResponse(
            $this->middlewareController->confirm(
                'marvin',
                $this->serviceManager->get(ModuleRepository::class),
                $modelManager,
            )
        );

        $settingRepository = $this->serviceManager->get(SettingRepository::class);
        $setting = $settingRepository->getByKeyAndModuleName('core', null, 'middlewareToken');

        $this->assertEquals('marvin', $setting->getValue());
    }
}
