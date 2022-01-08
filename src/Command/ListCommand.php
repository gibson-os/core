<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command;

use GibsonOS\Core\Dto\Command;
use GibsonOS\Core\Service\Command\TableService;
use GibsonOS\Core\Store\CommandStore;
use Psr\Log\LoggerInterface;

/**
 * @description List all registered Commands
 */
class ListCommand extends AbstractCommand
{
    public function __construct(
        private CommandStore $commandStore,
        private TableService $tableService,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    protected function run(): int
    {
        echo PHP_EOL . $this->tableService->getTable(
            ['Command', 'Description'],
            array_map(
                fn (Command $command): array => [$command->getCommand(), $command->getDescription()],
                iterator_to_array($this->commandStore->getList())
            )
        ) . PHP_EOL;

        return 0;
    }
}
