<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\GetError;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;

class TwigService
{
    private readonly Environment $twig;

    /**
     * @throws GetError
     * @throws LoaderError
     */
    public function __construct(DirService $dirService)
    {
        $vendorPath = (realpath(
            dirname(__FILE__) . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR,
        ) ?: '') . DIRECTORY_SEPARATOR;
        $loader = new FilesystemLoader();

        foreach ($dirService->getFiles($vendorPath) as $path) {
            $templatePath = $dirService->addEndSlash($path) . 'template';

            if (!is_dir($templatePath)) {
                continue;
            }

            $loader->addPath($templatePath, $dirService->removeEndSlash(str_replace($vendorPath, '', $path)));
        }

        $this->twig = new Environment($loader);
    }

    public function getTwig(): Environment
    {
        return $this->twig;
    }
}
