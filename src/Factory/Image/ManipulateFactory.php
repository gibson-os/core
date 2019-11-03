<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\Image;

use GibsonOS\Core\Factory\FileFactory;
use GibsonOS\Core\Service\Image\ManipulateService;

class ManipulateFactory
{
    /**
     * @return ManipulateService
     */
    public static function create(): ManipulateService
    {
        return new ManipulateService(FileFactory::create());
    }
}
