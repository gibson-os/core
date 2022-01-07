<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Event;

use GibsonOS\Core\Attribute\Event;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Core\Service\ServiceManagerService;
use GibsonOS\Core\Store\AbstractStore;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;

class ClassNameStore extends AbstractStore
{
    /**
     * @var array[]
     */
    private array $list = [];

    public function __construct(
        private DirService $dir,
        private FileService $file,
        private ServiceManagerService $serviceManagerService
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
     * @throws GetError
     * @throws FactoryError
     * @throws ReflectionException
     */
    private function generateList(): void
    {
        if (count($this->list) !== 0) {
            return;
        }

        $classNames = [];
        $vendorDir = realpath(
            dirname(__FILE__) . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            'gibson-os'
        ) . DIRECTORY_SEPARATOR;

        foreach ($this->dir->getFiles($vendorDir) as $moduleDir) {
            if (!is_dir($moduleDir)) {
                continue;
            }

            $eventDir = $moduleDir . DIRECTORY_SEPARATOR .
                'src' . DIRECTORY_SEPARATOR .
                'Event' . DIRECTORY_SEPARATOR
            ;

            foreach ($this->dir->getFiles($eventDir, '*.php') as $classPath) {
                $className = $this->serviceManagerService->getNamespaceByPath($classPath);
                $reflectionClass = new ReflectionClass($className);
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
        }

        ksort($classNames);
        $this->list = array_values($classNames);
    }
}
