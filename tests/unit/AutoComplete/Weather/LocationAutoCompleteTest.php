<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest\AutoComplete\Weather;

use GibsonOS\Core\AutoComplete\Weather\LocationAutoComplete;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Repository\Weather\LocationRepository;
use GibsonOS\UnitTest\AutoComplete\AbstractAutoCompleteTest;
use Prophecy\Prophecy\ObjectProphecy;

class LocationAutoCompleteTest extends AbstractAutoCompleteTest
{
    private LocationRepository|ObjectProphecy $locationRepository;

    protected function _before()
    {
        $this->locationRepository = $this->prophesize(LocationRepository::class);
        $this->serviceManager->setService(LocationRepository::class, $this->locationRepository->reveal());

        parent::_before();
    }

    protected function getAutoCompleteClassName(): string
    {
        return LocationAutoComplete::class;
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
        $location = new Location();
        $this->locationRepository->getById(42)
            ->shouldBeCalledOnce()
            ->willReturn($location)
        ;

        $this->assertEquals($location, $this->autoComplete->getById('42', []));
    }
}
