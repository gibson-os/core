<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\OpenTelemetry;

use InvalidArgumentException;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextStorageScopeInterface;
use Throwable;

class SpanService
{
    public function getScope(): ?ContextStorageScopeInterface
    {
        return Context::storage()->scope();
    }

    public function getCurrentSpan(): ?SpanInterface
    {
        $scope = $this->getScope();

        return $scope === null ? null : Span::fromContext($scope->context());
    }

    public function detachCurrentSpan(?Throwable $exception = null): SpanService
    {
        $span = $this->getCurrentSpan();

        if ($span === null) {
            return $this;
        }

        $this->getScope()?->detach();

        if ($exception !== null) {
            $span->recordException($exception);
            $span->setStatus(StatusCode::STATUS_ERROR);
        }

        $span->end();

        return $this;
    }

    /**
     * @psalm-param SpanKind::KIND_* $spanKind
     */
    public function buildFromInstrumentation(CachedInstrumentation $instrumentation, string $spanName, ?string $fileName = null, ?int $lineNumber = null, int $spanKind = SpanKind::KIND_INTERNAL, ?SpanContextInterface $link = null): SpanInterface
    {
        if ($spanName === '') {
            throw new InvalidArgumentException('Span name is empty!');
        }

        $spanBuilder = $instrumentation
            ->tracer()
            ->spanBuilder($spanName)
            ->setSpanKind($spanKind)
        ;

        if ($link !== null) {
            $spanBuilder->addLink($link);
        }

        $span = $spanBuilder->startSpan();

        if ($fileName !== null) {
            $span->setAttribute('app.fileName', $fileName);
        }

        if ($lineNumber !== null) {
            $span->setAttribute('app.lineNumber', $lineNumber);
        }

        Context::storage()->attach($span->storeInContext(Context::getCurrent()));

        return $span;
    }

    public function getTraceId(): ?string
    {
        return $this->getCurrentSpan()?->getContext()->getTraceId();
    }

    public function getSpanId(): ?string
    {
        return $this->getCurrentSpan()?->getContext()->getSpanId();
    }
}
