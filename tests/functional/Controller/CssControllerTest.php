<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Core\Controller;

use GibsonOS\Core\Controller\CssController;
use GibsonOS\Core\Enum\HttpMethod;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Service\CssService;
use GibsonOS\Test\Functional\Core\FunctionalTest;

class CssControllerTest extends FunctionalTest
{
    private CssController $cssController;

    protected function _before(): void
    {
        parent::_before();

        $this->cssController = $this->serviceManager->get(CssController::class);
    }

    public function testGet(): void
    {
        //        /** @var ModelManager $modelManager */
        //        $modelManager = $this->serviceManager->get(ModelManager::class);
        //        $module = (new Module($this->modelWrapper))
        //            ->setName('core')
        //        ;
        //        $modelManager->saveWithoutChildren($module);
        //        $task = (new Task($this->modelWrapper))
        //            ->setName('css')
        //            ->setModule($module)
        //        ;
        //        $modelManager->saveWithoutChildren($task);
        //        $action = (new Action($this->modelWrapper))
        //            ->setName('index')
        //            ->setMethod(HttpMethod::GET)
        //            ->setModule($module)
        //            ->setTask($task)
        //        ;
        //        $modelManager->saveWithoutChildren($action);
        //
        //        $response = $this->cssController->getIndex(
        //            $this->serviceManager->get(CssService::class),
        //        );
        //
        //        var_dump($this->cssController->getIndex(
        //            $this->serviceManager->get(CssService::class),
        //        ));
    }
}
