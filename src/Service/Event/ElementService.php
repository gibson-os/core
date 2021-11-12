<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Event;

use GibsonOS\Core\Event\AbstractEvent;
use GibsonOS\Core\Event\Describer\DescriberInterface;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\EventException;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Service\AbstractService;
use GibsonOS\Core\Service\ServiceManagerService;
use GibsonOS\Core\Utility\JsonUtility;
use InvalidArgumentException;
use JsonException;
use Psr\Log\LoggerInterface;

class ElementService extends AbstractService
{
    private const COMMAND_IF = 'if';

    private const COMMAND_ELSE = 'else';

    private const COMMAND_ELSE_IF = 'else_if';

    private const COMMAND_WHILE = 'while';

    private const COMMAND_DO_WHILE = 'do_while';

    private const OPERATOR_EQUAL = '===';

    private const OPERATOR_NOT_EQUAL = '!==';

    private const OPERATOR_BIGGER = '>';

    private const OPERATOR_BIGGER_EQUAL = '>=';

    private const OPERATOR_SMALLER = '<';

    private const OPERATOR_SMALLER_EQUAL = '<=';

    private const OPERATOR_SET = '=';

    public function __construct(private LoggerInterface $logger, private ServiceManagerService $serviceManagerService)
    {
    }

    /**
     * @param Element[] $elements
     *
     * @throws DateTimeError
     * @throws FactoryError
     * @throws JsonException
     * @throws EventException
     */
    public function runElements(array $elements, array $variables = []): void
    {
        $previousConditionResult = null;

        foreach ($elements as $element) {
            $previousConditionResult = $this->runElement($element, $previousConditionResult, $variables);
        }
    }

    /**
     * @param $value1
     * @param $value2
     */
    public function getConditionResult($value1, string $operator, $value2): bool
    {
        return match ($operator) {
            self::OPERATOR_EQUAL => $value1 === $value2,
            self::OPERATOR_NOT_EQUAL => $value1 !== $value2,
            self::OPERATOR_BIGGER => $value1 > $value2,
            self::OPERATOR_BIGGER_EQUAL => $value1 >= $value2,
            self::OPERATOR_SMALLER => $value1 < $value2,
            self::OPERATOR_SMALLER_EQUAL => $value1 <= $value2,
            default => throw new InvalidArgumentException(sprintf('Operator "%s" not allowed!', $operator)),
        };
    }

    /**
     * @throws DateTimeError
     * @throws EventException
     * @throws FactoryError
     * @throws JsonException
     */
    private function runElement(Element $element, ?bool $previousConditionResult, array &$variables): ?bool
    {
        $command = $element->getCommand();

        if ($command === null) {
            $this->runFunction($element);

            return null;
        }

        $children = $element->getChildren() ?? [];

        switch ($command) {
            case self::COMMAND_IF:
                $this->throwPreviousConditionIsNotNullException($previousConditionResult);
                $conditionResult = $this->getReturnsConditionResult($element, $variables);

                if ($conditionResult === true) {
                    $this->runElements($children);
                }

                return $conditionResult;
            case self::COMMAND_ELSE:
                $this->throwPreviousConditionIsNullException($previousConditionResult);

                if ($previousConditionResult !== true) {
                    $this->runElements($children);
                }

                return null;
            case self::COMMAND_ELSE_IF:
                $this->throwPreviousConditionIsNullException($previousConditionResult);

                if ($previousConditionResult === false) {
                    $conditionResult = $this->getReturnsConditionResult($element, $variables);

                    if ($conditionResult) {
                        $this->runElements($children);
                    }
                }

                return $conditionResult ?? $previousConditionResult;
            case self::COMMAND_WHILE:
                $this->throwPreviousConditionIsNotNullException($previousConditionResult);

                while ($this->getReturnsConditionResult($element, $variables)) {
                    $this->runElements($children);
                }

                return null;
            case self::COMMAND_DO_WHILE:
                $this->throwPreviousConditionIsNotNullException($previousConditionResult);

                do {
                    $this->runElements($children);
                } while ($this->getReturnsConditionResult($element, $variables));

                return null;
        }

        return null;
    }

    private function getReturnsConditionResult(Element $element, array &$variables): bool
    {
        $returns = JsonUtility::decode($element->getReturns() ?? '[]');
        $returnsCount = count($returns);
        $callReturn = $this->runFunction($element);
        $this->logger->debug('Returns: ' . var_export($callReturn, true));

        if ($returnsCount === 0) {
            return $callReturn;
        }

        if ($returnsCount <= 1) {
            $return = reset($returns);

            return $this->getConditionResult($callReturn, $return['operator'], $return['value']);
        }

        foreach ($returns as $return) {
            if (!$this->getConditionResult($callReturn, $return['operator'], $return['value'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws FactoryError
     * @throws JsonException
     *
     * @return mixed
     */
    private function runFunction(Element $element)
    {
        /** @var DescriberInterface $describer */
        $describer = $this->serviceManagerService->get($element->getClass());
        /** @var AbstractEvent $service */
        $service = $this->serviceManagerService->get($describer->getEventClassName());

        $this->logger->debug('Run event function ' . $element->getClass() . '::' . $element->getMethod());

        return $service->run($element);
    }

    /**
     * @throws EventException
     */
    private function throwPreviousConditionIsNullException(?bool $previousConditionResult): void
    {
        if ($previousConditionResult === null) {
            throw new EventException('Previous condition result is not set!');
        }
    }

    /**
     * @throws EventException
     */
    private function throwPreviousConditionIsNotNullException(?bool $previousConditionResult): void
    {
        if ($previousConditionResult !== null) {
            throw new EventException('Previous condition result is not null!');
        }
    }
}
