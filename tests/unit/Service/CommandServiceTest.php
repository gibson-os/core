<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Service;

use GibsonOS\Core\Command\InstallCommand;
use GibsonOS\Core\Dto\Command;
use GibsonOS\Core\Exception\ArgumentError;
use GibsonOS\Core\Exception\CommandError;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Service\CommandService;
use GibsonOS\Core\Service\ProcessService;
use GibsonOS\Core\Store\CommandStore;
use GibsonOS\Mock\Service\TestCommand;
use GibsonOS\Mock\Service\TestInvalidOptionCommand;
use GibsonOS\Mock\Service\TestRequiresArgumentCommand;
use GibsonOS\Test\Unit\Core\UnitTest;
use Prophecy\Prophecy\ObjectProphecy;

class CommandServiceTest extends UnitTest
{
    private ProcessService|ObjectProphecy $processService;

    private CommandStore|ObjectProphecy $commandStore;

    protected function _before()
    {
        $this->processService = $this->prophesize(ProcessService::class);
        $this->serviceManager->setService(ProcessService::class, $this->processService);
        $this->commandStore = $this->prophesize(CommandStore::class);
        $this->serviceManager->setService(CommandStore::class, $this->commandStore->reveal());
        $this->serviceManager->setService(CommandService::class, new CommandService(
            $this->serviceManager,
            $this->processService->reveal(),
            $this->serviceManager->get(ReflectionManager::class),
        ));
    }

    public function testExecute(): void
    {
        $commandService = $this->serviceManager->get(CommandService::class);

        $this->assertEquals(0, $commandService->execute(TestCommand::class));
        $this->assertEquals(255, $commandService->execute(TestCommand::class, ['arthur' => 'dent']));
        $this->assertEquals(42, $commandService->execute(TestCommand::class, options: ['marvin' => true]));
        $this->assertEquals(0, $commandService->execute(TestCommand::class, options: ['marvin' => false]));
        $this->assertEquals(0, $commandService->execute(TestRequiresArgumentCommand::class, ['arthur' => 'perfect']));
        $this->assertEquals(255, $commandService->execute(TestRequiresArgumentCommand::class, ['arthur' => 'dent']));
    }

    public function testExecuteInvalidArgument(): void
    {
        $commandService = $this->serviceManager->get(CommandService::class);

        $this->expectException(ArgumentError::class);
        $commandService->execute(TestCommand::class, ['galaxy' => 42]);
    }

    public function testExecuteMissingArgument(): void
    {
        $commandService = $this->serviceManager->get(CommandService::class);

        $this->expectException(ArgumentError::class);
        $commandService->execute(TestRequiresArgumentCommand::class);
    }

    public function testExecuteInvalidOption(): void
    {
        $commandService = $this->serviceManager->get(CommandService::class);

        $this->expectException(ArgumentError::class);
        $commandService->execute(TestCommand::class, options: ['trillian' => true]);
    }

    public function testExecuteInvalidOptionType(): void
    {
        $commandService = $this->serviceManager->get(CommandService::class);

        $this->expectException(ArgumentError::class);
        $commandService->execute(TestInvalidOptionCommand::class, options: ['marvin' => true]);
    }

    public function testExecuteInvalidOptionValue(): void
    {
        $commandService = $this->serviceManager->get(CommandService::class);

        $this->expectException(\Throwable::class);
        $commandService->execute(TestCommand::class, options: ['marvin' => 42]);
    }

    public function testExecuteAsync(): void
    {
        $commandService = $this->serviceManager->get(CommandService::class);

        $this->processService->executeAsync(\Prophecy\Argument::containingString('Test"  '))
            ->shouldBeCalledOnce()
        ;
        $commandService->executeAsync(TestCommand::class);

        $this->processService->executeAsync(\Prophecy\Argument::containingString('Test" "--ford=prefect" '))
            ->shouldBeCalledOnce()
        ;
        $commandService->executeAsync(TestCommand::class, ['ford' => 'prefect']);

        $this->processService->executeAsync(\Prophecy\Argument::containingString('Test" "--arthur=dent" "-marvin"'))
            ->shouldBeCalledOnce()
        ;
        $commandService->executeAsync(TestCommand::class, ['arthur' => 'dent'], ['marvin']);
    }

    public function testGetCommandClassname(): void
    {
        $commandService = $this->serviceManager->get(CommandService::class);

        $this->commandStore->getList()
            ->shouldBeCalledTimes(2)
            ->willYield([new Command(
                TestCommand::class,
                'Core\\Install',
                'Test',
            )])
        ;

        $this->assertEquals(InstallCommand::class, $commandService->getCommandClassname(['bin\\command', 'Core\\Install']));
        $this->assertEquals(InstallCommand::class, $commandService->getCommandClassname(['bin\\command', 'core\\install']));
        $this->assertEquals(TestCommand::class, $commandService->getCommandClassname(['bin\\command', 'c\\i']));
        $this->assertEquals(TestCommand::class, $commandService->getCommandClassname(['bin\\command', 'c:i']));
    }

    public function testGetCommandClassnameNotFound(): void
    {
        $commandService = $this->serviceManager->get(CommandService::class);

        $this->commandStore->getList()
            ->shouldBeCalledOnce()
            ->willYield([new Command(
                TestCommand::class,
                'Arthur\\Dent',
                'Test',
            )])
        ;

        $this->expectException(CommandError::class);
        $commandService->getCommandClassname(['bin\\command', 'Ford:Prefect']);
    }

    public function testGetCommandClassnameNotUnique(): void
    {
        $commandService = $this->serviceManager->get(CommandService::class);

        $this->commandStore->getList()
            ->shouldBeCalledOnce()
            ->willYield([
                new Command(
                    TestCommand::class,
                    'Arthur\\Dent',
                    'Test',
                ),
                new Command(
                    TestCommand::class,
                    'Arthur\\Dental',
                    'Test',
                ),
            ])
        ;

        $this->expectException(CommandError::class);
        $commandService->getCommandClassname(['bin\\command', 'art:den']);
    }
}
