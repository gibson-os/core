<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Core\Controller;

use GibsonOS\Core\Controller\CronjobController;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Cronjob;
use GibsonOS\Core\Store\CronjobStore;
use GibsonOS\Test\Functional\Core\FunctionalTest;

class CronjobControllerTest extends FunctionalTest
{
    private CronjobController $cronjobController;

    protected function _before(): void
    {
        parent::_before();

        $this->cronjobController = $this->serviceManager->get(CronjobController::class);
    }

    public function testIndex(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $list = [];

        for ($i = 1; $i <= 100; ++$i) {
            $command = sprintf('AAA %04d', $i);
            $date = new \DateTimeImmutable();
            $modelManager->saveWithoutChildren(
                (new Cronjob())
                ->setUser('marvin')
                ->setCommand($command)
                ->setAdded($date)
            );
            $list[] = [
                'id' => $i,
                'command' => $command,
                'user' => 'marvin',
                'arguments' => '[]',
                'options' => '[]',
                'active' => true,
                'lastRun' => null,
                'added' => $date->format('Y-m-d H:i:s'),
            ];
        }

        $date = new \DateTimeImmutable();
        $lastRun = new \DateTimeImmutable('-1 month');
        $modelManager->saveWithoutChildren(
            (new Cronjob())
                ->setUser('ford')
                ->setCommand('BBB')
                ->setAdded($date)
                ->setLastRun($lastRun)
                ->setActive(false)
                ->setArguments('["arthur": "dent"]')
                ->setOptions('["dent": "arthur"]')
        );
        $last = [
            'id' => 101,
            'command' => 'BBB',
            'user' => 'ford',
            'arguments' => '["arthur": "dent"]',
            'options' => '["dent": "arthur"]',
            'active' => false,
            'lastRun' => $lastRun->format('Y-m-d H:i:s'),
            'added' => $date->format('Y-m-d H:i:s'),
        ];

        $this->checkAjaxResponse(
            $this->cronjobController->index($this->serviceManager->get(CronjobStore::class)),
            $list,
            101,
        );
        $this->checkAjaxResponse(
            $this->cronjobController->index(
                $this->serviceManager->get(CronjobStore::class),
                start: 100,
            ),
            [$last],
            101,
        );
    }

    public function testIndexLimit(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $list = [];

        for ($i = 1; $i <= 5; ++$i) {
            $command = sprintf('AAA %04d', $i);
            $date = new \DateTimeImmutable();
            $modelManager->saveWithoutChildren(
                (new Cronjob())
                ->setUser('marvin')
                ->setCommand($command)
                ->setAdded($date)
            );
            $list[] = [
                'id' => $i,
                'command' => $command,
                'user' => 'marvin',
                'arguments' => '[]',
                'options' => '[]',
                'active' => true,
                'lastRun' => null,
                'added' => $date->format('Y-m-d H:i:s'),
            ];
        }

        $date = new \DateTimeImmutable();
        $lastRun = new \DateTimeImmutable('-1 month');
        $modelManager->saveWithoutChildren(
            (new Cronjob())
                ->setUser('ford')
                ->setCommand('BBB')
                ->setAdded($date)
                ->setLastRun($lastRun)
                ->setActive(false)
                ->setArguments('["arthur": "dent"]')
                ->setOptions('["dent": "arthur"]')
        );
        $last = [
            'id' => 6,
            'command' => 'BBB',
            'user' => 'ford',
            'arguments' => '["arthur": "dent"]',
            'options' => '["dent": "arthur"]',
            'active' => false,
            'lastRun' => $lastRun->format('Y-m-d H:i:s'),
            'added' => $date->format('Y-m-d H:i:s'),
        ];

        $this->checkAjaxResponse(
            $this->cronjobController->index($this->serviceManager->get(CronjobStore::class), 5),
            $list,
            6,
        );
        $this->checkAjaxResponse(
            $this->cronjobController->index(
                $this->serviceManager->get(CronjobStore::class),
                5,
                5,
            ),
            [$last],
            6,
        );
    }

    public function testIndexSort(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $listByCommand = [];
        $listByUser = [];
        $listByLastRun = [];
        $list = [];
        $counter = 5;

        for ($i = 1; $i <= 5; ++$i) {
            $command = sprintf('AAA %04d', $counter);
            $user = sprintf('marvin %04d', $i);
            $date = new \DateTimeImmutable();
            $lastRun = new \DateTimeImmutable('-' . $i . ' hours');
            $modelManager->saveWithoutChildren(
                (new Cronjob())
                ->setUser($user)
                ->setCommand($command)
                ->setAdded($date)
                ->setLastRun($lastRun)
                ->setActive($i > 3)
            );
            $item = [
                'id' => $i,
                'command' => $command,
                'user' => $user,
                'arguments' => '[]',
                'options' => '[]',
                'active' => $i > 3,
                'lastRun' => $lastRun->format('Y-m-d H:i:s'),
                'added' => $date->format('Y-m-d H:i:s'),
            ];
            $listByCommand[$command] = $item;
            $listByUser[$user] = $item;
            $listByLastRun[$lastRun->format('Y-m-d H:i:s')] = $item;
            $list[$i] = $item;
            --$counter;
        }

        $this->checkAjaxResponse(
            $this->cronjobController->index(
                $this->serviceManager->get(CronjobStore::class),
                sort: [['property' => 'command', 'direction' => 'DESC']],
            ),
            array_values($listByCommand),
            5,
        );
        ksort($listByCommand);
        $this->checkAjaxResponse(
            $this->cronjobController->index(
                $this->serviceManager->get(CronjobStore::class),
            ),
            array_values($listByCommand),
            5,
        );

        $this->checkAjaxResponse(
            $this->cronjobController->index(
                $this->serviceManager->get(CronjobStore::class),
                sort: [['property' => 'user']],
            ),
            array_values($listByUser),
            5,
        );
        krsort($listByUser);
        $this->checkAjaxResponse(
            $this->cronjobController->index(
                $this->serviceManager->get(CronjobStore::class),
                sort: [['property' => 'user', 'direction' => 'desc']],
            ),
            array_values($listByUser),
            5,
        );

        $this->checkAjaxResponse(
            $this->cronjobController->index(
                $this->serviceManager->get(CronjobStore::class),
                sort: [['property' => 'last_run', 'direction' => 'desc']],
            ),
            array_values($listByLastRun),
            5,
        );
        ksort($listByLastRun);
        $this->checkAjaxResponse(
            $this->cronjobController->index(
                $this->serviceManager->get(CronjobStore::class),
                sort: [['property' => 'last_run']],
            ),
            array_values($listByLastRun),
            5,
        );

        $this->checkAjaxResponse(
            $this->cronjobController->index(
                $this->serviceManager->get(CronjobStore::class),
                sort: [['property' => 'active']],
            ),
            array_values($list),
            5,
        );
//        krsort($list);
//        $this->checkAjaxResponse(
//            $this->cronjobController->index(
//                $this->serviceManager->get(CronjobStore::class),
//                sort: [['property' => 'active', 'direction' => 'desc']],
//            ),
//            array_values($list),
//            5,
//        );
    }
}
