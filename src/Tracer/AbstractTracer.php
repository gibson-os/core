<?php
declare(strict_types=1);

namespace GibsonOS\Core\Tracer;

use GibsonOS\Core\Enum\TracePrefix;
use Throwable;

abstract class AbstractTracer
{
    abstract public function isLoaded(): bool;

    abstract public function setTransactionName(string $transactionName): self;

    abstract public function setCustomParameter(string $key, mixed $value): self;

    public function setCustomParameters(array $values, TracePrefix $prefix = TracePrefix::NONE): self
    {
        foreach ($values as $key => $value) {
            $this->setCustomParameter($prefix->value . $key, $value);
        }

        return $this;
    }

    public function startSpan(string $spanName, array $attributes): self
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
