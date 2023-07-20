<?php
declare(strict_types=1);

namespace GibsonOS\Core\OpenTelemetry\Instrumentation;

use GibsonOS\Core\Service\ControllerService;
use GibsonOS\Core\Service\OpenTelemetry\InstrumentationService;
use OpenTelemetry\API\Common\Instrumentation\CachedInstrumentation;

class ControllerInstrumentation implements InstrumentationInterface
{
    public function __construct(private readonly InstrumentationService $instrumentationService)
    {
    }

    public function __invoke(): void
    {
        $instrumentation = new CachedInstrumentation('mysqli');

        $this->instrumentationService->addHook(
            $instrumentation,
            ControllerService::class,
            'runAction'
        );
    }
}
