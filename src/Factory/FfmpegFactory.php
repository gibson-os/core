<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Service\Ffmpeg;

class FfmpegFactory
{
    /**
     * @throws GetError
     *
     * @return Ffmpeg
     */
    public static function create(): Ffmpeg
    {
        $env = EnvFactory::create();

        return new Ffmpeg(
            $env->getString('FFMPEG_PATH'),
            DateTimeFactory::create(),
            FileFactory::create(),
            ProcessFactory::create(),
            ImageFactory::create()
        );
    }
}
