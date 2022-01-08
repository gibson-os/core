<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command\Module;

use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Service\ModuleService;
use Psr\Log\LoggerInterface;

/**
 * @description Scan GibsonOS modules to find changes on modules/tasks/actions
 */
class ScanCommand extends AbstractCommand
{
    public function __construct(private ModuleService $moduleService, LoggerInterface $logger)
    {
        parent::__construct($logger);
    }

    /**
     * @throws GetError
     * @throws SaveError
     */
    protected function run(): int
    {
        $this->moduleService->scan();

        return 0;
    }
}
