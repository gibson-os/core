<?php
declare(strict_types=1);

namespace GibsonOS\Core\OpenTelemetry\Instrumentation;

use GibsonOS\Core\Service\OpenTelemetry\InstrumentationService;
use GibsonOS\Core\Store\AbstractStore;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use Override;

class StoreInstrumentation implements InstrumentationInterface
{
    public function __construct(private readonly InstrumentationService $instrumentationService)
    {
    }

    #[Override]
    public function __invoke(): void
    {
        $instrumentation = new CachedInstrumentation('gibsonOS.store');

        $this->instrumentationService->addHook(
            $instrumentation,
            AbstractStore::class,
            'getList',
        );
    }
}
