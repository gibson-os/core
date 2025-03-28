<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Event;

use GibsonOS\Core\Attribute\Event;
use GibsonOS\Core\Attribute\GetClassNames;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Store\AbstractStore;
use ReflectionAttribute;
use ReflectionException;

class ClassNameStore extends AbstractStore
{
    /**
     * @var array[]
     */
    private array $list = [];

    public function __construct(
        private readonly ReflectionManager $reflectionManager,
        #[GetClassNames(['*/src/Event'])]
        private readonly array $classNames,
    ) {
    }

    /**
     * @throws ReflectionException
     *
     * @return array[]
     */
    public function getList(): array
    {
        $this->generateList();

        return $this->list;
    }

    /**
     * @throws ReflectionException
     */
    public function getCount(): int
    {
        return count($this->getList());
    }

    /**
     * @throws ReflectionException
     */
    private function generateList(): void
    {
        if ($this->list !== []) {
            return;
        }

        $classNames = [];

        foreach ($this->classNames as $className) {
            $reflectionClass = $this->reflectionManager->getReflectionClass($className);
            $eventAttribute = $this->reflectionManager->getAttribute(
                $reflectionClass,
                Event::class,
                ReflectionAttribute::IS_INSTANCEOF,
            );

            if ($eventAttribute === null) {
                continue;
            }

            $classNames[$eventAttribute->getTitle()] = [
                'className' => $className,
                'title' => $eventAttribute->getTitle(),
            ];
        }

        ksort($classNames);
        $this->list = array_values($classNames);
    }
}
