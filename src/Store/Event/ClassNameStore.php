<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Event;

use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\Event\Describer\DescriberInterface;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Core\Store\AbstractStore;

class ClassNameStore extends AbstractStore
{
    /**
     * @var array[]
     */
    private $list = [];

    /**
     * @var DirService
     */
    private $dir;

    /**
     * @var FileService
     */
    private $file;

    public function __construct(DirService $dir, FileService $file)
    {
        $this->dir = $dir;
        $this->file = $file;
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
     */
    private function generateList(): void
    {
        if (count($this->list) !== 0) {
            return;
        }

        $namespace = 'GibsonOS\\Module\\Hc\\Service\\Event\\Describer\\';
        $eventDescriberDir = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            'Service' . DIRECTORY_SEPARATOR .
            'Event' . DIRECTORY_SEPARATOR .
            'Describer' . DIRECTORY_SEPARATOR;
        $classNames = [];

        foreach ($this->dir->getFiles($eventDescriberDir, '*.php') as $classPath) {
            $className = str_replace('.php', '', $this->file->getFilename($classPath));

            if (mb_strpos($className, 'Abstract') !== false) {
                continue;
            }

            if (mb_strpos($className, 'Interface') !== false) {
                continue;
            }

            $classNameWithNamespace = $namespace . $className;
            $class = new $classNameWithNamespace();

            if (!$class instanceof DescriberInterface) {
                continue;
            }

            $classNames[] = [
                'className' => $className,
                'title' => $class->getTitle(),
            ];
        }

        $this->list = $classNames;
    }
}
