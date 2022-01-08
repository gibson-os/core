<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\Command\Argument;
use GibsonOS\Core\Attribute\Command\Option;
use GibsonOS\Core\Command\CommandInterface;
use GibsonOS\Core\Exception\ArgumentError;
use GibsonOS\Core\Exception\CommandError;
use GibsonOS\Core\Exception\FactoryError;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;

class CommandService
{
    public function __construct(private ServiceManagerService $serviceManager, private ProcessService $processService)
    {
    }

    /**
     * @param class-string $commandClassname
     * @param string[]     $arguments
     * @param bool[]       $options
     *
     * @throws FactoryError
     * @throws ReflectionException
     * @throws ArgumentError
     */
    public function execute(string $commandClassname, array $arguments = [], array $options = []): int
    {
        /** @var CommandInterface $command */
        $command = $this->serviceManager->get($commandClassname);
        $reflectionClass = new ReflectionClass($commandClassname);
        $this->setArguments($command, $reflectionClass, $arguments);
        $this->setOptions($command, $reflectionClass, $options);

        return $command->execute();
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
                DIRECTORY_SEPARATOR . '..'
        ) . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'command';

        $this->processService->executeAsync(
            $commandPath . ' ' . escapeshellarg($commandName) . ' ' .
            implode(' ', array_map(function ($item, $key) {
                return escapeshellarg('--' . $key . '=' . $item);
            }, $arguments, array_keys($arguments))) . ' ' .
            implode(' ', array_map(function ($item) {
                return escapeshellarg('-' . $item);
            }, $options))
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
     */
    public function getCommandClassname(array $arguments): string
    {
        foreach ($arguments as $index => $argument) {
            if ($index === 0 || mb_strpos($argument, '-') === 0) {
                continue;
            }

            $commandName = explode('\\', $argument);
            $module = array_shift($commandName);
            $classname = implode('\\', $commandName);

            if ($module === 'Core') {
                return 'GibsonOS\\Core\\Command\\' . $classname . 'Command';
            }

            return 'GibsonOS\\Module\\' . $module . '\\Command\\' . $classname . 'Command';
        }

        throw new CommandError('No Command found!');
    }

    public function getArguments(array $arguments): array
    {
        $argumentList = [];

        foreach ($arguments as $argument) {
            if (mb_strpos($argument, '--') !== 0) {
                continue;
            }

            $argumentArray = explode('=', $argument);
            $argumentList[mb_substr($argumentArray[0], 2)] = $argumentArray[1] ?? null;
        }

        return $argumentList;
    }

    /**
     * @return bool[]
     */
    public function getOptions(array $options): array
    {
        $optionList = [];

        foreach ($options as $option) {
            if (mb_strpos($option, '--') === 0 || mb_strpos($option, '-') !== 0) {
                continue;
            }

            $optionName = mb_substr($option, 1);
            $optionList[$optionName] = true;
        }

        return $optionList;
    }

    /**
     * @param string[] $arguments
     *
     * @throws ArgumentError
     */
    private function setArguments(CommandInterface $command, ReflectionClass $reflectionClass, array $arguments): void
    {
        $argumentProperties = [];

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $argumentAttributes = $reflectionProperty->getAttributes(
                Argument::class,
                ReflectionAttribute::IS_INSTANCEOF
            );

            if (count($argumentAttributes) === 0) {
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

            /** @psalm-suppress UndefinedMethod */
            $value = match ($reflectionProperty->getType()?->getName()) {
                'int' => (int) $arguments[$name],
                'float' => (float) $arguments[$name],
                'bool' => $arguments[$name] === 'true' || ((int) $arguments[$name]),
                default => $arguments[$name],
            };

            $command->{'set' . ucfirst($name)}($value);
            unset($arguments[$name]);
        }

        if (count($arguments) > 0) {
            throw new ArgumentError(sprintf(
                '%s "%s" not allowed! Possible arguments: "%s"',
                count($arguments) > 1 ? 'Arguments' : 'Argument',
                implode('", "', array_keys($arguments)),
                implode('", "', $argumentProperties)
            ));
        }
    }

    /**
     * @param bool[] $options
     *
     * @throws ArgumentError
     */
    private function setOptions(CommandInterface $command, ReflectionClass $reflectionClass, array $options): void
    {
        $optionsProperties = [];

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $optionAttributes = $reflectionProperty->getAttributes(
                Option::class,
                ReflectionAttribute::IS_INSTANCEOF
            );

            if (count($optionAttributes) === 0) {
                continue;
            }

            $name = $reflectionProperty->getName();

            /** @psalm-suppress UndefinedMethod */
            $typeName = $reflectionProperty->getType()?->getName();

            if ($typeName !== 'bool') {
                throw new ArgumentError(sprintf('Argument "%s" is type "%s" must be "bool"!', $name, $typeName));
            }

            $optionsProperties[] = $name;

            if (!isset($options[$name])) {
                $options[$name] = false;
            }

            $command->{'set' . ucfirst($name)}($options[$name]);
            unset($options[$name]);
        }

        if (count($options) > 0) {
            throw new ArgumentError(sprintf(
                '%s "%s" not allowed! Possible arguments: "%s"',
                count($options) > 1 ? 'Options' : 'Option',
                implode('", "', array_keys($options)),
                implode('", "', $optionsProperties)
            ));
        }
    }
}
