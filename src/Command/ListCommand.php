<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command;

use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Dto\Command;
use GibsonOS\Core\Service\Command\TableService;
use GibsonOS\Core\Store\CommandStore;
use Override;
use Psr\Log\LoggerInterface;

/**
 * @description List all registered Commands
 */
class ListCommand extends AbstractCommand
{
    public function __construct(
        private readonly CommandStore $commandStore,
        private readonly TableService $tableService,
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    #[Override]
    protected function run(): int
    {
        echo PHP_EOL . $this->tableService->getTable(
            ['Command', 'Description', 'Cronjobs (h m s DoM DoW mon year)'],
            array_map(
                fn (Command $command): array => [
                    $command->getCommand(),
                    $command->getDescription(),
                    implode(PHP_EOL, array_map(
                        fn (Cronjob $cronjob): string => $cronjob->getHours() . ' ' .
                            $cronjob->getMinutes() . ' ' .
                            $cronjob->getSeconds() . ' ' .
                            $cronjob->getDaysOfMonth() . ' ' .
                            $cronjob->getDaysOfWeek() . ' ' .
                            $cronjob->getMonths() . ' ' .
                            $cronjob->getYears(),
                        $command->getCronjobs(),
                    )),
                ],
                iterator_to_array($this->commandStore->getList()),
            ),
        ) . PHP_EOL;

        return self::SUCCESS;
    }
}
