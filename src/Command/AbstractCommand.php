<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command;

use GibsonOS\Core\Attribute\Command\Option;
use GibsonOS\Core\Service\LoggerService;
use Psr\Log\LoggerInterface;

abstract class AbstractCommand implements CommandInterface
{
    protected const SUCCESS = 0;

    protected const ERROR = 255;

    #[Option('Set verbose level warning')]
    private bool $v = false;

    #[Option('Set verbose level info')]
    private bool $vv = false;

    #[Option('Set verbose level debug')]
    private bool $vvv = false;

    #[Option('Add debug information to log messages')]
    private bool $debug = false;

    public function __construct(protected LoggerInterface $logger)
    {
    }

    abstract protected function run(): int;

    public function execute(): int
    {
        if ($this->logger instanceof LoggerService) {
            $this->logger
                ->setLevel(
                    $this->vvv ? LoggerService::LEVEL_DEBUG : (
                        $this->vv ? LoggerService::LEVEL_INFO :
                        ($this->v ? LoggerService::LEVEL_WARNING : LoggerService::LEVEL_ERROR)
                    )
                )
                ->setWriteOut(true)
                ->setDebug($this->debug)
            ;
        }

        return $this->run();
    }

    public function setV(bool $v): AbstractCommand
    {
        $this->v = $v;

        return $this;
    }

    public function setVv(bool $vv): AbstractCommand
    {
        $this->vv = $vv;

        return $this;
    }

    public function setVvv(bool $vvv): AbstractCommand
    {
        $this->vvv = $vvv;

        return $this;
    }

    public function setDebug(bool $debug): AbstractCommand
    {
        $this->debug = $debug;

        return $this;
    }
}
