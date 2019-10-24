<?php
namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\Ffmpeg as FfmpegService;

class Ffmpeg
{
    /**
     * @param string $ffmpegPath
     * @return FfmpegService
     */
    public static function create(string $ffmpegPath): FfmpegService
    {
        return new FfmpegService($ffmpegPath);
    }
}