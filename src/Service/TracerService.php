<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\GetServices;
use GibsonOS\Core\Enum\TracePrefix;
use GibsonOS\Core\Tracer\AbstractTracer;
use Throwable;

class TracerService
{
    /**
     * @param AbstractTracer[] $tracers
     */
    public function __construct(
        #[GetServices(['core/src/Tracer'], AbstractTracer::class)] private readonly array $tracers,
    ) {
    }

    public function setTransactionName(string $transactionName): TracerService
    {
        foreach ($this->tracers as $tracer) {
            if ($tracer->isLoaded()) {
                $tracer->setTransactionName($transactionName);
            }
        }

        return $this;
    }

    public function setCustomParameter(string $key, mixed $value): TracerService
    {
        foreach ($this->tracers as $tracer) {
            if ($tracer->isLoaded()) {
                $tracer->setCustomParameter($key, $value);
            }
        }

        return $this;
    }

    /**
     * @param array<string, mixed> $values
     */
    public function setCustomParameters(array $values, TracePrefix $prefix = TracePrefix::NONE): TracerService
    {
        foreach ($this->tracers as $tracer) {
            if ($tracer->isLoaded()) {
                $tracer->setCustomParameters($values, $prefix);
            }
        }

        return $this;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function startSpan(string $spanName, array $attributes): TracerService
    {
        foreach ($this->tracers as $tracer) {
            if ($tracer->isLoaded()) {
                $tracer->startSpan($spanName, $attributes);
            }
        }

        return $this;
    }

    public function stopSpan(Throwable $exception = null): TracerService
    {
        foreach ($this->tracers as $tracer) {
            if ($tracer->isLoaded()) {
                $tracer->stopSpan($exception);
            }
        }

        return $this;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function addEvent(string $eventName, array $attributes): TracerService
    {
        foreach ($this->tracers as $tracer) {
            if ($tracer->isLoaded()) {
                $tracer->addEvent($eventName, $attributes);
            }
        }

        return $this;
    }
}
