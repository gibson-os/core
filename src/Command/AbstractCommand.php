<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command;

use GibsonOS\Core\Exception\ArgumentError;
use GibsonOS\Core\Service\LoggerService;
use Psr\Log\LoggerInterface;

abstract class AbstractCommand implements CommandInterface
{
    private $argumentsValues = [];

    private $optionsValues = [];

    private $arguments = [];

    private $options = [];

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;

        $this->setOption('v');
        $this->setOption('vv');
        $this->setOption('vvv');
    }

    abstract protected function run(): int;

    /**
     * @throws ArgumentError
     */
    public function execute(): int
    {
        $this->validateArguments();
        $this->validateOptions();

        if ($this->logger instanceof LoggerService) {
            $this->logger
                ->setLevel(
                    $this->hasOption('vvv') ? LoggerService::LEVEL_DEBUG :
                    $this->hasOption('vv') ? LoggerService::LEVEL_INFO :
                    $this->hasOption('v') ? LoggerService::LEVEL_WARNING : LoggerService::LEVEL_ERROR
                )
                ->setWriteOut(true)
            ;
        }

        return $this->run();
    }

    public function setArguments(array $callArguments): CommandInterface
    {
        $this->argumentsValues = $callArguments;

        return $this;
    }

    public function setOptions(array $callOptions): CommandInterface
    {
        $this->optionsValues = $callOptions;

        return $this;
    }

    protected function setArgument(string $name, bool $required): void
    {
        $this->arguments[$name] = $required;
    }

    protected function setOption(string $name): void
    {
        $this->options[$name] = $name;
    }

    /**
     * @throws ArgumentError
     */
    protected function getArgument(string $name): ?string
    {
        return $this->hasArgument($name) ? $this->argumentsValues[$name] : null;
    }

    /**
     * @throws ArgumentError
     */
    protected function hasArgument(string $name): bool
    {
        if (!isset($this->arguments[$name])) {
            throw new ArgumentError(sprintf(
                'Argument %s not allowed! Possible arguments: %s',
                $name,
                implode(', ', array_keys($this->arguments))
            ));
        }

        return isset($this->argumentsValues[$name]);
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
    private function validateArguments(): void
    {
        $argumentsValues = $this->argumentsValues;

        foreach ($this->arguments as $argument => $required) {
            if (isset($argumentsValues[$argument])) {
                unset($argumentsValues[$argument]);

                continue;
            }

            if ($required) {
                throw new ArgumentError(sprintf('Required argument %s missing!', $argument));
            }
        }

        if (count($argumentsValues) > 0) {
            throw new ArgumentError(sprintf(
                'Invalid argument: %s! Possible arguments: %s',
                implode(', ', $argumentsValues),
                implode(', ', array_keys($this->arguments))
            ));
        }
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
