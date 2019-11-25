<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\SqLiteService;

class SqLiteFactory
{
    public static function create(string $filename): SqLiteService
    {
        $sqLite = new SqLiteService($filename, FileFactory::create());

        return $sqLite;
    }
}
