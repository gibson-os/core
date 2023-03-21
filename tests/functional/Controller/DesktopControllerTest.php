<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Core\Controller;

use GibsonOS\Core\Controller\DesktopController;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Desktop\Item;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Repository\Desktop\ItemRepository;
use GibsonOS\Test\Functional\Core\FunctionalTest;

class DesktopControllerTest extends FunctionalTest
{
    private DesktopController $desktopController;

    protected function _before(): void
    {
        parent::_before();

        $this->desktopController = $this->serviceManager->get(DesktopController::class);
    }

    public function testIndex(): void
    {
        $user = $this->addUser();

        $this->checkAjaxResponse(
            $this->desktopController->index(
                $this->serviceManager->get(ItemRepository::class),
                null,
                null,
                $user,
            ),
            [
                DesktopController::APPS_KEY => [],
                DesktopController::TOOLS_KEY => [],
                DesktopController::DESKTOP_KEY => [],
            ],
        );

        $modelManager = $this->serviceManager->get(ModelManager::class);
        $modelManager->saveWithoutChildren(
            (new Item())
                ->setText('Arthur')
                ->setModule('galaxy')
                ->setTask('marvin')
                ->setAction('42')
                ->setIcon('dent')
                ->setUser($user)
        );
        $item =
        $this->checkAjaxResponse(
            $this->desktopController->index(
                $this->serviceManager->get(ItemRepository::class),
                (new Setting())->setValue('{"arthur":"dent"}'),
                (new Setting())->setValue('{"ford":"prefect"}'),
                $user
            ),
            [
                DesktopController::APPS_KEY => ['arthur' => 'dent'],
                DesktopController::TOOLS_KEY => ['ford' => 'prefect'],
                DesktopController::DESKTOP_KEY => [[
                    'text' => 'Arthur',
                    'module' => 'galaxy',
                    'task' => 'marvin',
                    'action' => '42',
                    'icon' => 'dent',
                    'id' => 1,
                    'position' => 0,
                    'parameters' => null,
                ]],
            ],
        );
    }

    public function testSave(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $user = $this->addUser();

        $item = [
            'text' => 'Arthur',
            'module' => 'galaxy',
            'task' => 'marvin',
            'action' => '42',
            'icon' => 'dent',
            'id' => 1,
            'position' => 0,
            'parameters' => null,
        ];
        $this->checkAjaxResponse(
            $this->desktopController->save(
                $modelManager,
                $this->serviceManager->get(ItemRepository::class),
                [
                    (new Item())
                        ->setText('Arthur')
                        ->setModule('galaxy')
                        ->setTask('marvin')
                        ->setAction('42')
                        ->setIcon('dent')
                        ->setUser($user),
                ],
                $user,
            ),
            [$item],
        );
        $this->checkAjaxResponse(
            $this->desktopController->index(
                $this->serviceManager->get(ItemRepository::class),
                null,
                null,
                $user,
            ),
            [
                DesktopController::APPS_KEY => [],
                DesktopController::TOOLS_KEY => [],
                DesktopController::DESKTOP_KEY => [$item],
            ],
        );

        $item = [
            'text' => 'Ford',
            'module' => 'marvin',
            'task' => 'galaxy',
            'action' => '24',
            'icon' => 'prefect',
            'id' => 2,
            'position' => 0,
            'parameters' => ['zaphod' => 'bebblebrox'],
        ];
        $this->checkAjaxResponse(
            $this->desktopController->save(
                $modelManager,
                $this->serviceManager->get(ItemRepository::class),
                [
                    (new Item())
                        ->setText('Ford')
                        ->setModule('marvin')
                        ->setTask('galaxy')
                        ->setAction('24')
                        ->setIcon('prefect')
                        ->setUser($user)
                        ->setParameters(['zaphod' => 'bebblebrox']),
                ],
                $user,
            ),
            [$item],
        );
        $this->checkAjaxResponse(
            $this->desktopController->index(
                $this->serviceManager->get(ItemRepository::class),
                null,
                null,
                $user,
            ),
            [
                DesktopController::APPS_KEY => [],
                DesktopController::TOOLS_KEY => [],
                DesktopController::DESKTOP_KEY => [$item],
            ],
        );
    }

    public function testAdd(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $user = $this->addUser();

        $items = [];
        $items[] = [
            'text' => 'Arthur',
            'module' => 'galaxy',
            'task' => 'marvin',
            'action' => '42',
            'icon' => 'dent',
            'id' => 1,
            'position' => 0,
            'parameters' => null,
        ];
        $this->checkAjaxResponse(
            $this->desktopController->add(
                $modelManager,
                $this->serviceManager->get(ItemRepository::class),
                [
                    (new Item())
                        ->setText('Arthur')
                        ->setModule('galaxy')
                        ->setTask('marvin')
                        ->setAction('42')
                        ->setIcon('dent')
                        ->setUser($user)
                        ->setPosition(-1),
                ],
                $user,
            ),
            [$items[0]],
        );
        $this->checkAjaxResponse(
            $this->desktopController->index(
                $this->serviceManager->get(ItemRepository::class),
                null,
                null,
                $user,
            ),
            [
                DesktopController::APPS_KEY => [],
                DesktopController::TOOLS_KEY => [],
                DesktopController::DESKTOP_KEY => [$items[0]],
            ],
        );

        $items[] = [
            'text' => 'Ford',
            'module' => 'marvin',
            'task' => 'galaxy',
            'action' => '24',
            'icon' => 'prefect',
            'id' => 2,
            'position' => 1,
            'parameters' => ['zaphod' => 'bebblebrox'],
        ];
        $items[] = [
            'text' => 'Ford2',
            'module' => 'marvin2',
            'task' => 'galaxy2',
            'action' => '242',
            'icon' => 'prefect2',
            'id' => 3,
            'position' => 2,
            'parameters' => ['zaphod2' => 'bebblebrox2'],
        ];
        $this->checkAjaxResponse(
            $this->desktopController->add(
                $modelManager,
                $this->serviceManager->get(ItemRepository::class),
                [
                    (new Item())
                        ->setText('Ford')
                        ->setModule('marvin')
                        ->setTask('galaxy')
                        ->setAction('24')
                        ->setIcon('prefect')
                        ->setUser($user)
                        ->setPosition(-1)
                        ->setParameters(['zaphod' => 'bebblebrox']),
                    (new Item())
                        ->setText('Ford2')
                        ->setModule('marvin2')
                        ->setTask('galaxy2')
                        ->setAction('242')
                        ->setIcon('prefect2')
                        ->setUser($user)
                        ->setPosition(-1)
                        ->setParameters(['zaphod2' => 'bebblebrox2']),
                ],
                $user,
            ),
            [$items[1], $items[2]],
        );
        $this->checkAjaxResponse(
            $this->desktopController->index(
                $this->serviceManager->get(ItemRepository::class),
                null,
                null,
                $user,
            ),
            [
                DesktopController::APPS_KEY => [],
                DesktopController::TOOLS_KEY => [],
                DesktopController::DESKTOP_KEY => $items,
            ],
        );
    }
}
