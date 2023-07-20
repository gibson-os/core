<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\OpenTelemetry;

use OpenTelemetry\API\Common\Instrumentation\CachedInstrumentation;

use function OpenTelemetry\Instrumentation\hook;

use Throwable;

class InstrumentationService
{
    public function __construct(private readonly SpanService $spanService)
    {
    }

    /**
     * @param class-string|null $className
     */
    public function addHook(
        CachedInstrumentation $instrumentation,
        ?string $className,
        string $functionName,
        callable $pre = null,
        callable $post = null,
    ): InstrumentationService {
        $pre ??= function (
            ?object $object,
            array $params,
            string $class,
            string $function,
            ?string $fileName,
            ?int $lineNumber,
        ) use ($instrumentation): void {
            $this->spanService->buildFromInstrumentation(
                $instrumentation,
                sprintf('%s::%s', $object::class, $function),
                $fileName,
                $lineNumber
            );
        };
        $post ??= function (?object $object, array $params, mixed $statement, ?Throwable $exception): void {
            $this->spanService->detachCurrentSpan($exception);
        };

        /** @psalm-suppress UndefinedFunction */
        hook(
            $className,
            $functionName,
            pre: $pre,
            post: $post
        );

        return $this;
    }
}
