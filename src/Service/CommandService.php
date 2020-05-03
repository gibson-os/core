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

    public function __construct()
    {
        $this->serviceManager = new ServiceManagerService();
    }

    /**
     * @throws CommandError
     * @throws FactoryError
     */
    public function execute(array $arguments): int
    {
        /** @var CommandInterface $command */
        $command = $this->serviceManager->get($this->getCommandClassname($arguments))
            ->setArguments($this->getArgument($arguments))
            ->setOptions($this->getOptions($arguments))
        ;

        return $command->execute();
    }

    /**
     * @throws CommandError
     */
    private function getCommandClassname(array $arguments): string
    {
        foreach ($arguments as $index => $argument) {
            if ($index === 0 || mb_strpos($argument, '-') === 0) {
                continue;
            }

            $commandName = explode('\\', $argument);
            $module = 'Core';
            $classname = $commandName[0];

            if (count($commandName) > 1) {
                $module = 'Module\\' . $commandName[0];
                $classname = $commandName[1];
            }

            return 'GibsonOS\\' . $module . '\\Command\\' . $classname;
        }

        throw new CommandError('No Command found!');
    }

    private function getArgument(array $arguments): array
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

    private function getOptions(array $arguments): array
    {
        $options = [];

        foreach ($arguments as $argument) {
            if (mb_strpos($argument, '--') === 0 || mb_strpos($argument, '-') !== 0) {
                continue;
            }

            $options[] = mb_substr($argument, 1);
        }

        return $options;
    }
}
