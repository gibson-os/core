<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Attribute\GetClassNames;
use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Command\CommandInterface;
use GibsonOS\Core\Dto\Command;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Service\CommandService;
use Override;
use ReflectionAttribute;
use ReflectionException;
use Traversable;

class CommandStore extends AbstractStore
{
    /**
     * @param class-string[] $classStrings
     */
    public function __construct(
        private readonly CommandService $commandService,
        private readonly ReflectionManager $reflectionManager,
        #[GetClassNames(['*/src/Command'])]
        private readonly array $classStrings,
    ) {
    }

    /**
     * @throws ReflectionException
     *
     * @return Traversable<Command>
     */
    #[Override]
    public function getList(): Traversable
    {
        foreach ($this->classStrings as $classString) {
            $reflectionClass = $this->reflectionManager->getReflectionClass($classString);

            if (
                $reflectionClass->isAbstract()
                || $reflectionClass->isInterface()
                || !is_subclass_of($classString, CommandInterface::class)
            ) {
                continue;
            }

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
                [],
                $this->reflectionManager->getAttributes(
                    $reflectionClass,
                    Cronjob::class,
                    ReflectionAttribute::IS_INSTANCEOF,
                ),
            );
        }
    }

    #[Override]
    public function getCount(): int
    {
        return count($this->classStrings);
    }
}
