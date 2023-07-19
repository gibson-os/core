<?php
declare(strict_types=1);

namespace GibsonOS\Core\OpenTelemetry\Instrumentation;

use GibsonOS\Core\Store\AbstractStore;
use OpenTelemetry\API\Common\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Context\Context;

use function OpenTelemetry\Instrumentation\hook;

use Throwable;

class StoreInstrumentation implements InstrumentationInterface
{
    public function __invoke(): void
    {
        $instrumentation = new CachedInstrumentation('gibsonOS.store');

        hook(
            AbstractStore::class,
            'getList',
            pre: function (
                AbstractStore $store,
                array $params,
                string $class,
                string $function,
                ?string $filename,
                ?int $lineno,
            ) use ($instrumentation) {
                $parent = Context::getCurrent();
                $span = $instrumentation
                    ->tracer()
                    ->spanBuilder(sprintf('%s::%s', $store::class, $function))
                    ->setSpanKind(SpanKind::KIND_CLIENT)
                    ->setParent($parent)
                    ->startSpan()
                ;
                Context::storage()->attach($span->storeInContext($parent));
            },
            post: function (AbstractStore $store, array $params, mixed $statement, ?Throwable $exception) {
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
