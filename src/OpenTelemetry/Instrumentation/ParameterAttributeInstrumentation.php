<?php
declare(strict_types=1);

namespace GibsonOS\Core\OpenTelemetry\Instrumentation;

use GibsonOS\Core\Service\Attribute\ParameterAttributeInterface;
use GibsonOS\Core\Service\OpenTelemetry\InstrumentationService;
use GibsonOS\Core\Service\OpenTelemetry\SpanService;
use OpenTelemetry\API\Common\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\SpanKind;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionParameter;

class ParameterAttributeInstrumentation implements InstrumentationInterface
{
    public function __construct(
        private readonly InstrumentationService $instrumentationService,
        private readonly SpanService $spanService,
    ) {
    }

    public function __invoke(): void
    {
        $instrumentation = new CachedInstrumentation('gibsonOS.parameterAttribute');

        $this->instrumentationService
            ->addHook(
                $instrumentation,
                ParameterAttributeInterface::class,
                'replace',
                function (
                    ParameterAttributeInterface $parameterAttribute,
                    array $params,
                    string $class,
                    string $function,
                    ?string $fileName,
                    ?int $lineNumber,
                ) use ($instrumentation): void {
                    /** @var ReflectionParameter $reflectionParameter */
                    $reflectionParameter = $params[2];
                    $this->spanService->buildFromInstrumentation(
                        $instrumentation,
                        sprintf(
                            'parameter attribute `%s` replace called with parameters %s for parameter `%s` in `%s::%s`',
                            $params[0]::class,
                            json_encode($params[1]),
                            $reflectionParameter->getName(),
                            $reflectionParameter->getDeclaringClass()?->getName() ?? '',
                            $reflectionParameter->getDeclaringFunction()->getName(),
                        ),
                        $fileName,
                        $lineNumber,
                        SpanKind::KIND_CLIENT,
                    );
                },
            )
        ;
    }
}
