<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Install;

use Generator;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\ServiceManagerService;
use Psr\Log\LoggerInterface;

abstract class AbstractInstall implements InstallInterface
{
    public function __construct(
        protected DirService $dirService,
        protected ServiceManagerService $serviceManagerService,
        protected LoggerInterface $logger
    ) {
    }

    /**
     * @throws GetError
     */
    protected function getFiles(string $path): Generator
    {
        $path = $this->dirService->addEndSlash($path);

        foreach ($this->dirService->getFiles($path) as $file) {
            if (is_dir($file)) {
                yield from $this->getFiles($file);

                continue;
            }

            yield $file;
        }
    }
}
