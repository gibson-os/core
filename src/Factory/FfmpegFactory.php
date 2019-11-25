<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Service\FfmpegService;

class FfmpegFactory extends AbstractSingletonFactory
{
    /**
     * @throws GetError
     */
    protected static function createInstance(): FfmpegService
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

    public static function create(): FfmpegService
    {
        /** @var FfmpegService $service */
        $service = parent::create();

        return $service;
    }
}
