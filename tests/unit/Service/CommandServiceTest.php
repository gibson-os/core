<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Service;

use Codeception\Test\Unit;
use GibsonOS\Core\Command\InstallCommand;
use GibsonOS\Core\Dto\Command;
use GibsonOS\Core\Enum\TracePrefix;
use GibsonOS\Core\Exception\ArgumentError;
use GibsonOS\Core\Exception\CommandError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Service\CommandService;
use GibsonOS\Core\Service\LockService;
use GibsonOS\Core\Service\LoggerService;
use GibsonOS\Core\Service\ProcessService;
use GibsonOS\Core\Service\TracerService;
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

    private TracerService|ObjectProphecy $tracerService;

    private CommandService $commandService;

    protected function _before(): void
    {
        $this->processService = $this->prophesize(ProcessService::class);
        $this->commandStore = $this->prophesize(CommandStore::class);
        $this->lockService = $this->prophesize(LockService::class);
        $this->tracerService = $this->prophesize(TracerService::class);
        $serviceManager = new ServiceManager();
        $serviceManager->setInterface(LoggerInterface::class, LoggerService::class);
        $serviceManager->setService(ModelManager::class, $this->prophesize(ModelManager::class)->reveal());
        $serviceManager->setService(CommandStore::class, $this->commandStore->reveal());

        $this->commandService = new CommandService(
            $serviceManager,
            $this->processService->reveal(),
            new ReflectionManager(),
            $this->lockService->reveal(),
            $this->tracerService->reveal(),
        );
    }

    /**
     * @dataProvider getData
     */
    public function testExecute(string $commandClassName, array $arguments, array $options, int $expected): void
    {
        $this->tracerService->setTransactionName($commandClassName)
            ->shouldBeCalledOnce()
            ->willReturn($this->tracerService->reveal())
        ;
        $this->tracerService->setCustomParameters($arguments, TracePrefix::COMMAND_ARGUMENT)
            ->shouldBeCalledOnce()
            ->willReturn($this->tracerService->reveal())
        ;
        $this->tracerService->setCustomParameters($options, TracePrefix::COMMAND_OPTION)
            ->shouldBeCalledOnce()
            ->willReturn($this->tracerService->reveal())
        ;
        $this->tracerService->setCustomParameter('app.command', true)
            ->shouldBeCalledOnce()
            ->willReturn($this->tracerService->reveal())
        ;

        $this->assertEquals($expected, $this->commandService->execute($commandClassName, $arguments, $options));
    }

    public function getData(): array
    {
        return [
            'Success' => [TestCommand::class, [], [], 0],
            'Failure' => [TestCommand::class, ['arthur' => 'dent'], [], 255],
            'Answer' => [TestCommand::class, [], ['marvin' => true], 42],
            'Success with options' => [TestCommand::class, [], ['marvin' => false], 0],
            'Success with arguments' => [TestRequiresArgumentCommand::class, ['arthur' => 'prefect'], [], 0],
            'Failure with arguments' => [TestRequiresArgumentCommand::class, ['arthur' => 'dent'], [], 255],
        ];
    }

    public function testExecuteInvalidArgument(): void
    {
        $this->tracerService->setTransactionName(TestCommand::class)
            ->shouldBeCalledOnce()
            ->willReturn($this->tracerService->reveal())
        ;
        $this->tracerService->setCustomParameters(['galaxy' => 42], TracePrefix::COMMAND_ARGUMENT)
            ->shouldBeCalledOnce()
            ->willReturn($this->tracerService->reveal())
        ;
        $this->tracerService->setCustomParameters([], TracePrefix::COMMAND_OPTION)
            ->shouldBeCalledOnce()
            ->willReturn($this->tracerService->reveal())
        ;
        $this->tracerService->setCustomParameter('app.command', true)
            ->shouldBeCalledOnce()
            ->willReturn($this->tracerService->reveal())
        ;

        $this->expectException(ArgumentError::class);
        $this->commandService->execute(TestCommand::class, ['galaxy' => 42]);
    }

    public function testExecuteMissingArgument(): void
    {
        $this->tracerService->setTransactionName(TestRequiresArgumentCommand::class)
            ->shouldBeCalledOnce()
            ->willReturn($this->tracerService->reveal())
        ;
        $this->tracerService->setCustomParameters([], TracePrefix::COMMAND_ARGUMENT)
            ->shouldBeCalledOnce()
            ->willReturn($this->tracerService->reveal())
        ;
        $this->tracerService->setCustomParameters([], TracePrefix::COMMAND_OPTION)
            ->shouldBeCalledOnce()
            ->willReturn($this->tracerService->reveal())
        ;
        $this->tracerService->setCustomParameter('app.command', true)
            ->shouldBeCalledOnce()
            ->willReturn($this->tracerService->reveal())
        ;

        $this->expectException(ArgumentError::class);
        $this->commandService->execute(TestRequiresArgumentCommand::class);
    }

    public function testExecuteInvalidOption(): void
    {
        $this->tracerService->setTransactionName(TestCommand::class)
            ->shouldBeCalledOnce()
            ->willReturn($this->tracerService->reveal())
        ;
        $this->tracerService->setCustomParameters([], TracePrefix::COMMAND_ARGUMENT)
            ->shouldBeCalledOnce()
            ->willReturn($this->tracerService->reveal())
        ;
        $this->tracerService->setCustomParameters(['trillian' => true], TracePrefix::COMMAND_OPTION)
            ->shouldBeCalledOnce()
            ->willReturn($this->tracerService->reveal())
        ;
        $this->tracerService->setCustomParameter('app.command', true)
            ->shouldBeCalledOnce()
            ->willReturn($this->tracerService->reveal())
        ;

        $this->expectException(ArgumentError::class);
        $this->commandService->execute(TestCommand::class, options: ['trillian' => true]);
    }

    public function testExecuteInvalidOptionType(): void
    {
        $this->tracerService->setTransactionName(TestInvalidOptionCommand::class)
            ->shouldBeCalledOnce()
            ->willReturn($this->tracerService->reveal())
        ;
        $this->tracerService->setCustomParameters([], TracePrefix::COMMAND_ARGUMENT)
            ->shouldBeCalledOnce()
            ->willReturn($this->tracerService->reveal())
        ;
        $this->tracerService->setCustomParameters(['marvin' => true], TracePrefix::COMMAND_OPTION)
            ->shouldBeCalledOnce()
            ->willReturn($this->tracerService->reveal())
        ;
        $this->tracerService->setCustomParameter('app.command', true)
            ->shouldBeCalledOnce()
            ->willReturn($this->tracerService->reveal())
        ;

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
