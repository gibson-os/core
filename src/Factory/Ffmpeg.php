<?php
namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\Ffmpeg as FfmpegService;

class Ffmpeg
{
    /**
     * @return FfmpegService
     */
    public static function create(): FfmpegService
    {
        return new FfmpegService(getenv('FFMPEG_PATH'));
    }
}