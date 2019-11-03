<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\Image;

use GibsonOS\Core\Factory\FileFactory;
use GibsonOS\Core\Service\Image\Manipulate;

class ManipulateFactory
{
    /**
     * @return Manipulate
     */
    public static function create(): Manipulate
    {
        return new Manipulate(FileFactory::create());
    }
}
