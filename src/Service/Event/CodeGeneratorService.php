<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Event;

use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Service\AbstractService;
use GibsonOS\Core\Utility\JsonUtility;

class CodeGeneratorService extends AbstractService
{
    private const COMMAND_IF = 'if';

    private const COMMAND_ELSE = 'else';

    private const COMMAND_ELSE_IF = 'else_if';

    private const COMMAND_WHILE = 'while';

    private const COMMAND_DO_WHILE = 'do_while';

    private const OPERATOR_SET = '=';

    private ?int $parentId = null;

    /**
     * @var Element[]
     */
    private array $parents = [];

    /**
     * @param Element[] $elements
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

    private function getConditionStatement(string $command, Element $element): string
    {
        if ($element->getOperator() === null) {
            return $command;
        }

        $returns = JsonUtility::decode($element->getReturns() ?? '[]');

        if ($element->getOperator() === self::OPERATOR_SET) {
            return '$' . reset($returns) . ' = ' . $command;
        }

        // @todo multiple returns. Vorstellung: Jedes ELement der Antwort wird durchlaufen und mit jedem gesetzten verglichen
        return $command . ' ' . $element->getOperator() . ' ' . reset($returns);
    }
}
