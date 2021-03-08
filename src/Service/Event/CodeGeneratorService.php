<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Event;

use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Service\AbstractService;
use GibsonOS\Core\Utility\JsonUtility;
use InvalidArgumentException;
use JsonException;

class CodeGeneratorService extends AbstractService
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

    private ?int $parentId = null;

    /**
     * @var Element[]
     */
    private array $parents = [];

    /**
     * @param Element[] $elements
     *
     * @throws JsonException
     */
    public function generateByElements(array $elements): string
    {
        $this->parentId = null;
        $this->parents = [];
        $code = '';

        foreach ($elements as $element) {
            $code .= $this->generateCommandEnd($element);
            $code .= $this->generateCommandStart($element);
        }

        $code .= $this->generateCommandEnd();

        return $code;
    }

    /**
     * @throws JsonException
     */
    private function generateCommandStart(Element $element): string
    {
        $command = '$this->runFunction(unserialize(\'' . str_replace("'", "\\'", serialize($element)) . '\'))';

        if (!empty($element->getCommand())) {
            $this->parentId = (int) $element->getId();
            $this->parents[$this->parentId] = $element;
        }

        $conditionStatement = $this->getConditionStatement($command, $element);

        switch ($element->getCommand()) {
            case self::COMMAND_IF:
                return 'if (' . $conditionStatement . ') {';
            case self::COMMAND_ELSE:
                return '} else {';
            case self::COMMAND_ELSE_IF:
                return '} else if (' . $conditionStatement . ') {';
            case self::COMMAND_WHILE:
                return 'while (' . $conditionStatement . ') {';
            case self::COMMAND_DO_WHILE:
                return '{';
        }

        return $conditionStatement . ';';
    }

    /**
     * @throws JsonException
     */
    private function generateCommandEnd(Element $element = null): string
    {
        if (
            $element instanceof Element &&
            $this->parentId === $element->getParentId()
        ) {
            return '';
        }

        if ($this->parentId !== null) {
            $parent = $this->parents[$this->parentId];
            $command = '$this->runFunction(\'' . serialize($parent) . '\')';
            $return = '';

            switch ($parent->getCommand()) {
                case self::COMMAND_IF:
                case self::COMMAND_ELSE:
                case self::COMMAND_ELSE_IF:
                case self::COMMAND_WHILE:
                    $return = '}';

                    break;
                case self::COMMAND_DO_WHILE:
                    $return = '} while (' . $this->getConditionStatement($command, $parent) . ');';

                    break;
            }

            if ($element === null) {
                $this->parentId = $parent->getParentId();

                $return .= $this->generateCommandEnd();
            }

            if (!empty($return)) {
                return $return;
            }
        }

        if ($element !== null) {
            $this->parentId = $element->getParentId();
        }

        return '';
    }

    /**
     * @throws JsonException
     */
    private function getConditionStatement(string $command, Element $element): string
    {
        $returns = JsonUtility::decode($element->getReturns() ?? '[]');
        $hasOperator = false;
        $statements = [];
        $commandReturnIndex = '';

        foreach ($returns as $index => $return) {
            if ($return['operator'] === null) {
                continue;
            }

            if (count($returns) !== 1) {
                $commandReturnIndex = '[' . $index . ']';
            }

            $hasOperator = true;

            if ($return['operator'] === self::OPERATOR_SET) {
                //$statement .= '$' . reset($returns['operator']) . ' = $commandReturn' . $commandReturnIndex . ';';

                continue;
            }

            $statements[] = '$commandReturn' . $commandReturnIndex . ' ' . $return['operator'] . ' ' . $return['value'];
        }

        if ($hasOperator) {
            return
                'static function(): bool {' .
                    '$commandReturn = ' . $command . ';' .
                    'return ' . implode(' && ', $statements) . ';' .
                '}'
            ;
        }

        return $command;
    }

    /**
     * @param $value1
     * @param $value2
     */
    public function if($value1, string $operator, $value2): bool
    {
        switch ($operator) {
            case self::OPERATOR_EQUAL: return $value1 === $value2;
            case self::OPERATOR_NOT_EQUAL: return $value1 !== $value2;
            case self::OPERATOR_BIGGER: return $value1 > $value2;
            case self::OPERATOR_BIGGER_EQUAL: return $value1 >= $value2;
            case self::OPERATOR_SMALLER: return $value1 < $value2;
            case self::OPERATOR_SMALLER_EQUAL: return $value1 <= $value2;
            default: throw new InvalidArgumentException(sprintf('Operator "%s" not allowed!', $operator));
        }
    }
}
