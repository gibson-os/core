<?php
declare(strict_types=1);

namespace GibsonOS\Core\OpenTelemetry\Instrumentation;

use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Service\OpenTelemetry\InstrumentationService;
use GibsonOS\Core\Service\OpenTelemetry\SpanService;
use GibsonOS\Core\Service\WebService;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\SpanKind;
use Override;

class WebInstrumentation implements InstrumentationInterface
{
    public function __construct(
        private readonly InstrumentationService $instrumentationService,
        private readonly SpanService $spanService,
    ) {
    }

    #[Override]
    public function __invoke(): void
    {
        $instrumentation = new CachedInstrumentation('gibsonOS.web');
        $pre = function (
            WebService $webService,
            array $params,
            string $class,
            string $function,
            ?string $fileName,
            ?int $lineNumber,
        ) use ($instrumentation): void {
            /** @var Request $request */
            $request = $params[0];
            $method = $params[1] ?? $request->getMethod();
            $this->spanService->buildFromInstrumentation(
                $instrumentation,
                sprintf('%s %s', $method?->value ?? '', $request->getUrl()),
                $fileName,
                $lineNumber,
                SpanKind::KIND_CLIENT,
            );
        };

        $this->instrumentationService
            ->addHook(
                $instrumentation,
                WebService::class,
                'requestWithOutput',
                $pre,
            )
            ->addHook(
                $instrumentation,
                WebService::class,
                'request',
                $pre,
            )
        ;
    }
}
