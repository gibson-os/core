<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\Ffmpeg;

use GibsonOS\Core\Factory\AbstractSingletonFactory;
use GibsonOS\Core\Factory\FfmpegFactory;
use GibsonOS\Core\Service\Ffmpeg\MediaService;

class MediaFactory extends AbstractSingletonFactory
{
    /**
     * @return MediaService
     */
    protected static function createInstance(): MediaService
    {
        return new MediaService(FfmpegFactory::create());
    }

    public static function create(): MediaService
    {
        /** @var MediaService $service */
        $service = parent::create();

        return $service;
    }
}
