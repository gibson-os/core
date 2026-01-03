<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command;

use GibsonOS\Core\Attribute\Command\Argument;
use GibsonOS\Core\Attribute\Command\Option;
use GibsonOS\Core\Service\LoggerService;
use Override;
use Psr\Log\LoggerInterface;

abstract class AbstractCommand implements CommandInterface
{
    public const SUCCESS = 0;

    public const ERROR = 255;

    #[Option('Set verbose level warning')]
    protected bool $v = false;

    #[Option('Set verbose level info')]
    protected bool $vv = false;

    #[Option('Set verbose level debug')]
    protected bool $vvv = false;

    #[Option('Add debug information to log messages')]
    protected bool $debug = false;

    #[Argument('OpenTelemetry span ID')]
    protected ?string $openTelemetrySpanId = null;

    #[Argument('OpenTelemetry trace ID')]
    protected ?string $openTelemetryTraceId = null;

    public function __construct(protected LoggerInterface $logger)
    {
    }

    abstract protected function run(): int;

    #[Override]
    public function execute(): int
    {
        if ($this->logger instanceof LoggerService) {
            $this->logger
                ->setLevel(
                    $this->vvv ? LoggerService::LEVEL_DEBUG : (
                        $this->vv ? LoggerService::LEVEL_INFO :
                        ($this->v ? LoggerService::LEVEL_WARNING : LoggerService::LEVEL_ERROR)
                    ),
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

    public function setOpenTelemetrySpanId(?string $openTelemetrySpanId): AbstractCommand
    {
        $this->openTelemetrySpanId = $openTelemetrySpanId;

        return $this;
    }

    public function setOpenTelemetryTraceId(?string $openTelemetryTraceId): AbstractCommand
    {
        $this->openTelemetryTraceId = $openTelemetryTraceId;

        return $this;
    }
}
