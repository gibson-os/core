<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Service\Command;

use Codeception\Test\Unit;
use GibsonOS\Core\Service\Command\TableService;

class TableServiceTest extends Unit
{
    private TableService $tableService;

    protected function _before(): void
    {
        $this->tableService = new TableService();
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
