<?php
declare(strict_types=1);

namespace GibsonOS\Core\OpenTelemetry\Instrumentation;

use GibsonOS\Core\Service\ControllerService;
use OpenTelemetry\API\Common\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Context\Context;

use function OpenTelemetry\Instrumentation\hook;

use Throwable;

class ControllerInstrumentation implements InstrumentationInterface
{
    public function __invoke(): void
    {
        $instrumentation = new CachedInstrumentation('gibsonOS.controller');

        hook(
            ControllerService::class,
            'runAction',
            pre: function (
                ControllerService $controllerService,
                array $params,
                string $class,
                string $function,
                ?string $filename,
                ?int $lineno,
            ) use ($instrumentation) {
                $span = $instrumentation->tracer()->spanBuilder(sprintf('%s::%s', $class, $function))->startSpan();
                Context::storage()->attach($span->storeInContext(Context::getCurrent()));
            },
            post: function (ControllerService $controllerService, array $params, mixed $statement, ?Throwable $exception) {
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
