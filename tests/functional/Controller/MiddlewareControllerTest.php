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

    public function testPostConfirm(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $module = (new Module($this->modelWrapper))->setName('core');
        $modelManager->saveWithoutChildren($module);

        $this->checkSuccessResponse(
            $this->middlewareController->postConfirm(
                'galaxy',
                $this->serviceManager->get(ModuleRepository::class),
                $modelManager,
                $this->modelWrapper,
                (new Setting($this->modelWrapper))
                    ->setValue('marvin')
                    ->setModule($module)
                    ->setKey('middlewareToken'),
            ),
        );

        $settingRepository = $this->serviceManager->get(SettingRepository::class);
        $setting = $settingRepository->getByKeyAndModuleName('core', null, 'middlewareToken');

        $this->assertEquals('galaxy', $setting->getValue());
    }

    public function testPostConfirmWithoutMiddlewareToken(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $module = (new Module($this->modelWrapper))->setName('core');
        $modelManager->saveWithoutChildren($module);

        $this->checkSuccessResponse(
            $this->middlewareController->postConfirm(
                'marvin',
                $this->serviceManager->get(ModuleRepository::class),
                $modelManager,
                $this->modelWrapper,
            ),
        );

        $settingRepository = $this->serviceManager->get(SettingRepository::class);
        $setting = $settingRepository->getByKeyAndModuleName('core', null, 'middlewareToken');

        $this->assertEquals('marvin', $setting->getValue());
    }
}
