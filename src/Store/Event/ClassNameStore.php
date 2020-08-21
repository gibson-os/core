<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Event;

use GibsonOS\Core\Event\Describer\DescriberInterface;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Core\Service\ServiceManagerService;
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

    /**
     * @var ServiceManagerService
     */
    private $serviceManagerService;

    public function __construct(DirService $dir, FileService $file, ServiceManagerService $serviceManagerService)
    {
        $this->dir = $dir;
        $this->file = $file;
        $this->serviceManagerService = $serviceManagerService;
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

            $eventDescriberDir = $moduleDir . DIRECTORY_SEPARATOR .
                'src' . DIRECTORY_SEPARATOR .
                'Event' . DIRECTORY_SEPARATOR .
                'Describer' . DIRECTORY_SEPARATOR;
            $moduleName = ucfirst(str_replace($vendorDir, '', $moduleDir));
            $namespace =
                'GibsonOS\\' .
                ($moduleName === 'Core' ? '' : 'Module\\') . $moduleName .
                '\\Event\\Describer\\'
            ;

            foreach ($this->dir->getFiles($eventDescriberDir, '*.php') as $classPath) {
                $className = str_replace('.php', '', $this->file->getFilename($classPath));
                $classNameWithNamespace = $namespace . $className;

                try {
                    $describer = $this->serviceManagerService->get($classNameWithNamespace);
                } catch (FactoryError $e) {
                    continue;
                }

                if (!$describer instanceof DescriberInterface) {
                    continue;
                }

                $classNames[] = [
                    'describerClass' => $classNameWithNamespace,
                    'eventClass' => $describer->getEventClassName(),
                    'title' => $describer->getTitle(),
                ];
            }
        }

        $this->list = $classNames;
    }
}