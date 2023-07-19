<?php
declare(strict_types=1);

namespace GibsonOS\Core\OpenTelemetry\Instrumentation;

use GibsonOS\Core\Utility\JsonUtility;
use mysqli;
use OpenTelemetry\API\Common\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Context\Context;

use function OpenTelemetry\Instrumentation\hook;

use Throwable;

class MysqliInstrumentation implements InstrumentationInterface
{
    public function __invoke(): void
    {
        $instrumentation = new CachedInstrumentation('mysqli');

        hook(
            mysqli::class,
            'query',
            pre: function (
                mysqli $mysqli,
                array $params,
                string $class,
                string $function,
                ?string $filename,
                ?int $lineno,
            ) use ($instrumentation) {
                $span = $instrumentation->tracer()->spanBuilder('Mysqli query')->setSpanKind(SpanKind::KIND_CLIENT)->startSpan();
                error_log(JsonUtility::encode($params));
                $parent = Context::getCurrent();
                Context::storage()->attach($span->storeInContext($parent));
            },
            post: function (mysqli $mysqli, array $params, mixed $statement, ?Throwable $exception) {
                $scope = Context::storage()->scope();
                $scope->detach();
                $span = Span::fromContext($scope->context());

                if ($exception) {
                    $span->recordException($exception);
                    $span->setStatus(StatusCode::STATUS_ERROR);
                }

                $span->end();
            }
        );
    }
}
