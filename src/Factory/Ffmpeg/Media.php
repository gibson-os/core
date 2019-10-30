<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\Ffmpeg;

use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Factory\Ffmpeg as FfmpegFactory;
use GibsonOS\Core\Service\Ffmpeg;
use GibsonOS\Core\Service\Ffmpeg\Media as MediaService;

class Media
{
    /**
     * @param Ffmpeg|null $ffmpeg
     *
     * @throws CreateError
     * @throws FileNotFound
     * @throws GetError
     *
     * @return MediaService
     */
    public static function create(Ffmpeg $ffmpeg = null): MediaService
    {
        if (!$ffmpeg instanceof Ffmpeg) {
            $ffmpeg = FfmpegFactory::create();
        }

        return new MediaService($ffmpeg);
    }
}
