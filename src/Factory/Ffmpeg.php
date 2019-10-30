<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Service\Ffmpeg as FfmpegService;

class Ffmpeg
{
    /**
     * @throws GetError
     *
     * @return FfmpegService
     */
    public static function create(): FfmpegService
    {
        $env = Env::create();

        return new FfmpegService(
            $env->getString('FFMPEG_PATH'),
            DateTime::create(),
            File::create(),
            Process::create()
        );
    }
}
