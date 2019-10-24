<?php
namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Service\Ffmpeg\Media as MediaService;

class Media
{
    /**
     * @param string $filename
     * @return MediaService
     * @throws FileNotFound
     */
    public static function create($filename)
    {
        $ffmpeg = Ffmpeg::create();

        return new MediaService($ffmpeg, $filename);
    }
}