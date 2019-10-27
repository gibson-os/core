<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Service\Ffmpeg\Media as MediaService;

class Media
{
    /**
     * @param string $filename
     *
     * @throws FileNotFound
     * @throws GetError
     * @throws CreateError
     *
     * @return MediaService
     */
    public static function create($filename)
    {
        $ffmpeg = Ffmpeg::create();

        return new MediaService($ffmpeg, $filename);
    }
}
