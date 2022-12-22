<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest\AutoComplete;

use GibsonOS\Core\AutoComplete\UserAutoComplete;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Repository\UserRepository;
use Prophecy\Prophecy\ObjectProphecy;

class UserAutoCompleteTest extends AbstractAutoCompleteTest
{
    private UserRepository|ObjectProphecy $userRepository;

    protected function _before()
    {
        $this->userRepository = $this->prophesize(UserRepository::class);
        $this->serviceManager->setService(UserRepository::class, $this->userRepository->reveal());

        parent::_before();
    }

    protected function getAutoCompleteClassName(): string
    {
        return UserAutoComplete::class;
    }

    public function testGetByNamePart(): void
    {
        $this->userRepository->findByName('marvin')
            ->shouldBeCalledOnce()
            ->willReturn(['arthur'])
        ;

        $this->assertEquals(['arthur'], $this->autoComplete->getByNamePart('marvin', []));
    }

    public function testGetById(): void
    {
        $user = new User();
        $this->userRepository->getById(42)
            ->shouldBeCalledOnce()
            ->willReturn($user)
        ;

        $this->assertEquals($user, $this->autoComplete->getById('42', []));
    }
}
