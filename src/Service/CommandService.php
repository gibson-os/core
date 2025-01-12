<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\Command\Argument;
use GibsonOS\Core\Attribute\Command\Lock;
use GibsonOS\Core\Attribute\Command\Option;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Command\CommandInterface;
use GibsonOS\Core\Enum\TracePrefix;
use GibsonOS\Core\Exception\ArgumentError;
use GibsonOS\Core\Exception\CommandError;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\Lock\LockException;
use GibsonOS\Core\Exception\Lock\UnlockException;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Service\OpenTelemetry\SpanService;
use GibsonOS\Core\Store\CommandStore;
use JsonException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;

class CommandService
{
    public function __construct(
        private readonly ServiceManager $serviceManager,
        private readonly ProcessService $processService,
        private readonly ReflectionManager $reflectionManager,
        private readonly LockService $lockService,
        private readonly TracerService $tracerService,
        private readonly SpanService $spanService,
    ) {
    }

    /**
     * @param class-string $commandClassname
     * @param string[]     $arguments
     * @param bool[]       $options
     *
     * @throws FactoryError
     * @throws ReflectionException
     * @throws ArgumentError
     * @throws JsonException
     */
    public function execute(string $commandClassname, array $arguments = [], array $options = []): int
    {
        /** @var CommandInterface $command */
        $command = $this->serviceManager->create($commandClassname);
        $reflectionClass = $this->reflectionManager->getReflectionClass($commandClassname);
        $lockAttributes = $reflectionClass->getAttributes(Lock::class, ReflectionAttribute::IS_INSTANCEOF);
        $lockAttribute = null;

        $this->tracerService
            ->setTransactionName($commandClassname)
            ->setCustomParameters($arguments, TracePrefix::COMMAND_ARGUMENT)
            ->setCustomParameters($options, TracePrefix::COMMAND_OPTION)
            ->setCustomParameter('command', true)
        ;

        if (count($lockAttributes) === 1) {
            /** @var Lock $lockAttribute */
            $lockAttribute = $lockAttributes[0]->newInstance();

            try {
                $this->lockService->lock($lockAttribute->getName());
            } catch (LockException) {
                return AbstractCommand::ERROR;
            }
        }

        $this->setCommandArguments($command, $reflectionClass, $arguments);
        $this->setCommandOptions($command, $reflectionClass, $options);

        $return = $command->execute();

        if ($lockAttribute !== null) {
            try {
                $this->lockService->unlock($lockAttribute->getName());
            } catch (UnlockException) {
                return AbstractCommand::ERROR;
            }
        }

        $this->tracerService->setCustomParameter('statusCode', $return);

        return $return;
    }

    /**
     * @param class-string $commandClassname
     */
    public function executeAsync(string $commandClassname, array $arguments = [], array $options = []): void
    {
        $commandName = $this->getCommandName($commandClassname);
        $commandPath = realpath(
            dirname(__FILE__) .
                DIRECTORY_SEPARATOR . '..' .
                DIRECTORY_SEPARATOR . '..' .
                DIRECTORY_SEPARATOR . '..' .
                DIRECTORY_SEPARATOR . '..' .
                DIRECTORY_SEPARATOR . '..',
        ) . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'command';

        $traceId = $this->spanService->getTraceId();
        $spanId = $this->spanService->getSpanId();

        if ($traceId !== null && $spanId !== null) {
            $arguments['openTelemetryTraceId'] = $traceId;
            $arguments['openTelemetrySpanId'] = $spanId;
        }

        $this->processService->executeAsync(
            $commandPath . ' ' . escapeshellarg($commandName) . ' ' .
            implode(' ', array_map(function ($item, $key) {
                return escapeshellarg('--' . $key . '=' . $item);
            }, $arguments, array_keys($arguments))) . ' ' .
            implode(' ', array_map(function ($item) {
                return escapeshellarg('-' . $item);
            }, $options)),
        );
    }

    /**
     * @param class-string $className
     */
    public function getCommandName(string $className): string
    {
        $commandName = mb_substr(str_replace('Command\\', '', $className), 0, -7);

        return preg_replace('/^GibsonOS\\\\(Module\\\\)?/', '', $commandName);
    }

