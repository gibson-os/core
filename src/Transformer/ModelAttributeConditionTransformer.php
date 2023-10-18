<?php
declare(strict_types=1);

namespace GibsonOS\Core\Transformer;

use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\SessionService;
use ReflectionException;

class ModelAttributeConditionTransformer
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
    public function transform(array $conditions): array
    {
        $values = [];

        foreach ($conditions as $conditionKey => $condition) {
            $conditionParts = explode('.', $condition);
            $count = count($conditionParts);

            if ($count === 1) {
                $values[$conditionKey] = $this->requestService->getRequestValue($condition);

                continue;
            }

            if ($conditionParts[0] === 'session') {
                $value = $this->sessionService->get($conditionParts[1]);

                if ($count < 3) {
                    $values[$conditionKey] = $value;

                    continue;
                }

                for ($i = 2; $i < $count; ++$i) {
                    if (is_array($value)) {
                        $value = $value[$conditionParts[$i]];

                        continue;
                    }

                    if (!is_object($value)) {
                        throw new MapperException(sprintf(
                            'Value for %s is no object or array',
                            $conditionParts[$i],
                        ));
                    }

                    $reflectionClass = $this->reflectionManager->getReflectionClass($value);
                    $value = $this->reflectionManager->getProperty(
                        $reflectionClass->getProperty($conditionParts[$i]),
                        $value,
                    );
                }

                $values[$conditionKey] = $value;
            }

            if ($conditionParts[0] === 'value') {
                $values[$conditionKey] = $conditionParts[1];
            }
        }

        return $values;
    }
}
