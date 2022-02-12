<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Event;

use GibsonOS\Core\Attribute\Event;
use GibsonOS\Core\Attribute\GetClassNames;
use GibsonOS\Core\Exception\GetError;
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
        private ReflectionManager $reflectionManager,
        #[GetClassNames(['*/src/Event'])] private array $classNames
    ) {
    }

    /**
     * @throws GetError
     *
     * @return array[]
     */
    public function getList(): array
    {
        $this->generateList();

        return $this->list;
    }

    /**
     * @throws GetError
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
        if (count($this->list) !== 0) {
            return;
        }

        $classNames = [];

        foreach ($this->classNames as $className) {
            $reflectionClass = $this->reflectionManager->getReflectionClass($className);
            $eventAttributes = $reflectionClass->getAttributes(Event::class, ReflectionAttribute::IS_INSTANCEOF);

            if (empty($eventAttributes)) {
                continue;
            }

            /** @var Event $eventAttribute */
            $eventAttribute = $eventAttributes[0]->newInstance();

            $classNames[$eventAttribute->getTitle()] = [
                'className' => $className,
                'title' => $eventAttribute->getTitle(),
            ];
        }

        ksort($classNames);
        $this->list = array_values($classNames);
    }
}
