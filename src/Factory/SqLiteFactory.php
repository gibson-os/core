<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\FileService;
use GibsonOS\Core\Service\SqLiteService;

class SqLiteFactory
{
    public function __construct(private readonly FileService $fileService)
    {
    }

    public function create(string $filename): SqLiteService
    {
        return new SqLiteService($filename, $this->fileService);
    }
}
