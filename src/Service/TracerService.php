<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\GetServices;
use GibsonOS\Core\Enum\TracePrefix;
use GibsonOS\Core\Tracer\AbstractTracer;

class TracerService
{
    /**
     * @param AbstractTracer[] $tracers
     */
    public function __construct(
        #[GetServices(['*/src/Tracer'], AbstractTracer::class)] private readonly array $tracers,
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
    public function addSpan(string $spanName, array $attributes): TracerService
    {
        foreach ($this->tracers as $tracer) {
            if ($tracer->isLoaded()) {
                $tracer->addSpan($spanName, $attributes);
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
