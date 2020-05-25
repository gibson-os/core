<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\GetError;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;

class TwigService
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @throws GetError
     * @throws LoaderError
     */
    public function __construct(DirService $dirService)
    {
        $projectPath = realpath(
            dirname(__FILE__) . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..'
        ) . DIRECTORY_SEPARATOR;
        $loader = new FilesystemLoader();
        $loader->addPath($projectPath . 'template', 'core');
        $projectPath = realpath(
            $projectPath .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR
        );

        foreach ($dirService->getFiles($projectPath . 'vendor' . DIRECTORY_SEPARATOR . 'gibson-os') as $path) {
            $templatePath = $dirService->addEndSlash($path) . 'templates';

            if (!is_dir($templatePath)) {
                continue;
            }

            $loader->addPath(
                $templatePath,
                $dirService->removeEndSlash(str_replace(
                    $projectPath . 'vendor' . DIRECTORY_SEPARATOR . 'gibson-os' . DIRECTORY_SEPARATOR,
                    '',
                    $path
                ))
            );
        }

        $this->twig = new Environment($loader);
    }

    public function getTwig(): Environment
    {
        return $this->twig;
    }
}
