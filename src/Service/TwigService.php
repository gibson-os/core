<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigService
{
    /**
     * @var Environment
     */
    private $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader();
        $loader->addPath(realpath(dirname(__FILE__)) . '/../../template', 'core');

        $this->twig = new Environment($loader);
    }

    public function getTwig(): Environment
    {
        return $this->twig;
    }
}
