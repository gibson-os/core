<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Command\CommandInterface;
use GibsonOS\Core\Exception\CommandError;
use GibsonOS\Core\Exception\FactoryError;

class CommandService
{
    /**
     * @var ServiceManagerService
     */
    private $serviceManager;

    /**
     * @var ProcessService
     */
    private $processService;

    public function __construct(ServiceManagerService $serviceManagerService, ProcessService $processService)
    {
        $this->serviceManager = $serviceManagerService;
        $this->processService = $processService;
    }

    /**
     * @throws FactoryError
     */
    public function execute(string $commandClassname, array $arguments, array $options): int
    {
        /** @var CommandInterface $command */
        $command = $this->serviceManager->get($commandClassname);
        $command
            ->setArguments($arguments)
            ->setOptions($options)
        ;

        return $command->execute();
    }

    public function executeAsync(string $commandClassname, array $arguments, array $options): void
    {
        $commandName = mb_substr(str_replace('Command\\', '', $commandClassname), 0, -7);
        $commandName = preg_replace('/^GibsonOS\\\\(Module\\\\)?/', '', $commandName);

        $this->processService->executeAsync(
            'php bin/command ' . escapeshellarg($commandName) .
            implode(' ', array_map(function ($item) {
                return escapeshellarg('--' . $item);
            }, $arguments)) . ' ' .
            implode(' ', array_map(function ($item) {
                return escapeshellarg('-' . $item);
            }, $options))
        );
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

    public function getOptions(array $options): array
    {
        $optionList = [];

        foreach ($options as $option) {
            if (mb_strpos($option, '--') === 0 || mb_strpos($option, '-') !== 0) {
                continue;
            }

            $optionName = mb_substr($option, 1);
            $optionList[$optionName] = $optionName;
        }

        return $optionList;
    }
}
