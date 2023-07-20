<?php
declare(strict_types=1);

namespace GibsonOS\Core\OpenTelemetry\Instrumentation;

use GibsonOS\Core\Service\OpenTelemetry\InstrumentationService;
use GibsonOS\Core\Service\OpenTelemetry\SpanService;
use mysqli;
use mysqli_stmt;
use OpenTelemetry\API\Common\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\SpanKind;

class MysqliInstrumentation implements InstrumentationInterface
{
    public function __construct(
        private readonly InstrumentationService $instrumentationService,
        private readonly SpanService $spanService,
    ) {
    }

    public function __invoke(): void
    {
        $instrumentation = new CachedInstrumentation('mysqli');

        $this->instrumentationService
            ->addHook(
                $instrumentation,
                mysqli::class,
                'query',
                function (
                    mysqli $mysqli,
                    array $params,
                    string $class,
                    string $function,
                    ?string $fileName,
                    ?int $lineNumber,
                ) use ($instrumentation): void {
                    $this->spanService->buildFromInstrumentation(
                        $instrumentation,
                        sprintf('send query: %s', $params[0]),
                        $fileName,
                        $lineNumber,
                        SpanKind::KIND_CLIENT,
                    );
                }
            )
            ->addHook(
                $instrumentation,
                mysqli::class,
                'prepare',
                function (
                    mysqli $mysqli,
                    array $params,
                    string $class,
                    string $function,
                    ?string $fileName,
                    ?int $lineNumber,
                ) use ($instrumentation): void {
                    $this->spanService->buildFromInstrumentation(
                        $instrumentation,
                        sprintf('prepare query: %s', $params[0]),
                        $fileName,
                        $lineNumber,
                        SpanKind::KIND_CLIENT,
                    );
                }
            )
            ->addHook(
                $instrumentation,
                mysqli_stmt::class,
                'execute',
                function (
                    mysqli_stmt $mysqliStatement,
                    array $params,
                    string $class,
                    string $function,
                    ?string $fileName,
                    ?int $lineNumber,
                ) use ($instrumentation): void {
                    $this->spanService->buildFromInstrumentation(
                        $instrumentation,
                        'execute query',
                        $fileName,
                        $lineNumber,
                        SpanKind::KIND_CLIENT,
                    );
                }
            )
        ;
    }
}
