<?php
namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\SqLite as SqLiteService;

class SqLite
{
    /**
     * @param string $filename
     * @return SqLiteService
     */
    public static function create(string $filename): SqLiteService
    {
        $sqLite = new SqLiteService($filename);

        return $sqLite;
    }
}