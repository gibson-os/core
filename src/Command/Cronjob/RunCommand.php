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
        while ((int) (new DateTime())->format('s') < 59) {
            $this->cronjobService->run($this->getArgument('user') ?? '');
        }

        return 0;
    }
}
