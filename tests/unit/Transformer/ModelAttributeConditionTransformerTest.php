<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Transformer;

use Codeception\Test\Unit;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Core\Transformer\ModelAttributeConditionTransformer;
use GibsonOS\Mock\Dto\Mapper\MapObject;
use GibsonOS\Mock\Dto\Mapper\MapObjectParent;
use GibsonOS\Mock\Dto\Mapper\StringEnum;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ModelAttributeConditionTransformerTest extends Unit
{
    use ProphecyTrait;

    private ModelAttributeConditionTransformer $modelAttributeConditionTransformer;

    private ObjectProphecy|RequestService $requestService;

    private SessionService|ObjectProphecy $sessionService;

    protected function _before()
    {
        $this->requestService = $this->prophesize(RequestService::class);
        $this->sessionService = $this->prophesize(SessionService::class);

        $this->modelAttributeConditionTransformer = new ModelAttributeConditionTransformer(
            $this->requestService->reveal(),
            $this->sessionService->reveal(),
            new ReflectionManager(),
        );
    }

    public function testTransformRequestValue(): void
    {
        $this->requestService->getRequestValue('id')
            ->shouldBeCalledOnce()
            ->willReturn(42)
        ;

        $this->assertEquals(['id' => 42], $this->modelAttributeConditionTransformer->transform(['id' => 'id']));
    }

    public function testTransformValue(): void
    {
        $this->assertEquals(['id' => 42], $this->modelAttributeConditionTransformer->transform(['id' => 'value.42']));
    }

    public function testTransformNestedException(): void
    {
        $this->sessionService->get('arthur')
            ->shouldBeCalledOnce()
            ->willReturn(42)
        ;

        $this->expectException(MapperException::class);

        $this->modelAttributeConditionTransformer->transform(['id' => 'session.arthur.dent']);
    }

    /**
     * @dataProvider getData
     */
    public function testTransformSessionValue(
        array $conditions,
        string $sessionKey,
        mixed $sessionValue,
        array $expected,
    ): void {
        $this->sessionService->get($sessionKey)
            ->shouldBeCalledOnce()
            ->willReturn($sessionValue)
        ;

        $this->assertEquals($expected, $this->modelAttributeConditionTransformer->transform($conditions));
    }

    public function getData(): array
    {
        $mapObject = new MapObject(StringEnum::YES, 42);
        $mapObjectParent = new MapObjectParent(true, ['dent' => 42]);

        return [
            [['id' => 'session.marvin'], 'marvin', 42, ['id' => 42]],
            [['id' => 'session.arthur.dent'], 'arthur', ['dent' => 42], ['id' => 42]],
            [['id' => 'session.arthur.dent.intValue'], 'arthur', ['dent' => $mapObject], ['id' => 42]],
            [['id' => 'session.arthur.dent'], 'arthur', ['dent' => $mapObject], ['id' => $mapObject]],
            [['id' => 'session.arthur.options.dent'], 'arthur', $mapObjectParent, ['id' => 42]],
            [['id' => 'session.arthur.options'], 'arthur', $mapObjectParent, ['id' => ['dent' => 42]]],
        ];
    }
}
