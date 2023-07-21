<?php
declare(strict_types=1);

namespace GibsonOS\Core\Tracer;

use GibsonOS\Core\Enum\TracePrefix;
use Throwable;

abstract class AbstractTracer
{
    abstract public function isLoaded(): bool;

    abstract public function setTransactionName(string $transactionName): self;

    abstract public function setCustomParameter(string $key, mixed $value, TracePrefix $prefix = TracePrefix::APP): self;

    public function setCustomParameters(array $values, TracePrefix $prefix = TracePrefix::APP): self
    {
        foreach ($values as $key => $value) {
            $this->setCustomParameter($key, $value, $prefix);
        }

        return $this;
    }

    public function startSpan(string $spanName, array $attributes, TracePrefix $prefix = TracePrefix::APP): self
    {
        return $this;
    }

    public function stopSpan(Throwable $exception = null): self
    {
        return $this;
    }

    public function addEvent(string $eventName, array $attributes): self
    {
        return $this;
    }
}
