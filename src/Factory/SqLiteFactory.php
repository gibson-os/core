<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\SqLite;

class SqLiteFactory
{
    /**
     * @param string $filename
     *
     * @return SqLite
     */
    public static function create(string $filename): SqLite
    {
        $sqLite = new SqLite($filename, FileFactory::create());

        return $sqLite;
    }
}
