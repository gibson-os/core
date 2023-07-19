<?php
/** @noinspection PhpComposerExtensionStubsInspection */
declare(strict_types=1);

namespace GibsonOS\Core\Tracer;

use GibsonOS\Core\Attribute\GetEnv;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\ScopeInterface;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

class OpenTelemetryTracer extends AbstractTracer
{
    /**
     * @var TransportInterface<'application/x-protobuf'>
     */
    private TransportInterface $transport;

    private TracerProvider $tracerProvider;

    private TracerInterface $tracer;

    private SpanInterface $root;

    private SpanInterface $span;

    private ScopeInterface $scope;

    public function __construct(
        #[GetEnv('OTEL_EXPORTER_OTLP_ENDPOINT')] private readonly ?string $endpoint,
    ) {
        if (!$this->isLoaded()) {
            return;
        }

        /** @var TransportInterface<'application/x-protobuf'> $transport */
        $transport = (new OtlpHttpTransportFactory())->create(
            sprintf('http://%s:4318/v1/traces', $this->endpoint ?? ''),
            'application/x-protobuf'
        );
        $this->transport = $transport;

        $this->tracerProvider = new TracerProvider(
            new SimpleSpanProcessor(
                new SpanExporter($this->transport)
            )
        );
    }

    public function isLoaded(): bool
    {
        return $this->endpoint !== null;
    }

    public function setTransactionName(string $transactionName): OpenTelemetryTracer
    {
        $this->tracer = $this->tracerProvider->getTracer('gibsonOS');
        $this->root = $this->span = $this->tracer->spanBuilder($transactionName ?: 'index')->startSpan();
        $this->scope = $this->span->activate();

        return $this;
    }

    public function setCustomParameter(string $key, mixed $value): OpenTelemetryTracer
    {
        if ($key !== '') {
            $this->span->setAttribute($key, $value);
        }

        return $this;
    }

    public function addSpan(string $spanName, array $attributes = []): OpenTelemetryTracer
    {
        if ($spanName === '') {
            return $this;
        }

        $this->span = $this->tracer->spanBuilder($spanName)->startSpan();
        $this->span->setAttributes($attributes);

        return $this;
    }

    public function addEvent(string $eventName, array $attributes): self
    {
        $this->span->addEvent($eventName, $attributes);

        return $this;
    }

    public function __destruct()
    {
        if (!$this->isLoaded()) {
            return;
        }

        $this->span->end();
        $this->root->end();
        $this->scope->detach();
    }
}
