<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Service\FfmpegService;

class FfmpegFactory
{
    /**
     * @throws GetError
     *
     * @return FfmpegService
     */
    public static function create(): FfmpegService
    {
        $env = EnvFactory::create();

        return new FfmpegService(
            $env->getString('FFMPEG_PATH'),
            DateTimeFactory::create(),
            FileFactory::create(),
            ProcessFactory::create(),
            ImageFactory::create()
        );
    }
}
