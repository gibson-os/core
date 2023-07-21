<?php
/** @noinspection PhpComposerExtensionStubsInspection */
declare(strict_types=1);

namespace GibsonOS\Core\Tracer;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Attribute\GetServices;
use GibsonOS\Core\Enum\TracePrefix;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\OpenTelemetry\Instrumentation\InstrumentationInterface;
use GibsonOS\Core\Service\OpenTelemetry\SpanService;
use GibsonOS\Core\Service\RequestService;
use OpenTelemetry\API\Common\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Common\Instrumentation\Configurator;
use OpenTelemetry\API\Common\Instrumentation\Globals;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Time\SystemClock;
use OpenTelemetry\SDK\Common\Util\ShutdownHandler;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProviderBuilder;
use Throwable;

class OpenTelemetryTracer extends AbstractTracer
{
    private SpanInterface $rootSpan;

    private readonly CachedInstrumentation $instrumentation;

    public function __construct(
        private readonly SpanService $spanService,
        private readonly RequestService $requestService,
        #[GetEnv('OTEL_EXPORTER_OTLP_ENDPOINT')] private readonly ?string $endpoint,
        #[GetServices(['core/src/OpenTelemetry/Instrumentation'], InstrumentationInterface::class)] private readonly array $instrumentations,
    ) {
        if (!$this->isLoaded()) {
            return;
        }

        /** @var TransportInterface<'application/x-protobuf'> $transport */
        $transport = (new OtlpHttpTransportFactory())->create(
            sprintf('%s/v1/traces', $this->endpoint ?? ''),
            'application/x-protobuf'
        );
        $spanProcessor = new BatchSpanProcessor(
            new SpanExporter($transport),
            new SystemClock(),
        );
        $tracerProvider = (new TracerProviderBuilder())
            ->addSpanProcessor($spanProcessor)
            ->setSampler(new ParentBased(new AlwaysOnSampler()))
            ->build()
        ;

        /** @psalm-suppress InternalMethod */
        Globals::registerInitializer(static function (Configurator $configurator) use ($tracerProvider) {
            $propagator = TraceContextPropagator::getInstance();

            return $configurator
                ->withTracerProvider($tracerProvider)
                ->withPropagator($propagator);
        });

        try {
            $link = SpanContext::create(
                $this->requestService->getHeader('X-OpenTelemetry-traceId'),
                $this->requestService->getHeader('X-OpenTelemetry-spanId'),
            );
        } catch (RequestError) {
            $link = null;
        }

        $this->instrumentation = new CachedInstrumentation('gibsonOS');
        $this->rootSpan = $this->spanService->buildFromInstrumentation(
            $this->instrumentation,
            'root',
            link: $link,
        );
        Context::storage()->attach($this->rootSpan->storeInContext(Context::getCurrent()));

        ShutdownHandler::register(function (): void {
            $this->spanService->detachCurrentSpan();
        });
        ShutdownHandler::register([$tracerProvider, 'shutdown']);

        foreach ($this->instrumentations as $instrumentation) {
            $instrumentation();
        }
    }

    public function isLoaded(): bool
    {
        return $this->endpoint !== null && extension_loaded('opentelemetry');
    }

    public function setTransactionName(string $transactionName): OpenTelemetryTracer
    {
        if ($transactionName !== '') {
            $this->rootSpan->updateName($transactionName);
        }

        return $this;
    }

    public function setCustomParameter(string $key, mixed $value, TracePrefix $prefix = TracePrefix::APP): OpenTelemetryTracer
    {
        if ($key !== '') {
            $this->rootSpan->setAttribute($prefix->value . $key, $value);
        }

        return $this;
    }

    public function startSpan(string $spanName, array $attributes = [], TracePrefix $prefix = TracePrefix::APP): OpenTelemetryTracer
    {
        if ($spanName === '') {
            return $this;
        }

        $span = $this->spanService->buildFromInstrumentation($this->instrumentation, $spanName);

        foreach ($attributes as $key => $attribute) {
            $span->setAttribute($prefix->value . $key, $attribute);
        }

        return $this;
    }

    public function stopSpan(Throwable $exception = null): AbstractTracer
    {
        $this->spanService->detachCurrentSpan($exception);

        return $this;
    }

    public function addEvent(string $eventName, array $attributes): self
    {
        $this->spanService->getCurrentSpan()?->addEvent($eventName, $attributes);

        return $this;
    }
}
