<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command\Cronjob;

use GibsonOS\Core\Attribute\Command\Lock;
use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\FileService;
use Psr\Log\LoggerInterface;

/**
 * @description Remove cookies older than 1 day
 */
#[Cronjob(minutes: '27', seconds: '30')]
#[Lock('cookieRemoveCommand')]
class CookieRemoveCommand extends AbstractCommand
{
    public function __construct(
        private readonly DirService $dirService,
        private readonly FileService $fileService,
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    /**
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     */
    protected function run(): int
    {
        foreach ($this->dirService->getFiles(sys_get_temp_dir(), 'cookies*') as $file) {
            $this->logger->debug(sprintf('Cookie file %s', $file));

            if (filemtime($file) > time() - 86400) {
                continue;
            }

            $this->fileService->delete(sys_get_temp_dir(), $this->fileService->getFilename($file));
            $this->logger->info(sprintf('Cookie %s removed', $file));
        }

        return self::SUCCESS;
    }
}
