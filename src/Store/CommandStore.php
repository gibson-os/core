<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use Generator;
use GibsonOS\Core\Attribute\GetClassNames;
use GibsonOS\Core\Dto\Command;
use GibsonOS\Core\Service\CommandService;
use ReflectionClass;
use ReflectionException;

class CommandStore extends AbstractStore
{
    /**
     * @param class-string[] $classStrings
     */
    public function __construct(
        private CommandService $commandService,
        #[GetClassNames(['*/src/Command'])] private array $classStrings
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function getList(): Generator
    {
        foreach ($this->classStrings as $classString) {
            $reflectionClass = new ReflectionClass($classString);
            $description = '';
            $docComment = $reflectionClass->getDocComment();

            if (!empty($docComment)) {
                $description = (string) preg_replace('/.+@description\s([^\n]*).*/s', '$1', $docComment);
            }

            yield new Command(
                $classString,
                $this->commandService->getCommandName($classString),
                $description,
                [], // @todo auf attribute umbauen. Dann kann das alles per reflektion bgearbeitet werden
                []
            );
        }
    }

    public function getCount(): int
    {
        return count($this->classStrings);
    }
}
