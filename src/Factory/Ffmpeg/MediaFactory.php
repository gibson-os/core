<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\Ffmpeg;

use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Factory\FfmpegFactory;
use GibsonOS\Core\Service\Ffmpeg;
use GibsonOS\Core\Service\Ffmpeg\Media;

class MediaFactory
{
    /**
     * @param Ffmpeg|null $ffmpeg
     *
     * @throws GetError
     *
     * @return Media
     */
    public static function create(Ffmpeg $ffmpeg = null): Media
    {
        if (!$ffmpeg instanceof Ffmpeg) {
            $ffmpeg = FfmpegFactory::create();
        }

        return new Media($ffmpeg);
    }
}
