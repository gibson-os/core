<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\AutoComplete\Weather;

use GibsonOS\Core\AutoComplete\Weather\LocationAutoComplete;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Repository\Weather\LocationRepository;
use GibsonOS\Test\Unit\Core\AutoComplete\UnitAutoCompleteTest;
use Prophecy\Prophecy\ObjectProphecy;

class LocationAutoCompleteTest extends UnitAutoCompleteTest
{
    private LocationRepository|ObjectProphecy $locationRepository;

    protected function _before()
    {
        $this->locationRepository = $this->prophesize(LocationRepository::class);

        parent::_before();
    }

    protected function getAutoComplete(): LocationAutoComplete
    {
        return new LocationAutoComplete($this->locationRepository->reveal());
    }

    public function testGetByNamePart(): void
    {
        $this->locationRepository->findByName('marvin', false)
            ->shouldBeCalledTimes(2)
            ->willReturn(['arthur'])
        ;
        $this->locationRepository->findByName('marvin', true)
            ->shouldBeCalledOnce()
            ->willReturn(['dent'])
        ;

        $this->assertEquals(['arthur'], $this->autoComplete->getByNamePart('marvin', []));
        $this->assertEquals(['dent'], $this->autoComplete->getByNamePart('marvin', ['onlyActive' => true]));
        $this->assertEquals(['arthur'], $this->autoComplete->getByNamePart('marvin', ['onlyActive' => false]));
    }

    public function testGetById(): void
    {
        $location = new Location($this->mysqlDatabase->reveal());
        $this->locationRepository->getById(42)
            ->shouldBeCalledOnce()
            ->willReturn($location)
        ;

        $this->assertEquals($location, $this->autoComplete->getById('42', []));
    }

    protected function getValueField(): string
    {
        return 'id';
    }

    protected function getDisplayField(): string
    {
        return 'name';
    }
}
