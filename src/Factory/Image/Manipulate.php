<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\Image;

use GibsonOS\Core\Factory\File;
use GibsonOS\Core\Service\Image\Manipulate as ManipulateService;

class Manipulate
{
    /**
     * @return ManipulateService
     */
    public static function create(): ManipulateService
    {
        return new ManipulateService(File::create());
    }
}
