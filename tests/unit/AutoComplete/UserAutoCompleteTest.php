<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\AutoComplete;

use GibsonOS\Core\AutoComplete\UserAutoComplete;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Repository\UserRepository;
use Prophecy\Prophecy\ObjectProphecy;

class UserAutoCompleteTest extends UnitAutoCompleteTest
{
    private UserRepository|ObjectProphecy $userRepository;

    protected function _before()
    {
        $this->userRepository = $this->prophesize(UserRepository::class);

        parent::_before();
    }

    protected function getAutoComplete(): UserAutoComplete
    {
        return new UserAutoComplete($this->userRepository->reveal());
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
        $user = new User($this->modelWrapper->reveal());
        $this->userRepository->getById(42)
            ->shouldBeCalledOnce()
            ->willReturn($user)
        ;

        $this->assertEquals($user, $this->autoComplete->getById('42', []));
    }

    protected function getValueField(): string
    {
        return 'id';
    }

    protected function getDisplayField(): string
    {
        return 'user';
    }
}
