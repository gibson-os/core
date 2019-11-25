<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\FileService;

class FileFactory extends AbstractSingletonFactory
{
    protected static function createInstance(): FileService
    {
        return new FileService(DirFactory::create());
    }

    public static function create(): FileService
    {
        /** @var FileService $service */
        $service = parent::create();

        return $service;
    }
}
