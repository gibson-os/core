<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command;

use GibsonOS\Core\Attribute\Command\Argument;
use GibsonOS\Core\Dto\Install\Input;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\InstallException;
use GibsonOS\Core\Service\InstallService;
use Psr\Log\LoggerInterface;

/**
 * @description Install GibsonOS
 */
class InstallCommand extends AbstractCommand
{
    #[Argument('Module to install')]
    private ?string $module = null;

    #[Argument('Part to install')]
    private ?string $part = null;

    public function __construct(private InstallService $installService, LoggerInterface $logger)
    {
        parent::__construct($logger);
    }

    /**
     * @throws GetError
     * @throws InstallException
     */
    protected function run(): int
    {
        foreach ($this->installService->install($this->module, $this->part) as $installDto) {
            echo $installDto->getMessage();

            if ($installDto instanceof Input) {
                $value = $installDto->getValue();

                if ($value !== null) {
                    printf(' (Leave empty for: %s)', $value);
                }

                echo ': ';
                $installDto->setValue(trim(fgets(STDIN) ?: '') ?: ($value ?? ''));
            } else {
                echo PHP_EOL;
            }
        }

        return self::SUCCESS;
    }
}
