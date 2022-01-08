<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command;

use GibsonOS\Core\Service\InstallService;
use Psr\Log\LoggerInterface;

/**
 * @description Install GibsonOS
 */
class InstallCommand extends AbstractCommand
{
    public function __construct(private InstallService $installService, LoggerInterface $logger)
    {
        parent::__construct($logger);

        $this->setArgument('module', false);
        $this->setArgument('part', false);
    }

    protected function run(): int
    {
        $this->installService->install($this->getArgument('module'), $this->getArgument('part'));

        return 0;
    }
}
