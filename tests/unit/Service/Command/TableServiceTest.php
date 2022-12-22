<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest\Service\Command;

use GibsonOS\Core\Service\Command\TableService;
use GibsonOS\UnitTest\AbstractTest;

class TableServiceTest extends AbstractTest
{
    private TableService $tableService;

    protected function _before(): void
    {
        $this->tableService = $this->serviceManager->get(TableService::class);
    }

    /**
     * @dataProvider getData
     */
    public function testGetTable(array $headers, array $content, string $result): void
    {
        $this->assertEquals($result, $this->tableService->getTable($headers, $content));
    }

    public function getData(): array
    {
        return [
            [
                ['First', 'Last'],
                [
                    ['Ford', 'Prefect'],
                    ['Arthur', 'Dent'],
                ],
                'First  | Last   ' . PHP_EOL .
                '----------------' . PHP_EOL .
                'Ford   | Prefect' . PHP_EOL .
                'Arthur | Dent   ' . PHP_EOL,
            ], [
                ['Firstname', 'Lastname'],
                [
                    ['Ford', 'Prefect'],
                    ['Arthur', 'Dent'],
                ],
                'Firstname | Lastname' . PHP_EOL .
                '--------------------' . PHP_EOL .
                'Ford      | Prefect ' . PHP_EOL .
                'Arthur    | Dent    ' . PHP_EOL,
            ],
        ];
    }
}
