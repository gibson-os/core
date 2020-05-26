<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command\Cronjob;

use DateTime;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\ArgumentError;
use GibsonOS\Core\Service\CronjobService;

class RunCommand extends AbstractCommand
{
    /**
     * @var CronjobService
     */
    private $cronjobService;

    public function __construct(CronjobService $cronjobService)
    {
        $this->cronjobService = $cronjobService;

        $this->setArgument('user', true);
    }

    /**
     * @throws ArgumentError
     */
    protected function run(): int
    {
        do {
            $startSecond = (int) (new DateTime())->format('s');
            $this->cronjobService->run($this->getArgument('user') ?? '');

            do {
                usleep(100000);
                $endSecond = (int) (new DateTime())->format('s');
            } while ($startSecond === $endSecond);
        } while ($endSecond < 59);

        return 0;
    }
}
