<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command;

use GibsonOS\Core\Attribute\Command\Option;
use GibsonOS\Core\Exception\ArgumentError;
use GibsonOS\Core\Service\LoggerService;
use Psr\Log\LoggerInterface;

abstract class AbstractCommand implements CommandInterface
{
    private array $optionsValues = [];

    /**
     * @var string[]
     */
    private array $options = [];

    #[Option]
    private bool $v = false;

    #[Option]
    private bool $vv = false;

    #[Option]
    private bool $vvv = false;

    #[Option]
    private bool $debug = false;

    public function __construct(protected LoggerInterface $logger)
    {
        $this->setOption('v');
        $this->setOption('vv');
        $this->setOption('vvv');
        $this->setOption('debug');
    }

    abstract protected function run(): int;

    /**
     * @throws ArgumentError
     */
    public function execute(): int
    {
        $this->validateOptions();

        if ($this->logger instanceof LoggerService) {
            $this->logger
                ->setLevel(
                    $this->hasOption('vvv') ? LoggerService::LEVEL_DEBUG : (
                        $this->hasOption('vv') ? LoggerService::LEVEL_INFO :
                        ($this->hasOption('v') ? LoggerService::LEVEL_WARNING : LoggerService::LEVEL_ERROR)
                    )
                )
                ->setWriteOut(true)
                ->setDebug($this->hasOption('debug'))
            ;
        }

        return $this->run();
    }

    public function setOptions(array $options): CommandInterface
    {
        $this->optionsValues = $options;

        return $this;
    }

    protected function setOption(string $name): void
    {
        $this->options[$name] = $name;
    }

    /**
     * @throws ArgumentError
     */
    protected function hasOption(string $name): bool
    {
        if (!isset($this->options[$name])) {
            throw new ArgumentError(sprintf(
                'Option %s not allowed! Possible options: %s',
                $name,
                implode(', ', array_keys($this->options))
            ));
        }

        return isset($this->optionsValues[$name]);
    }

    /**
     * @throws ArgumentError
     */
    private function validateOptions(): void
    {
        $optionsValues = $this->optionsValues;

        foreach ($this->options as $option) {
            if (isset($optionsValues[$option])) {
                unset($optionsValues[$option]);
            }
        }

        if (count($optionsValues) > 0) {
            throw new ArgumentError(sprintf(
                'Invalid option: %s! Possible options: %s',
                implode(', ', $optionsValues),
                implode(', ', array_keys($this->options))
            ));
        }
    }
}