    /**
     * @throws CommandError
     * @throws FactoryError
     * @throws ReflectionException
     */
    public function getCommandClassname(array $arguments): string
    {
        $commandNameParts = null;
        $module = '';
        $classname = '';

        foreach ($arguments as $index => $argument) {
            if ($index === 0 || mb_strpos($argument, '-') === 0) {
                continue;
            }

            $commandNameParts = str_replace(':', '\\', $argument);

            if (!is_string($commandNameParts)) {
                throw new CommandError('Command name parts is no string!');
            }

            $commandNameParts = array_map('ucfirst', explode('\\', $commandNameParts));
            $module = array_shift($commandNameParts);

            $classname = implode('\\', $commandNameParts);

            if ($module === 'Core') {
                $classname = 'GibsonOS\\Core\\Command\\' . $classname . 'Command';

                break;
            }

            $classname = 'GibsonOS\\Module\\' . $module . '\\Command\\' . $classname . 'Command';

            break;
        }

        if (class_exists($classname)) {
            return $classname;
        }

        $possibleCommands = $this->getPossibleCommands();
        $possibleCommands = $this->getPossibleCommandsPart($possibleCommands, $module);
        $possibleCommands = reset($possibleCommands) ?: [];

        foreach ($commandNameParts ?? [] as $commandNamePart) {
            $possibleCommands = $this->getPossibleCommandsPart($possibleCommands, $commandNamePart);
            $possibleCommands = reset($possibleCommands);
        }

        if (is_string($possibleCommands)) {
            return $possibleCommands;
        }

        throw new CommandError('No Command found!');
    }

    /**
     * @param string[] $arguments
     *
     * @throws ArgumentError
     */
    private function setCommandArguments(CommandInterface $command, ReflectionClass $reflectionClass, array $arguments): void
    {
        $argumentProperties = [];

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if (!$this->reflectionManager->hasAttribute(
                $reflectionProperty,
                Argument::class,
                ReflectionAttribute::IS_INSTANCEOF,
            )) {
                continue;
            }

            $name = $reflectionProperty->getName();
            $argumentProperties[] = $name;

            if (!isset($arguments[$name])) {
                if (!$reflectionProperty->hasDefaultValue()) {
                    throw new ArgumentError(sprintf('Required argument "%s" missing!', $name));
                }

                continue;
            }

            $value = match ($this->reflectionManager->getTypeName($reflectionProperty)) {
                'int' => (int) $arguments[$name],
                'float' => (float) $arguments[$name],
                'bool' => $arguments[$name] === 'true' || ((int) $arguments[$name]),
                default => $arguments[$name],
            };

            $command->{'set' . ucfirst($name)}($value);
            unset($arguments[$name]);
        }

        if ($arguments !== []) {
            throw new ArgumentError(sprintf(
                '%s "%s" not allowed! Possible arguments: "%s"',
                count($arguments) > 1 ? 'Arguments' : 'Argument',
                implode('", "', array_keys($arguments)),
                implode('", "', $argumentProperties),
            ));
        }
    }

    /**
     * @param bool[] $options
     *
     * @throws ArgumentError
     */
    private function setCommandOptions(CommandInterface $command, ReflectionClass $reflectionClass, array $options): void
    {
        $optionsProperties = [];

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if (!$this->reflectionManager->hasAttribute(
                $reflectionProperty,
                Option::class,
                ReflectionAttribute::IS_INSTANCEOF,
            )) {
                continue;
            }

            $name = $reflectionProperty->getName();
            $typeName = $this->reflectionManager->getTypeName($reflectionProperty);

            if ($typeName !== 'bool') {
                throw new ArgumentError(sprintf(
                    'Option "%s" is type "%s" must be "bool"!',
                    $name,
                    $typeName ?? 'null',
                ));
            }

            $optionsProperties[] = $name;

            if (!isset($options[$name])) {
                $options[$name] = false;
            }

            $command->{'set' . ucfirst($name)}($options[$name]);
            unset($options[$name]);
        }

        if ($options !== []) {
            throw new ArgumentError(sprintf(
                '%s "%s" not allowed! Possible options: "%s"',
                count($options) > 1 ? 'Options' : 'Option',
                implode('", "', array_keys($options)),
                implode('", "', $optionsProperties),
            ));
        }
    }

    /**
     * @throws FactoryError
     * @throws ReflectionException
     *
     * @psalm-suppress InvalidReturnStatement
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidArrayOffset
     */
    private function getPossibleCommands(): array
    {
        $commands = [];
        $commandStore = $this->serviceManager->get(CommandStore::class);

        foreach ($commandStore->getList() as $command) {
            $commandParts = explode('\\', $command->getCommand());
            $commandPosition = &$commands;

            foreach ($commandParts as $commandPart) {
                if (!isset($commandPosition[$commandPart])) {
                    $commandPosition[$commandPart] = [];
                }

                $commandPosition = &$commandPosition[$commandPart];
            }

            $commandPosition = $command->getClassString();
        }

        return $commands;
    }

    /**
     * @throws CommandError
     */
    private function getPossibleCommandsPart(array &$commands, string $keyPart): array
    {
        $parts = [];

        foreach ($commands as $key => $command) {
            if (mb_stripos($key, $keyPart) === 0) {
                $parts[$key] = $command;
            }
        }

        if (count($parts) > 1) {
            throw new CommandError(sprintf(
                '%s is not unique! Possible is: %s',
                $keyPart,
                implode(', ', array_keys($parts)),
            ));
        }

        return $parts;
    }
}
