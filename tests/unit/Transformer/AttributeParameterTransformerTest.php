<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Transformer;

use Codeception\Test\Unit;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Core\Transformer\AttributeParameterTransformer;
use GibsonOS\Mock\Dto\Mapper\MapObject;
use GibsonOS\Mock\Dto\Mapper\MapObjectParent;
use GibsonOS\Mock\Dto\Mapper\StringEnum;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class AttributeParameterTransformerTest extends Unit
{
    use ProphecyTrait;

    private AttributeParameterTransformer $attributeParameterTransformer;

    private ObjectProphecy|RequestService $requestService;

    private ObjectProphecy|SessionService $sessionService;

    protected function _before()
    {
        $this->requestService = $this->prophesize(RequestService::class);
        $this->sessionService = $this->prophesize(SessionService::class);

        $this->attributeParameterTransformer = new AttributeParameterTransformer(
            $this->requestService->reveal(),
            $this->sessionService->reveal(),
            new ReflectionManager(),
        );
    }

    public function testTransformValue(): void
    {
        $this->assertEquals(['id' => 42], $this->attributeParameterTransformer->transform(['id' => 'value.42']));
    }

    public function testTransformNestedException(): void
    {
        $this->sessionService->get('arthur')
            ->shouldBeCalledOnce()
            ->willReturn(42)
        ;

        $this->expectException(MapperException::class);

        $this->attributeParameterTransformer->transform(['id' => 'session.arthur.dent']);
    }

    /**
     * @dataProvider getSessionData
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

        $this->assertEquals($expected, $this->attributeParameterTransformer->transform($conditions));
    }

    public function getSessionData(): array
    {
        $mapObject = new MapObject(StringEnum::YES, 42);
        $mapObjectParent = new MapObjectParent(true, ['dent' => 42]);

        return [
            [['id' => 'session.marvin'], 'marvin', 42, ['id' => 42]],
            [['id' => 'session.arthur.dent'], 'arthur', [], ['id' => null]],
            [['id' => 'session.arthur.dent'], 'arthur', $mapObject, ['id' => null]],
            [['id' => 'session.arthur.dent'], 'arthur', ['dent' => 42], ['id' => 42]],
            [['id' => 'session.arthur.dent.intValue'], 'arthur', ['dent' => $mapObject], ['id' => 42]],
            [['id' => 'session.arthur.dent'], 'arthur', ['dent' => $mapObject], ['id' => $mapObject]],
            [['id' => 'session.arthur.options.dent'], 'arthur', $mapObjectParent, ['id' => 42]],
            [['id' => 'session.arthur.options'], 'arthur', $mapObjectParent, ['id' => ['dent' => 42]]],
        ];
    }

    /**
     * @dataProvider getRequestData
     */
    public function testTransformRequestValue(
        array $conditions,
        string $requestKey,
        mixed $requestValue,
        array $expected,
    ): void {
        $this->requestService->getRequestValue($requestKey)
            ->shouldBeCalledOnce()
            ->willReturn($requestValue)
        ;

        $this->assertEquals($expected, $this->attributeParameterTransformer->transform($conditions));
    }

    public function getRequestData(): array
    {
        return [
            [['id' => 'marvin'], 'marvin', 42, ['id' => 42]],
            [['id' => 'arthur.dent'], 'arthur', '[]', ['id' => null]],
            [['id' => 'arthur.dent'], 'arthur', '{"dent": 42}', ['id' => 42]],
            [['id' => 'arthur.dent'], 'arthur', '{"dent": [42]}', ['id' => [42]]],
            [['id' => 'arthur.dent'], 'arthur', '{"dent": {"ford": 42}}', ['id' => ['ford' => 42]]],
            [['id' => 'arthur.dent.ford'], 'arthur', '{"dent": {"ford": 42}}', ['id' => 42]],
        ];
    }
}
