<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\ProcessService;

class ProcessFactory extends AbstractSingletonFactory
{
    /**
     * @return ProcessService
     */
    protected static function createInstance(): ProcessService
    {
        return new ProcessService();
    }

    public static function create(): ProcessService
    {
        /** @var ProcessService $service */
        $service = parent::create();

        return $service;
    }
}
