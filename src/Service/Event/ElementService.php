<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Event;

use GibsonOS\Core\Event\AbstractEvent;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\EventException;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Model\Event;
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
    public function runElements(array $elements, Event $event, array $variables = []): void
    {
        $previousConditionResult = null;

        foreach ($elements as $element) {
            $previousConditionResult = $this->runElement($element, $event, $previousConditionResult, $variables);
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
    private function runElement(Element $element, Event $event, ?bool $previousConditionResult, array &$variables): ?bool
    {
        $command = $element->getCommand();

        if ($command === null) {
            $this->runFunction($element, $event);

            return null;
        }

        $children = $element->getChildren() ?? [];

        switch ($command) {
            case self::COMMAND_IF:
                $this->throwPreviousConditionIsNotNullException($previousConditionResult);
                $conditionResult = $this->getReturnsConditionResult($element, $event, $variables);

                if ($conditionResult === true) {
                    $this->runElements($children, $event);
                }

                return $conditionResult;
            case self::COMMAND_ELSE:
                $this->throwPreviousConditionIsNullException($previousConditionResult);

                if ($previousConditionResult !== true) {
                    $this->runElements($children, $event);
                }

                return null;
            case self::COMMAND_ELSE_IF:
                $this->throwPreviousConditionIsNullException($previousConditionResult);

                if ($previousConditionResult === false) {
                    $conditionResult = $this->getReturnsConditionResult($element, $event, $variables);

                    if ($conditionResult) {
                        $this->runElements($children, $event);
                    }
                }

                return $conditionResult ?? $previousConditionResult;
            case self::COMMAND_WHILE:
                $this->throwPreviousConditionIsNotNullException($previousConditionResult);

                while ($this->getReturnsConditionResult($element, $event, $variables)) {
                    $this->runElements($children, $event);
                }

                return null;
            case self::COMMAND_DO_WHILE:
                $this->throwPreviousConditionIsNotNullException($previousConditionResult);

                do {
                    $this->runElements($children, $event);
                } while ($this->getReturnsConditionResult($element, $event, $variables));

                return null;
        }

        return null;
    }

    /**
     * @throws FactoryError
     * @throws JsonException
     * @throws EventException
     */
    private function getReturnsConditionResult(Element $element, Event $event, array &$variables): bool
    {
        $returns = JsonUtility::decode($element->getReturns() ?? '[]');
        $returnsCount = count($returns);
        $callReturn = $this->runFunction($element, $event);
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
     * @throws JsonException
     * @throws EventException
     * @throws FactoryError
     *
     * @return mixed
     */
    private function runFunction(Element $element, Event $event)
    {
        /** @var AbstractEvent $service */
        $service = $this->serviceManagerService->get($element->getClass());

        $this->logger->debug('Run event function ' . $element->getClass() . '::' . $element->getMethod());

        return $service->run($element, $event);
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
