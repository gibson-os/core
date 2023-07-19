<?php
declare(strict_types=1);

namespace GibsonOS\Core\OpenTelemetry\Instrumentation;

interface InstrumentationInterface
{
    public function __invoke(): void;
}
