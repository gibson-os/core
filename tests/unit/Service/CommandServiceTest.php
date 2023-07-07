<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Service;

use Codeception\Test\Unit;
use GibsonOS\Core\Command\InstallCommand;
use GibsonOS\Core\Dto\Command;
use GibsonOS\Core\Exception\ArgumentError;
use GibsonOS\Core\Exception\CommandError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Service\CommandService;
use GibsonOS\Core\Service\LockService;
use GibsonOS\Core\Service\LoggerService;
use GibsonOS\Core\Service\NewRelicService;
use GibsonOS\Core\Service\ProcessService;
use GibsonOS\Core\Store\CommandStore;
use GibsonOS\Mock\Service\TestCommand;
use GibsonOS\Mock\Service\TestInvalidOptionCommand;
use GibsonOS\Mock\Service\TestRequiresArgumentCommand;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Throwable;

class CommandServiceTest extends Unit
{
    use ProphecyTrait;

    private ProcessService|ObjectProphecy $processService;

    private CommandStore|ObjectProphecy $commandStore;

    private LockService|ObjectProphecy $lockService;

    private NewRelicService|ObjectProphecy $newRelicService;

    private CommandService $commandService;

    protected function _before(): void
    {
        $this->processService = $this->prophesize(ProcessService::class);
        $this->commandStore = $this->prophesize(CommandStore::class);
        $this->lockService = $this->prophesize(LockService::class);
        $this->newRelicService = $this->prophesize(NewRelicService::class);
        $serviceManager = new ServiceManager();
        $serviceManager->setInterface(LoggerInterface::class, LoggerService::class);
        $serviceManager->setService(ModelManager::class, $this->prophesize(ModelManager::class)->reveal());
        $serviceManager->setService(CommandStore::class, $this->commandStore->reveal());

        $this->newRelicService->isLoaded()
            ->willReturn(false)
        ;

        $this->commandService = new CommandService(
            $serviceManager,
            $this->processService->reveal(),
            new ReflectionManager(),
            $this->lockService->reveal(),
            $this->newRelicService->reveal(),
        );
    }

    public function testExecute(): void
    {
        $this->assertEquals(0, $this->commandService->execute(TestCommand::class));
        $this->assertEquals(255, $this->commandService->execute(TestCommand::class, ['arthur' => 'dent']));
        $this->assertEquals(42, $this->commandService->execute(TestCommand::class, options: ['marvin' => true]));
        $this->assertEquals(0, $this->commandService->execute(TestCommand::class, options: ['marvin' => false]));
        $this->assertEquals(0, $this->commandService->execute(TestRequiresArgumentCommand::class, ['arthur' => 'perfect']));
        $this->assertEquals(255, $this->commandService->execute(TestRequiresArgumentCommand::class, ['arthur' => 'dent']));
    }

    public function testExecuteInvalidArgument(): void
    {
        $this->expectException(ArgumentError::class);
        $this->commandService->execute(TestCommand::class, ['galaxy' => 42]);
    }

    public function testExecuteMissingArgument(): void
    {
        $this->expectException(ArgumentError::class);
        $this->commandService->execute(TestRequiresArgumentCommand::class);
    }

    public function testExecuteInvalidOption(): void
    {
        $this->expectException(ArgumentError::class);
        $this->commandService->execute(TestCommand::class, options: ['trillian' => true]);
    }

    public function testExecuteInvalidOptionType(): void
    {
        $this->expectException(ArgumentError::class);
        $this->commandService->execute(TestInvalidOptionCommand::class, options: ['marvin' => true]);
    }

    public function testExecuteInvalidOptionValue(): void
    {
        $this->expectException(Throwable::class);
        $this->commandService->execute(TestCommand::class, options: ['marvin' => 42]);
    }

    public function testExecuteAsync(): void
    {
        $this->processService->executeAsync(\Prophecy\Argument::containingString('Test"  '))
            ->shouldBeCalledOnce()
        ;
        $this->commandService->executeAsync(TestCommand::class);

        $this->processService->executeAsync(\Prophecy\Argument::containingString('Test" "--ford=prefect" '))
            ->shouldBeCalledOnce()
        ;
        $this->commandService->executeAsync(TestCommand::class, ['ford' => 'prefect']);

        $this->processService->executeAsync(\Prophecy\Argument::containingString('Test" "--arthur=dent" "-marvin"'))
            ->shouldBeCalledOnce()
        ;
        $this->commandService->executeAsync(TestCommand::class, ['arthur' => 'dent'], ['marvin']);
    }

    public function testGetCommandClassname(): void
    {
        $this->commandStore->getList()
            ->shouldBeCalledTimes(2)
            ->willYield([new Command(
                TestCommand::class,
                'Core\\Install',
                'Test',
            )])
        ;

        $this->assertEquals(InstallCommand::class, $this->commandService->getCommandClassname(['bin\\command', 'Core\\Install']));
        $this->assertEquals(InstallCommand::class, $this->commandService->getCommandClassname(['bin\\command', 'core\\install']));
        $this->assertEquals(TestCommand::class, $this->commandService->getCommandClassname(['bin\\command', 'c\\i']));
        $this->assertEquals(TestCommand::class, $this->commandService->getCommandClassname(['bin\\command', 'c:i']));
    }

    public function testGetCommandClassnameNotFound(): void
    {
        $this->commandStore->getList()
            ->shouldBeCalledOnce()
            ->willYield([new Command(
                TestCommand::class,
                'Arthur\\Dent',
                'Test',
            )])
        ;

        $this->expectException(CommandError::class);
        $this->commandService->getCommandClassname(['bin\\command', 'Ford:Prefect']);
    }

    public function testGetCommandClassnameNotUnique(): void
    {
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
        $this->commandService->getCommandClassname(['bin\\command', 'art:den']);
    }
}
