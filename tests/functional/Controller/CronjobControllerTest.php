<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Core\Controller;

use DateTimeImmutable;
use GibsonOS\Core\Controller\CronjobController;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Cronjob;
use GibsonOS\Core\Model\Cronjob\Time;
use GibsonOS\Core\Store\Cronjob\TimeStore;
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

    public function testGet(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $list = [];

        for ($i = 1; $i <= 100; ++$i) {
            $command = sprintf('AAA %04d', $i);
            $date = new DateTimeImmutable();
            $modelManager->saveWithoutChildren(
                (new Cronjob($this->modelWrapper))
                ->setUser('marvin')
                ->setCommand($command)
                ->setAdded($date),
            );
            $list[] = [
                'id' => $i,
                'command' => $command,
                'user' => 'marvin',
                'arguments' => '[]',
                'options' => '[]',
                'active' => true,
                'last_run' => null,
                'added' => $date->format('Y-m-d H:i:s'),
            ];
        }

        $date = new DateTimeImmutable();
        $lastRun = new DateTimeImmutable('-1 month');
        $modelManager->saveWithoutChildren(
            (new Cronjob($this->modelWrapper))
                ->setUser('ford')
                ->setCommand('BBB')
                ->setAdded($date)
                ->setLastRun($lastRun)
                ->setActive(false)
                ->setArguments('["arthur": "dent"]')
                ->setOptions('["dent": "arthur"]'),
        );
        $last = [
            'id' => 101,
            'command' => 'BBB',
            'user' => 'ford',
            'arguments' => '["arthur": "dent"]',
            'options' => '["dent": "arthur"]',
            'active' => false,
            'last_run' => $lastRun->format('Y-m-d H:i:s'),
            'added' => $date->format('Y-m-d H:i:s'),
        ];

        $this->checkSuccessResponse(
            $this->cronjobController->get($this->serviceManager->get(CronjobStore::class)),
            $list,
            101,
        );
        $this->checkSuccessResponse(
            $this->cronjobController->get(
                $this->serviceManager->get(CronjobStore::class),
                start: 100,
            ),
            [$last],
            101,
        );
    }

    public function testGetLimit(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $list = [];

        for ($i = 1; $i <= 5; ++$i) {
            $command = sprintf('AAA %04d', $i);
            $date = new DateTimeImmutable();
            $modelManager->saveWithoutChildren(
                (new Cronjob($this->modelWrapper))
                    ->setUser('marvin')
                    ->setCommand($command)
                    ->setAdded($date),
            );
            $list[] = [
                'id' => $i,
                'command' => $command,
                'user' => 'marvin',
                'arguments' => '[]',
                'options' => '[]',
                'active' => true,
                'last_run' => null,
                'added' => $date->format('Y-m-d H:i:s'),
            ];
        }

        $date = new DateTimeImmutable();
        $lastRun = new DateTimeImmutable('-1 month');
        $modelManager->saveWithoutChildren(
            (new Cronjob($this->modelWrapper))
                ->setUser('ford')
                ->setCommand('BBB')
                ->setAdded($date)
                ->setLastRun($lastRun)
                ->setActive(false)
                ->setArguments('["arthur": "dent"]')
                ->setOptions('["dent": "arthur"]'),
        );
        $last = [
            'id' => 6,
            'command' => 'BBB',
            'user' => 'ford',
            'arguments' => '["arthur": "dent"]',
            'options' => '["dent": "arthur"]',
            'active' => false,
            'last_run' => $lastRun->format('Y-m-d H:i:s'),
            'added' => $date->format('Y-m-d H:i:s'),
        ];

        $this->checkSuccessResponse(
            $this->cronjobController->get($this->serviceManager->get(CronjobStore::class), 5),
            $list,
            6,
        );
        $this->checkSuccessResponse(
            $this->cronjobController->get(
                $this->serviceManager->get(CronjobStore::class),
                5,
                5,
            ),
            [$last],
            6,
        );
    }

    public function testGetSort(): void
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
            $date = new DateTimeImmutable();
            $lastRun = new DateTimeImmutable('-' . $i . ' hours');
            $modelManager->saveWithoutChildren(
                (new Cronjob($this->modelWrapper))
                ->setUser($user)
                ->setCommand($command)
                ->setAdded($date)
                ->setLastRun($lastRun)
                ->setActive($i > 3),
            );
            $item = [
                'id' => $i,
                'command' => $command,
                'user' => $user,
                'arguments' => '[]',
                'options' => '[]',
                'active' => $i > 3,
                'last_run' => $lastRun->format('Y-m-d H:i:s'),
                'added' => $date->format('Y-m-d H:i:s'),
            ];
            $listByCommand[$command] = $item;
            $listByUser[$user] = $item;
            $listByLastRun[$lastRun->format('Y-m-d H:i:s')] = $item;
            $list[$i] = $item;
            --$counter;
        }

        $this->checkSuccessResponse(
            $this->cronjobController->get(
                $this->serviceManager->get(CronjobStore::class),
                sort: [['property' => 'command', 'direction' => 'DESC']],
            ),
            array_values($listByCommand),
            5,
        );
        ksort($listByCommand);
        $this->checkSuccessResponse(
            $this->cronjobController->get(
                $this->serviceManager->get(CronjobStore::class),
            ),
            array_values($listByCommand),
            5,
        );

        $this->checkSuccessResponse(
            $this->cronjobController->get(
                $this->serviceManager->get(CronjobStore::class),
                sort: [['property' => 'user']],
            ),
            array_values($listByUser),
            5,
        );
        krsort($listByUser);
        $this->checkSuccessResponse(
            $this->cronjobController->get(
                $this->serviceManager->get(CronjobStore::class),
                sort: [['property' => 'user', 'direction' => 'desc']],
            ),
            array_values($listByUser),
            5,
        );

        $this->checkSuccessResponse(
            $this->cronjobController->get(
                $this->serviceManager->get(CronjobStore::class),
                sort: [['property' => 'last_run', 'direction' => 'desc']],
            ),
            array_values($listByLastRun),
            5,
        );
        ksort($listByLastRun);
        $this->checkSuccessResponse(
            $this->cronjobController->get(
                $this->serviceManager->get(CronjobStore::class),
                sort: [['property' => 'last_run']],
            ),
            array_values($listByLastRun),
            5,
        );

        $this->checkSuccessResponse(
            $this->cronjobController->get(
                $this->serviceManager->get(CronjobStore::class),
                sort: [['property' => 'active']],
            ),
            array_values($list),
            5,
        );

        $response = $this->cronjobController->get(
            $this->serviceManager->get(CronjobStore::class),
            sort: [['property' => 'active', 'direction' => 'desc']],
        );
        $body = json_decode($response->getBody(), true);
        $this->assertTrue($body['success']);
        $this->assertFalse($body['failure']);
        $this->assertEquals(5, $body['total']);

        foreach ($body['data'] as $item) {
            $position = array_search($item, $list);

            if ($position === false) {
                $this->assertTrue(false, sprintf('%s not found', json_encode($item)));

                continue;
            }

            unset($list[$position]);
        }

        $this->assertEquals(0, count($list));
    }

    public function testTimes(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $cronjob = (new Cronjob($this->modelWrapper))
            ->setUser('marvin')
            ->setCommand('galaxy')
        ;
        $modelManager->saveWithoutChildren($cronjob);
        $modelManager->saveWithoutChildren(
            (new Time($this->modelWrapper))
                ->setCronjob($cronjob),
        );
        $modelManager->saveWithoutChildren(
            (new Time($this->modelWrapper))
                ->setCronjob($cronjob)
                ->setFromMinute(7)
                ->setToMinute(7)
                ->setFromSecond(42)
                ->setToSecond(42),
        );

        $response = $this->cronjobController->getTimes(
            $this->serviceManager->get(TimeStore::class),
            $cronjob,
        );
        $body = json_decode($response->getBody(), true);

        $expected = [
            '*' => [
                'part' => 'year',
                'items' => [
                    '*' => [
                        'part' => 'month',
                        'items' => [
                            '*' => [
                                'part' => 'day_of_month',
                                'items' => [
                                    '*' => [
                                        'part' => 'day_of_week',
                                        'items' => [
                                            '*' => [
                                                'part' => 'hour',
                                                'items' => [
                                                    'part' => 'minute',
                                                    'items' => [
                                                        '*' => [
                                                            0 => [
                                                                'hour' => '*',
                                                                'minute' => '*',
                                                                'second' => '*',
                                                                'day_of_month' => '*',
                                                                'day_of_week' => '*',
                                                                'month' => '*',
                                                                'year' => '*',
                                                            ],
                                                        ],
                                                        7 => [
                                                            0 => [
                                                                'hour' => '*',
                                                                'minute' => '7',
                                                                'second' => '42',
                                                                'day_of_month' => '*',
                                                                'day_of_week' => '*',
                                                                'month' => '*',
                                                                'year' => '*',
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $body['data']);
    }
}
