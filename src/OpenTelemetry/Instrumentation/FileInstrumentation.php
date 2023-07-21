<?php
declare(strict_types=1);

namespace GibsonOS\Core\OpenTelemetry\Instrumentation;

use GibsonOS\Core\Enum\TracePrefix;
use GibsonOS\Core\Service\OpenTelemetry\InstrumentationService;
use GibsonOS\Core\Service\OpenTelemetry\SpanService;
use OpenTelemetry\API\Common\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\SpanKind;

class FileInstrumentation implements InstrumentationInterface
{
    public function __construct(
        private readonly InstrumentationService $instrumentationService,
        private readonly SpanService $spanService,
    ) {
    }

    public function __invoke(): void
    {
        $instrumentation = new CachedInstrumentation('gibsonOS.web');
        $pre = function (
            null $object,
            array $params,
            string $class,
            string $function,
            ?string $fileName,
            ?int $lineNumber,
        ): void {
        };

        $this->instrumentationService
            ->addHook(
                $instrumentation,
                null,
                'fopen',
                function (
                    null $object,
                    array $params,
                    null $class,
                    string $function,
                    ?string $fileName,
                    ?int $lineNumber,
                ) use ($instrumentation): void {
                    $this->spanService->buildFromInstrumentation(
                        $instrumentation,
                        sprintf('open %s', $params[0]),
                        $fileName,
                        $lineNumber,
                        SpanKind::KIND_CLIENT,
                    )->setAttribute(TracePrefix::FILE->value . 'mode', $params[1]);
                },
            )
            ->addHook(
                $instrumentation,
                null,
                'fread',
                function (
                    null $object,
                    array $params,
                    null $class,
                    string $function,
                    ?string $fileName,
                    ?int $lineNumber,
                ) use ($instrumentation): void {
                    $this->spanService->buildFromInstrumentation(
                        $instrumentation,
                        sprintf('read %d bytes', $params[1]),
                        $fileName,
                        $lineNumber,
                        SpanKind::KIND_CLIENT,
                    );
                },
            )
            ->addHook(
                $instrumentation,
                null,
                'fwrite',
                function (
                    null $object,
                    array $params,
                    null $class,
                    string $function,
                    ?string $fileName,
                    ?int $lineNumber,
                ) use ($instrumentation): void {
                    $this->spanService->buildFromInstrumentation(
                        $instrumentation,
                        sprintf('write%s', isset($params[2]) ? $params[2] . ' bytes' : ''),
                        $fileName,
                        $lineNumber,
                        SpanKind::KIND_CLIENT,
                    );
                },
            )
        ;
    }
}
