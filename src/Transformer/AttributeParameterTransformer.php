<?php
declare(strict_types=1);

namespace GibsonOS\Core\Transformer;

use GibsonOS\Core\Exception\MapperException;
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
     * @throws MapperException
     * @throws RequestError
     * @throws ReflectionException
     */
    public function transform(array $parameters): array
    {
        $values = [];

        foreach ($parameters as $parameterKey => $parameter) {
            $parameterParts = explode('.', $parameter);

            if ($parameterParts[0] === 'value') {
                $values[$parameterKey] = $parameterParts[1];

                continue;
            }

            $value = match ($parameterParts[0]) {
                'session' => $this->sessionService->get($parameterParts[1]),
                default => JsonUtility::decode((string) $this->requestService->getRequestValue($parameterParts[0])),
            };

            $count = count($parameterParts);

            for ($i = $parameterParts[0] === 'session' ? 2 : 1; $i < $count; ++$i) {
                if (is_array($value)) {
                    $value = $value[$parameterParts[$i]] ?? null;

                    continue;
                }

                if (!is_object($value)) {
                    throw new MapperException(sprintf(
                        'Value for %s is no object or array',
                        $parameterParts[$i],
                    ));
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
}
