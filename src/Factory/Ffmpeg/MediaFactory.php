<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\Ffmpeg;

use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Factory\FfmpegFactory;
use GibsonOS\Core\Service\Ffmpeg\MediaService;
use GibsonOS\Core\Service\FfmpegService;

class MediaFactory
{
    /**
     * @param FfmpegService|null $ffmpeg
     *
     * @throws GetError
     *
     * @return MediaService
     */
    public static function create(FfmpegService $ffmpeg = null): MediaService
    {
        if (!$ffmpeg instanceof FfmpegService) {
            $ffmpeg = FfmpegFactory::create();
        }

        return new MediaService($ffmpeg);
    }
}
