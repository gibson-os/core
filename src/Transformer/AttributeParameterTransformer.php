<?php
declare(strict_types=1);

namespace GibsonOS\Core\Transformer;

use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Core\Utility\JsonUtility;
use ReflectionException;

class AttributeParameterTransformer
{
    public function __construct(
        private readonly RequestService $requestService,
        private readonly SessionService $sessionService,
        private readonly ReflectionManager $reflectionManager,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function transform(array $parameters, string $prefix = ''): array
    {
        $values = [];

        foreach ($parameters as $parameterKey => $parameter) {
            $parameterParts = explode('.', $prefix . $parameter);

            if ($parameterParts[0] === 'value') {
                $values[$parameterKey] = $parameterParts[1];

                continue;
            }

            try {
                $value = match ($parameterParts[0]) {
                    'session' => $this->sessionService->get($parameterParts[1]),
                    default => $this->getRequestValue($parameterParts[0]),
                };
            } catch (RequestError) {
                $values[$parameterKey] = null;

                continue;
            }

            $count = count($parameterParts);

            for ($i = $parameterParts[0] === 'session' ? 2 : 1; $i < $count; ++$i) {
                if (is_array($value)) {
                    if (!isset($value[0])) {
                        $value = $value[$parameterParts[$i]] ?? null;

                        continue;
                    }

                    $value = array_map(
                        static fn (mixed $valueItem): mixed => $valueItem[$parameterParts[$i]] ?? null,
                        $value,
                    );

                    continue;
                }

                if (!is_object($value)) {
                    break;
                }

                $reflectionClass = $this->reflectionManager->getReflectionClass($value);

                try {
                    $value = $this->reflectionManager->getProperty(
                        $reflectionClass->getProperty($parameterParts[$i]),
                        $value,
                    );
                } catch (ReflectionException) {
                    $value = null;
                }
            }

            $values[$parameterKey] = $value;
        }

        return $values;
    }

    /**
     * @throws RequestError
     */
    private function getRequestValue(string $key): mixed
    {
        $value = $this->requestService->getRequestValue($key);

        return is_string($value) ? JsonUtility::decode($value) : $value;
    }
}
