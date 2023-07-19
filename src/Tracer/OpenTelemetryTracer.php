<?php
/** @noinspection PhpComposerExtensionStubsInspection */
declare(strict_types=1);

namespace GibsonOS\Core\Tracer;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Attribute\GetServices;
use GibsonOS\Core\OpenTelemetry\Instrumentation\InstrumentationInterface;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\Context\Context;

class OpenTelemetryTracer extends AbstractTracer
{
    public function __construct(
        #[GetEnv('OTEL_EXPORTER_OTLP_ENDPOINT')] private readonly ?string $endpoint,
        #[GetServices(['*/src/OpenTelemetry/Instrumentation'], InstrumentationInterface::class)] private readonly array $instrumentations,
    ) {
        if (!$this->isLoaded()) {
            return;
        }

        foreach ($this->instrumentations as $instrumentation) {
            $instrumentation();
        }

        //
        //        $spanProcessor = new BatchSpanProcessor(
        //            new OtlpExporter(),
        //            new SystemClock(),
        //        );
        //        $this->tracerProvider = (new TracerProviderBuilder())
        //            ->addSpanProcessor($spanProcessor)
        //            ->setSampler(new ParentBased(new AlwaysOnSampler()))
        //            ->build();
        //        $scope = Configurator::create()
        //            ->withTracerProvider($this->tracerProvider)
        //            ->activate();

        //        Globals::registerInitializer(function (Configurator $configurator) {
        //            $propagator = TraceContextPropagator::getInstance();
        //            $spanProcessor = new BatchSpanProcessor(
        //                new OtlpExporter(),
        //                new SystemClock(),
        //            );
        //            $tracerProvider = (new TracerProviderBuilder())
        //                ->addSpanProcessor($spanProcessor)
        //                ->setSampler(new ParentBased(new AlwaysOnSampler()))
        //                ->build();
        //
        //            ShutdownHandler::register([$tracerProvider, 'shutdown']);
        //
        //            return $configurator
        //                ->withTracerProvider($tracerProvider)
        //                ->withPropagator($propagator)
        //            ;
        //        });
        //        if (!$this->isLoaded()) {
        //            return;
        //        }
        //
        //        /** @var TransportInterface<'application/x-protobuf'> $transport */
        //        $transport = (new OtlpHttpTransportFactory())->create(
        //            sprintf('http://%s:4318/v1/traces', $this->endpoint ?? ''),
        //            'application/x-protobuf'
        //        );
        //        $this->transport = $transport;
        //
        //        $this->tracerProvider = new TracerProvider(
        //            new SimpleSpanProcessor(
        //                new SpanExporter($this->transport)
        //            )
        //        );
    }

    public function isLoaded(): bool
    {
        return $this->endpoint !== null;
    }

    public function setTransactionName(string $transactionName): OpenTelemetryTracer
    {
        $scope = Context::storage()->scope();
        $span = Span::fromContext($scope->context());
        $span->updateName($transactionName);

        return $this;
    }

    public function setCustomParameter(string $key, mixed $value): OpenTelemetryTracer
    {
        //        if ($key !== '') {
        //            $this->span->setAttribute($key, $value);
        //        }

        return $this;
    }
}
